<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as SysLog;
use App\Models\User\UserOrder;
use App\Services\PaypalService;
use App\Services\LogService;
use App\Services\UserPointsService;

class PaypalController extends Controller
{
    public function createOrder(Request $request, PaypalService $paypal, LogService $logs)
    {
        $request->validate([
            'package_key' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        // Shop enabled check
        if (!\App\Models\Server\ServerConfig::isShopEnabled()) {
            return response()->json(['error' => 'shop_disabled'], 403);
        }

        // Validate against shop packages (duplicated here or via service)
        $packages = app(\App\Services\ShopService::class)->getPackages();
        $key = $request->string('package_key');
        if (!isset($packages[$key])) {
            return response()->json(['error' => 'invalid_package'], 422);
        }
        $pkg = $packages[$key];

        $order = UserOrder::create([
            'user_id' => $user->id,
            'package_key' => $key,
            'gold_amount' => (int) $pkg['gold'],
            'amount_eur' => (float) $pkg['eur'],
            'status' => 'pending',
            'provider' => 'paypal',
        ]);

        try {
            $returnUrl = route('paypal.success');
            $cancelUrl = route('paypal.cancel');
            $approvalUrl = $paypal->createOrder($order, $returnUrl, $cancelUrl);

            $logs->log(
                $user->id,
                'payment_order_created',
                'trade',
                'Commande PayPal créée',
                ['order_id' => $order->id, 'provider_order_id' => $order->provider_order_id, 'package' => $key, 'amount_eur' => $order->amount_eur]
            );

            return response()->json(['approval_url' => $approvalUrl]);
        } catch (\Throwable $e) {
            $order->status = 'failed';
            $order->save();
            $logs->log(
                $user->id,
                'payment_order_failed',
                'trade',
                'Échec de création de commande PayPal',
                ['order_id' => $order->id, 'error' => $e->getMessage()],
                null,
                null,
                \App\Models\User\UserLog::SEVERITY_ERROR
            );
            return response()->json(['error' => 'create_failed'], 500);
        }
    }

    public function captureOrder(Request $request, PaypalService $paypal, LogService $logs, UserPointsService $points)
    {
        $request->validate([
            'provider_order_id' => 'required|string',
        ]);

        $orderId = $request->string('provider_order_id');
        $order = UserOrder::where('provider_order_id', $orderId)->first();
        if (!$order) {
            return response()->json(['error' => 'order_not_found'], 404);
        }

        try {
            $capture = $paypal->captureOrder($orderId);
            $status = $capture['status'] ?? null;
            if ($status === 'COMPLETED') {
                $order->status = 'paid';
                $order->save();

                $user = $order->user;
                if ($user) {
                    $rate = max(0.0, \App\Models\Server\ServerConfig::getShopRewardRate());
                    $finalGold = (int) floor(((int) $order->gold_amount) * $rate);
                    $user->gold_balance = (int) ($user->gold_balance ?? 0) + $finalGold;
                    $user->save();

                    // Optionally trigger points recalculation
                    $points->calculateUserPoints($user->id, false);

                    $logs->log(
                        $user->id,
                        'payment_order_captured',
                        'trade',
                        'Paiement capturé et or crédité',
                        ['order_id' => $order->id, 'base_gold' => $order->gold_amount, 'applied_rate' => $rate, 'final_gold' => $finalGold, 'amount_eur' => $order->amount_eur]
                    );
                }
                return response()->json(['status' => 'paid']);
            }

            $order->status = 'failed';
            $order->save();
            if ($order->user_id) {
                $logs->log(
                    $order->user_id,
                    'payment_order_capture_failed',
                    'trade',
                    'Échec de capture PayPal',
                    ['order_id' => $order->id, 'provider_order_id' => $orderId],
                );
            }
            return response()->json(['status' => 'failed'], 400);
        } catch (\Throwable $e) {
            SysLog::error('paypal capture failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'capture_failed'], 500);
        }
    }

    public function success(Request $request)
    {
        // Simple success page; client uses capture endpoint for state update
        return response()->view('paypal.success');
    }

    public function cancel(Request $request, LogService $logs)
    {
        $providerId = $request->query('token');
        if ($providerId) {
            $order = UserOrder::where('provider_order_id', $providerId)->first();
            if ($order) {
                $order->status = 'canceled';
                $order->save();
                if ($order->user_id) {
                    $logs->log(
                        $order->user_id,
                        'payment_order_canceled',
                        'trade',
                        'Commande PayPal annulée',
                        ['order_id' => $order->id]
                    );
                }
            }
        }
        return response()->view('paypal.cancel');
    }

    public function webhook(Request $request, LogService $logs)
    {
        // NOTE: For production, verify PayPal webhook signature.
        $event = $request->input('event_type');
        $resource = $request->input('resource', []);
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? ($resource['id'] ?? null);

        if (!$orderId) {
            return response()->json(['status' => 'ignored']);
        }

        $order = UserOrder::where('provider_order_id', $orderId)->first();
        if (!$order) {
            return response()->json(['status' => 'ignored']);
        }

        switch ($event) {
            case 'PAYMENT.CAPTURE.COMPLETED':
                $order->status = 'paid';
                $order->save();
                if ($order->user_id) {
                    $logs->log(
                        $order->user_id,
                        'payment_webhook_completed',
                        'trade',
                        'Webhook PayPal: paiement complété',
                        ['order_id' => $order->id]
                    );
                }
                break;
            case 'PAYMENT.CAPTURE.DENIED':
            case 'PAYMENT.CAPTURE.REFUNDED':
                $order->status = 'failed';
                $order->save();
                if ($order->user_id) {
                    $logs->log(
                        $order->user_id,
                        'payment_webhook_failed',
                        'trade',
                        'Webhook PayPal: paiement refusé/remboursé',
                        ['order_id' => $order->id, 'event' => $event]
                    );
                }
                break;
            case 'CUSTOMER.DISPUTE.CREATED':
                if ($order->user_id) {
                    $logs->log(
                        $order->user_id,
                        'payment_webhook_dispute',
                        'trade',
                        'Webhook PayPal: litige créé',
                        ['order_id' => $order->id]
                    );
                }
                break;
        }

        return response()->json(['status' => 'ok']);
    }
}