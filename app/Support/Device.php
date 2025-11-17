<?php

namespace App\Support;

class Device
{
    /**
     * Détection simple du mobile via User-Agent, avec override par query (?mobile=1).
     */
    public static function isMobile(): bool
    {
        // Override de debug/preview via query string
        if (request()->boolean('mobile')) {
            return true;
        }

        // Essai avec les Client Hints modernes (Chrome/Edge/Android)
        $chUaMobile = (string) request()->header('Sec-CH-UA-Mobile', '');
        if ($chUaMobile !== '' && str_contains($chUaMobile, '?1')) {
            return true;
        }

        $ua = (string) request()->header('User-Agent', '');
        if ($ua === '') return false;

        // Liste étendue d’indicateurs de terminaux mobiles, incluant Kindle/Silk
        // et certains identifiants de modèles Kindle Fire (KF..., KFTT, etc.)
        $pattern = '/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|Silk|Kindle|KF[A-Z]+/i';
        return preg_match($pattern, $ua) === 1;
    }
}