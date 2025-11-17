<?php

return [
    // Active le cache pour les données lourdes du Game
    'cache_enabled' => env('GAME_CACHE_ENABLED', true),

    // TTL en secondes pour chaque jeu de données
    'cache_ttl' => [
        'planets' => env('GAME_CACHE_TTL_PLANETS', 30),
        'queues' => env('GAME_CACHE_TTL_QUEUES', 5),
        'missions' => env('GAME_CACHE_TTL_MISSIONS', 10),
        'badges_recent' => env('GAME_CACHE_TTL_BADGES_RECENT', 60),
        'badges_upcoming' => env('GAME_CACHE_TTL_BADGES_UPCOMING', 60),
        // Galaxy
        'galaxy_templates' => env('GAME_CACHE_TTL_GALAXY_TEMPLATES', 30),
        'galaxy_system' => env('GAME_CACHE_TTL_GALAXY_SYSTEM', 10),
        // Templates
        'template_builds' => env('GAME_CACHE_TTL_TEMPLATE_BUILDS', 300),
    ],
];