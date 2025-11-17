<?php

namespace App\Services;

use App\Models\User\UserAssetCustomization;
use App\Models\Template\TemplateBuild;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserCustomizationService
{
    /**
     * Resolve display name and icon URL for a template build with user overrides.
     * Returns ['name' => string, 'icon_url' => string|null, 'icon_filename' => string|null]
     */
    public function resolveBuild(User $user, TemplateBuild $build): array
    {
        $override = UserAssetCustomization::where('user_id', $user->id)
            ->where('template_build_id', $build->id)
            ->where('status', 'approved')
            ->first();

        $name = $override?->display_name ?: ($build->label ?? $build->name);

        // Default icon path according to type
        $defaultFolder = match ($build->type) {
            TemplateBuild::TYPE_UNIT => 'images/units/',
            TemplateBuild::TYPE_DEFENSE => 'images/defenses/',
            TemplateBuild::TYPE_SHIP => 'images/ships/',
            default => 'images/units/',
        };

        $defaultUrl = asset($defaultFolder . $build->icon);

        // Si l'utilisateur a téléversé une icône personnalisée, construire l'URL publique via asset()
        $iconUrl = $override && $override->icon_path ? asset($override->icon_path) : $defaultUrl;

        return [
            'name' => $name,
            'icon_url' => $iconUrl,
            'icon_filename' => $build->icon,
        ];
    }
}