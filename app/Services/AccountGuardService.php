<?php

namespace App\Services;

use App\Models\User\UserLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;

class AccountGuardService
{
    public function __construct(
        private IpReputationService $ipReputation
    ) {}

    /**
     * Valider une tentative d'inscription
     */
    public function ensureRegisterAllowed(string $email, string $ip, ?string $userAgent = null): void
    {
        // Bloquer si IP est VPN/Proxy/Tor
        if (Config::get('security.vpn_block_on.register', true) && $this->ipReputation->isVpnOrProxy($ip)) {
            throw ValidationException::withMessages([
                'email' => 'Inscription refusée: VPN/Proxy détecté sur votre IP.'
            ]);
        }

        // Bloquer si trop de comptes créés depuis cette IP dans la fenêtre
        $maxAccounts = (int) Config::get('security.multi_account.max_accounts_per_ip', 2);
        $windowDays = (int) Config::get('security.multi_account.window_days', 30);
        if ($this->countRecentRegistrationsByIp($ip, $windowDays) >= $maxAccounts) {
            throw ValidationException::withMessages([
                'email' => "Inscription refusée: trop de comptes créés depuis cette IP (fenêtre {$windowDays}j)."
            ]);
        }
    }

    /**
     * Valider une tentative de connexion
     */
    public function ensureLoginAllowed(string $ip): void
    {
        if (Config::get('security.vpn_block_on.login', true) && $this->ipReputation->isVpnOrProxy($ip)) {
            throw ValidationException::withMessages([
                'email' => 'Connexion refusée: VPN/Proxy détecté sur votre IP.'
            ]);
        }
    }

    /**
     * Compte le nombre d'inscriptions enregistrées depuis une IP sur une période
     */
    private function countRecentRegistrationsByIp(string $ip, int $windowDays): int
    {
        return UserLog::byCategory(UserLog::CATEGORY_AUTH)
            ->byActionType('register')
            ->where('ip_address', $ip)
            ->where('created_at', '>=', now()->subDays($windowDays))
            ->count();
    }
}