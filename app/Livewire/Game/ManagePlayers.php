<?php

namespace App\Livewire\Game;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use App\Models\Server\ServerConfig;
use App\Services\ShopService;

#[Layout('components.layouts.game')]
class ManagePlayers extends Component
{
    public $goldBalance = 0;
    public $vipActive = false;
    public $vipUntil = null;
    public $vipBadgeEnabled = true;
    public $activeTab = 'home';
    public $hidePointsBreakdown = false;

    // Coût du VIP pour 1 mois en or
    public int $vipCostGold = 2000;

    // Packages boutique (clé => [gold, eur])
    public array $shopPackages = [];

    // Limites (normal/VIP)
    public int $maxPlanetEquipsNormal = 0;
    public int $maxPlanetEquipsVip = 0;
    public int $maxBookmarksNormal = 0;
    public int $maxBookmarksVip = 0;

    public $orders = [];
    public bool $shopEnabled = true;
    public float $shopRewardRate = 1.0; // multiplicateur, ex: 1.25

    public function mount()
    {
        $user = Auth::user();
        $this->hidePointsBreakdown = (bool) ($user->hide_points_breakdown ?? false);
        $this->goldBalance = (int) ($user->gold_balance ?? 0);
        $this->vipActive = (bool) ($user->vip_active ?? false);
        $this->vipUntil = $user->vip_until;
        $this->vipBadgeEnabled = (bool) ($user->vip_badge_enabled ?? true);

        // Charger les limites pour affichage des avantages VIP
        $this->maxPlanetEquipsNormal = ServerConfig::get('max_planet_equips_normal', 2);
        $this->maxPlanetEquipsVip = ServerConfig::getMaxPlanetEquipsVip();
        $this->maxBookmarksNormal = ServerConfig::getMaxBookmarksNormal();
        $this->maxBookmarksVip = ServerConfig::getMaxBookmarksVip();

        $this->loadOrders();
        $this->handlePaypalCallback();

        // Boutique status & bonus
        $this->shopEnabled = (bool) ServerConfig::isShopEnabled();
        $this->shopRewardRate = (float) ServerConfig::getShopRewardRate();

        // Charger les packages depuis le service centralisé
        $this->shopPackages = app(ShopService::class)->getPackages();
    }

    public function saveSettings()
    {
        $user = Auth::user();
        $user->gold_balance = max(0, (int) $this->goldBalance);
        $user->vip_active = (bool) $this->vipActive;
        $user->vip_until = $this->vipUntil;
        $user->vip_badge_enabled = (bool) $this->vipBadgeEnabled;
        $user->save();

        $this->dispatch('toast:success', [
            'title' => 'Sauvegardé',
            'text' => 'Paramètres du joueur mis à jour.'
        ]);
    }

