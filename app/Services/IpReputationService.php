<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IpReputationService
{
    public function check(string $ip): array
    {
        $cacheKey = 'ip_reputation:' . $ip;
        $ttl = (int) config('security.ip_reputation.cache_ttl_minutes', 1440);

        return Cache::remember($cacheKey, $ttl * 60, function () use ($ip) {
            $provider = config('security.ip_reputation.provider', 'none');
            $apiKey = config('security.ip_reputation.api_key');

            try {
                switch ($provider) {
                    case 'vpnapi':
                        // https://vpnapi.io/
                        $resp = Http::timeout(5)
                            ->get("https://vpnapi.io/api/{$ip}", ['key' => $apiKey]);
                        if (!$resp->ok()) break;
                        $data = $resp->json();
                        $security = $data['security'] ?? [];
                        return [
                            'vpn' => (bool)($security['vpn'] ?? false),
                            'proxy' => (bool)($security['proxy'] ?? false),
                            'tor' => (bool)($security['tor'] ?? false),
                            'provider' => 'vpnapi',
                        ];

                    case 'iphub':
                        // https://iphub.info/api
                        $resp = Http::timeout(5)
                            ->withHeaders(['X-Key' => (string) $apiKey])
                            ->get("http://v2.api.iphub.info/ip/{$ip}");
                        if (!$resp->ok()) break;
                        $data = $resp->json();
                        // block: 0 (residential), 1 (non-residential/hosting), 2 (non-residential)
                        $block = (int)($data['block'] ?? 0);
                        return [
                            'vpn' => $block >= 1,
                            'proxy' => $block >= 1,
                            'tor' => false,
                            'provider' => 'iphub',
                        ];

                    case 'none':
                    default:
                        return [
                            'vpn' => false,
                            'proxy' => false,
                            'tor' => false,
                            'provider' => 'none',
                        ];
                }
            } catch (\Throwable $e) {
                // En cas d’erreur réseau/provider
                $blockOnFailure = (bool) config('security.ip_reputation.default_block_on_failure', false);
                return [
                    'vpn' => $blockOnFailure,
                    'proxy' => $blockOnFailure,
                    'tor' => $blockOnFailure,
                    'provider' => $provider,
                    'error' => $e->getMessage(),
                ];
            }

            // Réponse non-OK du provider
            $blockOnFailure = (bool) config('security.ip_reputation.default_block_on_failure', false);
            return [
                'vpn' => $blockOnFailure,
                'proxy' => $blockOnFailure,
                'tor' => $blockOnFailure,
                'provider' => $provider,
            ];
        });
    }

    public function isVpnOrProxy(string $ip): bool
    {
        $result = $this->check($ip);
        return ($result['vpn'] ?? false) || ($result['proxy'] ?? false) || ($result['tor'] ?? false);
    }
}