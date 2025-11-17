<?php

namespace App\Services;

class ShopService
{
    /**
     * Return available packages.
     * Keys are used as identifiers; each package includes a human label.
     */
    public function getPackages(): array
    {
        return [
            'nano' => ['label' => 'NANO', 'gold' => 200, 'eur' => 1.99],
            'starter' => ['label' => 'STARTER', 'gold' => 500, 'eur' => 4.99],
            'scout' => ['label' => 'SCOUT', 'gold' => 900, 'eur' => 7.99],
            'adventurer' => ['label' => 'ADVENTURER', 'gold' => 1200, 'eur' => 9.99],
            'captain' => ['label' => 'CAPTAIN', 'gold' => 2000, 'eur' => 14.99],
            'warrior' => ['label' => 'WARRIOR', 'gold' => 2500, 'eur' => 19.99, 'recommended' => true],
            'commander' => ['label' => 'COMMANDER', 'gold' => 4000, 'eur' => 29.99],
            'master' => ['label' => 'MASTER', 'gold' => 5200, 'eur' => 39.99],
            'legend' => ['label' => 'LEGEND', 'gold' => 6500, 'eur' => 49.99],
            'mythic' => ['label' => 'MYTHIC', 'gold' => 14000, 'eur' => 99.99],
        ];
    }
}