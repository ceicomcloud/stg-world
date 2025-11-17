<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

class PaypalService
{
    protected Client $client;
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $mode = Config::get('services.paypal.mode', 'sandbox');
        $this->baseUrl = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
        $this->clientId = Config::get('services.paypal.client_id');
        $this->clientSecret = Config::get('services.paypal.client_secret');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false,
            'timeout' => 15,
        ]);
    }

    protected function getAccessToken(): ?string
    {
        // Try cache first
        $cacheKey = 'paypal_access_token';
        $token = Cache::get($cacheKey);
        if ($token) {
            return $token;
        }

        $response = $this->client->post('/v1/oauth2/token', [
            'auth' => [$this->clientId, $this->clientSecret],
            'form_params' => ['grant_type' => 'client_credentials'],
            'headers' => ['Accept' => 'application/json'],
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $token = $data['access_token'] ?? null;
        $expires = isset($data['expires_in']) ? (int) $data['expires_in'] : 0; // seconds
        if ($token) {
            // Store slightly less than expiry to avoid edge invalidation
            $ttl = $expires > 120 ? $expires - 60 : 300;
            Cache::put($cacheKey, $token, $ttl);
        }
        return $token;
    }

    /**
     * Create PayPal Order and return approval URL.
     */
    public function createOrder(\App\Models\User\UserOrder $order, string $returnUrl, string $cancelUrl): string
    {
        $token = $this->getAccessToken();
        if (!$token) {
            throw new \RuntimeException('Unable to acquire PayPal token');
        }

        $body = [
            'intent' => 'CAPTURE',
            'application_context' => [
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => 'EUR',
                    'value' => number_format($order->amount_eur, 2, '.', ''),
                ],
                'custom_id' => (string) $order->id,
                'description' => 'Pack ' . strtoupper($order->package_key) . ' - ' . number_format($order->gold_amount) . ' or',
            ]],
        ];

        $response = $this->client->post('/v2/checkout/orders', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => $body,
        ]);

        // Retry once on unauthorized
        if ($response->getStatusCode() === 401) {
            Cache::forget('paypal_access_token');
            $token = $this->getAccessToken();
            if (!$token) {
                throw new \RuntimeException('Unable to acquire PayPal token (retry)');
            }
            $response = $this->client->post('/v2/checkout/orders', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]);
        }

        $data = json_decode((string) $response->getBody(), true);

        if (!isset($data['id'])) {
            throw new \RuntimeException('PayPal order creation failed: ' . json_encode($data));
        }

        $order->provider_order_id = $data['id'];
        $order->save();

        $approveLink = collect($data['links'] ?? [])
            ->first(fn ($l) => ($l['rel'] ?? '') === 'approve')['href'] ?? null;

        if (!$approveLink) {
            throw new \RuntimeException('Missing approval link from PayPal response');
        }

        return $approveLink;
    }

    /**
     * Capture PayPal Order and return response array.
     */
    public function captureOrder(string $orderId): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            throw new \RuntimeException('Unable to acquire PayPal token');
        }

        $response = $this->client->post("/v2/checkout/orders/{$orderId}/capture", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ]);

        // Retry once on unauthorized
        if ($response->getStatusCode() === 401) {
            Cache::forget('paypal_access_token');
            $token = $this->getAccessToken();
            if (!$token) {
                throw new \RuntimeException('Unable to acquire PayPal token (retry)');
            }
            $response = $this->client->post("/v2/checkout/orders/{$orderId}/capture", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
            ]);
        }

        return json_decode((string) $response->getBody(), true) ?? [];
    }
}