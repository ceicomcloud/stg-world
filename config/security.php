<?php

return [
    // Configuration anti-VPN / proxy / Tor
    'ip_reputation' => [
        // Provider possible: 'none', 'vpnapi', 'iphub'
        'provider' => env('IP_REPUTATION_PROVIDER', 'none'),
        'api_key' => env('IP_REPUTATION_API_KEY', null),
        'cache_ttl_minutes' => 1440, // 24h
        // Si le provider est injoignable, comportement par défaut
        'default_block_on_failure' => false,
    ],

    // Où bloquer les connexions depuis VPN/Proxy
    'vpn_block_on' => [
        'register' => true,
        'login' => true,
    ],

    // Règles anti-multicompte basées sur IP de création
    'multi_account' => [
        'max_accounts_per_ip' => 2,
        'window_days' => 30,
    ],
];