    public function saveHidePointsBreakdown(): void
    {
        $user = Auth::user();
        if ($user) {
            $newValue = !((bool) ($user->hide_points_breakdown ?? false));
            $user->hide_points_breakdown = $newValue;
            $user->save();

            $this->hidePointsBreakdown = $newValue;

            $this->dispatch('toast:success', [
                'title' => 'Confidentialité mise à jour',
                'text' => $newValue ? 'Le détail de vos points est désormais masqué.' : 'Le détail de vos points est désormais visible.'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.game.manage-players');
    }

    // ---------------- Onglets -----------------
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        if ($tab === 'transactions') {
            $this->loadOrders();
        }
    }

    // ---------------- VIP -----------------
    public function activateVipOneMonth(): void
    {
        $user = Auth::user();
        if (($user->gold_balance ?? 0) < $this->vipCostGold) {
            $this->dispatch('toast:error', [
                'title' => 'Or insuffisant',
                'text' => 'Vous avez besoin de ' . number_format($this->vipCostGold) . ' or.'
            ]);
            return;
        }

        // Déduire l'or et activer le VIP
        $user->gold_balance = max(0, (int) $user->gold_balance - $this->vipCostGold);
        $user->vip_active = true;
        $user->vip_until = now()->addMonth();
        $user->save();

        // Mettre à jour l'UI
        $this->goldBalance = (int) $user->gold_balance;
        $this->vipActive = true;
        $this->vipUntil = $user->vip_until;

        $this->dispatch('toast:success', [
            'title' => 'VIP activé',
            'text' => 'Votre VIP est activé pour 1 mois.'
        ]);
    }

    public function toggleVipBadge(): void
    {
        $this->vipBadgeEnabled = !$this->vipBadgeEnabled;
    }

    // ---------------- Boutique / PayPal -----------------
    public function createPaypalOrder(string $packageKey): void
    {
        // Shop enabled check
        if (!\App\Models\Server\ServerConfig::isShopEnabled()) {
            $this->dispatch('toast:error', [
                'title' => 'Shop désactivé',
                'text' => 'Les achats sont momentanément indisponibles.'
            ]);
            return;
        }

        if (!isset($this->shopPackages[$packageKey])) {
            $this->dispatch('toast:error', [
                'title' => 'Package invalide',
                'text' => 'Ce package n\'existe pas.'
            ]);
            return;
        }

        $pkg = $this->shopPackages[$packageKey];

        // Créer une commande en statut pending
        $order = \App\Models\User\UserOrder::create([
            'user_id' => Auth::id(),
            'package_key' => $packageKey,
            'gold_amount' => (int) $pkg['gold'],
            'amount_eur' => (float) $pkg['eur'],
            'status' => 'pending',
            'provider' => 'paypal',
        ]);

        try {
            // Rediriger le retour/annulation vers le même écran avec paramètres de query
            $returnUrl = route('game.manage-players', ['paypal' => 'return']);
            $cancelUrl = route('game.manage-players', ['paypal' => 'cancel']);

            $approvalUrl = app(\App\Services\PaypalService::class)
                ->createOrder(
                    $order,
                    $returnUrl,
                    $cancelUrl
                );

            $this->dispatch('toast:success', [
                'title' => 'Commande créée',
                'text' => 'Redirection vers PayPal…'
            ]);

            // Redirection dure vers PayPal (Livewire v3)
            $this->redirect($approvalUrl, navigate: false);
        } catch (\Throwable $e) {
            $order->status = 'failed';
            $order->save();
            $this->dispatch('toast:error', [
                'title' => 'Erreur PayPal',
                'text' => 'Impossible de créer la commande. Réessayez plus tard.'
            ]);
        }

        $this->loadOrders();
    }

    protected function loadOrders(): void
    {
        $this->orders = \App\Models\User\UserOrder::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->take(20)
            ->get();
    }

    // ----------------- PayPal Callback via query -----------------
    protected function handlePaypalCallback(): void
    {
        $action = request()->query('paypal'); // 'return' ou 'cancel'
        $token = request()->query('token');   // identifiant d'ordre PayPal

        if (!$action || !$token) {
            return;
        }

        // Toujours ouvrir l'onglet Transactions lors d'un retour
        $this->setActiveTab('transactions');

        if ($action === 'return') {
            try {
                $capture = app(\App\Services\PaypalService::class)->captureOrder($token);
                $status = $capture['status'] ?? null;
                if ($status === 'COMPLETED') {
                    $order = \App\Models\User\UserOrder::where('provider_order_id', $token)->first();
                    if ($order) {
                        $order->status = 'paid';
                        $order->save();
                        // Créditer l'or (avec bonus shop)
                        $user = $order->user;
                        if ($user) {
                            $rate = max(0.0, \App\Models\Server\ServerConfig::getShopRewardRate());
                            $finalGold = (int) floor(((int) $order->gold_amount) * $rate);
                            $user->gold_balance = (int) ($user->gold_balance ?? 0) + $finalGold;
                            $user->save();
                            $this->goldBalance = (int) $user->gold_balance;
                        }
                    }
                    $this->dispatch('toast:success', [
                        'title' => 'Paiement confirmé',
                        'text' => 'Or crédité sur votre compte.'
                    ]);
                } else {
                    $this->updateOrderStatus($token, 'failed');
                    $this->dispatch('toast:error', [
                        'title' => 'Paiement non confirmé',
                        'text' => 'La capture PayPal n\'est pas complétée.'
                    ]);
                }
            } catch (\Throwable $e) {
                $this->updateOrderStatus($token, 'failed');
                $this->dispatch('toast:error', [
                    'title' => 'Erreur PayPal',
                    'text' => 'Échec de la capture de paiement.'
                ]);
            }
        } elseif ($action === 'cancel') {
            $this->updateOrderStatus($token, 'canceled');
            $this->dispatch('toast:warning', [
                'title' => 'Paiement annulé',
                'text' => 'La commande a été annulée.'
            ]);
        }

        $this->loadOrders();
    }

    protected function updateOrderStatus(?string $providerOrderId, string $status): void
    {
        if (!$providerOrderId) return;
        $order = \App\Models\User\UserOrder::where('provider_order_id', $providerOrderId)->first();
        if ($order) {
            $order->status = $status;
            $order->save();
        }
    }
}