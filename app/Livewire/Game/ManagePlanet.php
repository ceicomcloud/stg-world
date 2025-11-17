<?php

namespace App\Livewire\Game;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetResource;
use App\Models\Template\TemplateResource;
use App\Models\User\UserTechnology;
use App\Traits\LogsUserActions;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.game')]
class ManagePlanet extends Component
{
    use LogsUserActions;
    public $planet = null;
    public $availablePlanets = [];
    public $planetBuildings = [];
    public $planetUnits = [];
    public $planetDefenses = [];
    public $planetShips = [];
    public $planetInfo = [];
    
    // Propriétés pour l'édition
    public $isEditing = false;
    public $editName = '';
    public $editDescription = '';
    public $isEditingImage = false;
    public $selectedImage = '';
    public $availableImages = [];
    
    // Propriétés pour la gestion de la production
    public $planetResources = [];
    public $productionRates = [];
    public $isEditingProduction = false;

    // Porte des étoiles
    public $stargateActivationCost = 10000; // Deuterium
    public $stargateDeactivationCost = 5000; // Deuterium

    public function mount()
    {
        $this->loadAvailableImages();
        $this->loadUserPlanets();
        
        // Utiliser la planète actuelle de l'utilisateur ou la première disponible
        $actualPlanet = Auth::user()->getActualPlanet();
        if ($actualPlanet) {
            $planetData = $this->availablePlanets->firstWhere('id', $actualPlanet->id);
            if ($planetData) {
                $this->selectPlanet($planetData);
                return;
            }
        }
        
        // Fallback sur la première planète disponible
        if ($this->availablePlanets->isNotEmpty()) {
            $this->selectPlanet($this->availablePlanets->first());
        }
    }

    public function loadUserPlanets()
    {
        // Utiliser la relation planets du modèle User
        $this->availablePlanets = Auth::user()->planets()
            ->select('id', 'name', 'description', 'is_main_planet')
            ->get();
    }

    public function selectPlanet($planetData)
    {
        $this->planet = $planetData;
        $this->loadPlanetData();
    }

    public function loadPlanetData()
    {
        if (!$this->planet) {
            return;
        }

        $planet = Planet::find($this->planet['id']);
        if (!$planet) {
            return;
        }

        // Pré-calcul des cooldowns
        $shieldCooldownSeconds = $planet->last_shield_activation
            ? max(0, now()->diffInSeconds($planet->last_shield_activation->copy()->addDays(30)))
            : 0;
        $stargateCooldownSeconds = $planet->last_stargate_toggle
            ? max(0, now()->diffInSeconds($planet->last_stargate_toggle->copy()->addHours(24)))
            : 0;

        // Récupération des prérequis pour la protection planétaire
        $inversionField = \App\Models\User\UserTechnology::where('user_id', auth()->id())
            ->whereHas('technology', function($query) {
                $query->where('name', 'champ_inversion');
            })
            ->first();
        $currentTechLevel = $inversionField->level ?? 0;
        $requiredTechLevel = 5;

        $shieldGenerator = $planet->defenses()
            ->whereHas('defense', function($query) {
                $query->where('name', 'generateur_bouclier');
            })
            ->first();
        $currentGenerators = $shieldGenerator->quantity ?? 0;
        $requiredGenerators = 10 * 7; // 7 jours de protection

        $deuterium = $planet->resources()
            ->whereHas('resource', function($query) {
                $query->where('name', 'deuterium');
            })
            ->first();
        $currentDeuterium = $deuterium->current_amount ?? 0;
        $requiredDeuterium = 5000 * 7; // 7 jours de protection

        // Charger les informations de la planète
        $this->planetInfo = [
            'name' => $planet->name,
            'description' => $planet->description,
            'image' => $planet->image,
            'used_fields' => $planet->used_fields,
            'total_fields' => $planet->templatePlanet->fields ?? 0,
            'type' => $planet->templatePlanet->type ?? 'unknown',
            'coordinates' => [
                'galaxy' => $planet->templatePlanet->galaxy ?? 0,
                'system' => $planet->templatePlanet->system ?? 0,
                'position' => $planet->templatePlanet->position ?? 0,
            ],
            'bonuses' => [
                'metal' => $planet->templatePlanet->metal_bonus ?? 1,
                'crystal' => $planet->templatePlanet->crystal_bonus ?? 1,
                'deuterium' => $planet->templatePlanet->deuterium_bonus ?? 1,
                'energy' => $planet->templatePlanet->energy_bonus ?? 1,
            ],
            // Informations sur la protection planétaire
            'shield_protection_active' => $planet->isShieldProtectionActive(),
            'can_activate_shield' => $planet->canActivateShieldProtection(),
            'remaining_shield_time' => round($planet->getRemainingShieldProtectionTime() / 3600, 1),
            'shield_protection_progress' => $planet->getShieldProtectionProgress(),
            'shield_cooldown_days' => $planet->last_shield_activation ? max(0, 30 - now()->diffInDays($planet->last_shield_activation)) : 0,
            'shield_cooldown_breakdown' => $this->breakdownSeconds($shieldCooldownSeconds),
            'shield_cooldown_text' => $this->formatCooldownText($shieldCooldownSeconds),
            // Prérequis d'activation du bouclier
            'shield_required_tech_level' => $requiredTechLevel,
            'shield_current_tech_level' => $currentTechLevel,
            'shield_tech_met' => $currentTechLevel >= $requiredTechLevel,
            'shield_required_generators' => $requiredGenerators,
            'shield_current_generators' => $currentGenerators,
            'shield_generators_met' => $currentGenerators >= $requiredGenerators,
            'shield_required_deuterium' => $requiredDeuterium,
            'shield_current_deuterium' => $currentDeuterium,
            'shield_deuterium_met' => $currentDeuterium >= $requiredDeuterium,
            // Informations sur la Porte des étoiles
            'stargate_active' => $planet->isStargateActive(),
            'stargate_can_toggle' => !$planet->isStargateInCooldown(),
            'stargate_cooldown_breakdown' => $this->breakdownSeconds($stargateCooldownSeconds),
            'stargate_cooldown_text' => $this->formatCooldownText($stargateCooldownSeconds),
            'stargate_activation_cost' => $this->stargateActivationCost,
            'stargate_deactivation_cost' => $this->stargateDeactivationCost,
        ];

        // Charger les bâtiments
        $this->planetBuildings = $planet->buildings()->with('build')->get()->map(function ($building) {
            return [
                'id' => $building->id,
                'name' => $building->build->label ?? $building->build->name,
                'level' => $building->level,
                'icon' => $building->build->icon,
                'type' => $building->build->type,
                'is_active' => $building->is_active,
            ];
        })->toArray();

        // Charger les unités
        $this->planetUnits = $planet->units()->with('unit')->get()->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->unit->label ?? $unit->unit->name,
                'quantity' => $unit->quantity,
                'icon' => $unit->unit->icon,
            ];
        })->toArray();

        // Charger les défenses
        $this->planetDefenses = $planet->defenses()->with('defense')->get()->map(function ($defense) {
            return [
                'id' => $defense->id,
                'name' => $defense->defense->label ?? $defense->defense->name,
                'quantity' => $defense->quantity,
                'icon' => $defense->defense->icon,
            ];
        })->toArray();

        // Charger les vaisseaux
        $this->planetShips = $planet->ships()->with('ship')->get()->map(function ($ship) {
            return [
                'id' => $ship->id,
                'name' => $ship->ship->label ?? $ship->ship->name,
                'quantity' => $ship->quantity,
                'icon' => $ship->ship->icon,
            ];
        })->toArray();
        
        
        // Charger les ressources et la production
        $this->loadPlanetResources();
    }

    /**
     * Activer la Porte des étoiles (coût et cooldown 24h)
     */
    public function activateStargate()
    {
        if (!$this->planet) {
            return;
        }

        $planet = Planet::find($this->planet['id']);
        if (!$planet) {
            return;
        }

        // Vérifier cooldown
        if ($planet->isStargateInCooldown()) {
            $this->dispatch('toast:error', [
                'title' => 'Cooldown',
                'text' => 'La Porte des étoiles est en cooldown. Patientez avant de basculer.'
            ]);
            return;
        }

        // Vérifier les ressources (deuterium)
        $deuterium = $planet->resources()
            ->whereHas('resource', function($query) {
                $query->where('name', 'deuterium');
            })
            ->first();

        if (!$deuterium || $deuterium->current_amount < $this->stargateActivationCost) {
            $this->dispatch('toast:error', [
                'title' => 'Ressources insuffisantes',
                'text' => "Vous avez besoin de {$this->stargateActivationCost} Deuterium pour activer la Porte des étoiles."
            ]);
            return;
        }

        // Déduire le coût et activer
        $deuterium->current_amount -= $this->stargateActivationCost;
        $deuterium->save();

        $planet->stargate_active = true;
        $planet->last_stargate_toggle = now();
        $planet->save();

        // Recharger
        $this->loadPlanetData();

        $this->dispatch('toast:success', [
            'title' => 'Porte des étoiles',
            'text' => 'Porte des étoiles activée. Les attaques terrestres sont désormais interdites sur cette planète.'
        ]);

        // Log
        $this->logAction(
            'stargate_activated',
            'planet',
            'Activation de la Porte des étoiles',
            [
                'planet_id' => $planet->id,
                'deuterium_cost' => $this->stargateActivationCost,
                'cooldown_hours' => 24
            ],
            $planet->id
        );
    }

    /**
     * Désactiver la Porte des étoiles (coût et cooldown 24h)
     */
    public function deactivateStargate()
    {
        if (!$this->planet) {
            return;
        }

        $planet = Planet::find($this->planet['id']);
        if (!$planet) {
            return;
        }

        // Vérifier cooldown
        if ($planet->isStargateInCooldown()) {
            $this->dispatch('toast:error', [
                'title' => 'Cooldown',
                'text' => 'La Porte des étoiles est en cooldown. Patientez avant de basculer.'
            ]);
            return;
        }

        // Vérifier les ressources (deuterium)
        $deuterium = $planet->resources()
            ->whereHas('resource', function($query) {
                $query->where('name', 'deuterium');
            })
            ->first();

        if (!$deuterium || $deuterium->current_amount < $this->stargateDeactivationCost) {
            $this->dispatch('toast:error', [
                'title' => 'Ressources insuffisantes',
                'text' => "Vous avez besoin de {$this->stargateDeactivationCost} Deuterium pour désactiver la Porte des étoiles."
            ]);
            return;
        }

        // Déduire le coût et désactiver
        $deuterium->current_amount -= $this->stargateDeactivationCost;
        $deuterium->save();

        $planet->stargate_active = false;
        $planet->last_stargate_toggle = now();
        $planet->save();

        // Recharger
        $this->loadPlanetData();

        $this->dispatch('toast:success', [
            'title' => 'Porte des étoiles',
            'text' => 'Porte des étoiles désactivée. Les attaques terrestres redeviennent possibles.'
        ]);

        // Log
        $this->logAction(
            'stargate_deactivated',
            'planet',
            'Désactivation de la Porte des étoiles',
            [
                'planet_id' => $planet->id,
                'deuterium_cost' => $this->stargateDeactivationCost,
                'cooldown_hours' => 24
            ],
            $planet->id
        );
    }
    
    public function loadPlanetResources()
    {
        if (!$this->planet) {
            return;
        }
        
        $planet = Planet::find($this->planet['id']);
        if (!$planet) {
            return;
        }
        
        // Charger les ressources actuelles
        $resources = $planet->resources()->with('resource')->get();
        
        $this->planetResources = [];
        $this->productionRates = [];
        
        foreach ($resources as $resource) {
            $templateResource = $resource->resource;
            if (!$templateResource) continue;
            
            // Use PlanetResource methods for calculations
            $baseProductionPerHour = $resource->getCurrentProductionPerHour();
            $storageCapacity = $resource->getStorageCapacity();
            $productionRate = $resource->production_rate ?? 100;
            
            // Calculate current production per hour and daily production
            $currentProductionPerHour = $baseProductionPerHour * ($productionRate / 100);
            $dailyProduction = $currentProductionPerHour * 24;
            
            $this->planetResources[$templateResource->name] = [
                'id' => $resource->id,
                'name' => $templateResource->display_name,
                'icon' => $templateResource->icon,
                'current_amount' => $resource->current_amount,
                'storage_capacity' => $storageCapacity,
                'base_production_per_hour' => $baseProductionPerHour,
                'current_production_per_hour' => $currentProductionPerHour,
                'daily_production' => $dailyProduction,
                'production_rate' => $productionRate,
            ];
            
            $this->productionRates[$templateResource->name] = $productionRate;
        }
    }
    


    #[On('planet-changed')]
    public function refreshPlanetData()
    {
        $this->loadPlanetData();
    }


    /**
     * Toggle building activation status
     */
    public function toggleBuildingStatus($buildingId)
    {
        if (!$this->planet) {
            return;
        }

        $planet = Planet::find($this->planet['id']);
        if (!$planet) {
            return;
        }

        $building = $planet->buildings()->find($buildingId);
        if (!$building) {
            return;
        }

        // Toggle the is_active status
        $building->is_active = !$building->is_active;
        $building->save();

        // Reload planet data to reflect changes
        $this->loadPlanetData();

        // Show success message
        $status = $building->is_active ? 'activé' : 'désactivé';

        $this->dispatch('toast:success', [
            'title' => 'Bâtiment!',
            'text' => "Bâtiment {$status} avec succès."
        ]);
    }

    public function startEditing()
    {
        if (!$this->planet) {
            return;
        }
        
        $this->isEditing = true;
        $this->editName = $this->planetInfo['name'] ?? '';
        $this->editDescription = $this->planetInfo['description'] ?? '';
    }

    public function cancelEditing()
    {
        $this->isEditing = false;
        $this->editName = '';
        $this->editDescription = '';
    }

    public function savePlanetInfo()
    {
        if (!$this->planet) {
            return;
        }

        // Validation
        $this->validate([
            'editName' => 'required|string|max:255',
            'editDescription' => 'nullable|string|max:1000',
        ], [
            'editName.required' => 'Le nom de la planète est requis.',
            'editName.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'editDescription.max' => 'La description ne peut pas dépasser 1000 caractères.',
        ]);

        try {
            $planet = Planet::find($this->planet['id']);
            if ($planet) {
                $planet->update([
                    'name' => $this->editName,
                    'description' => $this->editDescription,
                ]);

                // Mettre à jour les données locales
                $this->planetInfo['name'] = $this->editName;
                $this->planetInfo['description'] = $this->editDescription;
                
                // Mettre à jour la liste des planètes disponibles
                $this->loadUserPlanets();
                
                // Mettre à jour la planète sélectionnée
                $this->planet['name'] = $this->editName;
                $this->planet['description'] = $this->editDescription;

                $this->isEditing = false;
                $this->editName = '';
                $this->editDescription = '';

                // Notification de succès
                $this->dispatch('toast:success', [
                    'title' => 'Planète mise à jour',
                    'text' => 'Les informations de la planète ont été sauvegardées avec succès.'
                ]);
            }
        } catch (\Exception $e) {
            // Notification d'erreur
            $this->dispatch('toast:error', [
                'title' => 'Erreur',
                'text' => 'Une erreur est survenue lors de la sauvegarde.'
            ]);
        }
    }

    public function loadAvailableImages()
    {
        $this->availableImages = [];
        for ($i = 1; $i <= 10; $i++) {
            $this->availableImages[] = 'planet-' . $i . '.png';
        }
    }

    public function startEditingImage()
    {
        $this->isEditingImage = true;
        $this->selectedImage = $this->planetInfo['image'] ?? 'planet-1.png';
    }

    public function cancelEditingImage()
    {
        $this->isEditingImage = false;
        $this->selectedImage = '';
    }

    public function selectPlanetImage($imageName)
    {
        $this->selectedImage = $imageName;
    }

    public function savePlanetImage()
    {
        try {
            if ($this->planet) {
                // Mettre à jour l'image de la planète
                $planet = Planet::find($this->planet['id']);
                if ($planet) {
                    $planet->image = $this->selectedImage;
                    $planet->save();

                    // Recharger les données de la planète
                    $this->loadPlanetData();
                    
                    // Réinitialiser l'état d'édition
                    $this->isEditingImage = false;
                    $this->selectedImage = '';

                    // Notification de succès
                    $this->dispatch('toast:success', [
                        'title' => 'Image mise à jour',
                        'text' => 'L\'image de la planète a été changée avec succès.'
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Notification d'erreur
            $this->dispatch('toast:error', [
                'title' => 'Erreur',
                'text' => 'Une erreur est survenue lors du changement d\'image.'
            ]);
        }
    }
    
    /**
     * Save production rate for a single resource
     */
    public function saveProductionRate($resourceName, $rate)
    {
        if (!$this->planet) {
            return;
        }

        try {
            $rate = max(0, min(100, (float)$rate));
            
            // Find the resource template
            $resourceTemplate = \App\Models\Template\TemplateResource::where('name', $resourceName)->first();
            if (!$resourceTemplate) {
                return;
            }

            // Get the actual planet model
            $planet = Planet::find($this->planet['id']);
            if (!$planet) {
                return;
            }

            // Update the planet resource
            $planetResource = $planet->resources()
                ->where('resource_id', $resourceTemplate->id)
                ->first();
                
            if ($planetResource) {
                $planetResource->update(['production_rate' => $rate]);
                
                // Update the local data
                $this->productionRates[$resourceName] = $rate;
                if (isset($this->planetResources[$resourceName])) {
                    $this->planetResources[$resourceName]['production_rate'] = $rate;
                    $baseProductionPerHour = $this->planetResources[$resourceName]['base_production_per_hour'];
                    $currentProductionPerHour = ($baseProductionPerHour * $rate / 100);
                    
                    $this->planetResources[$resourceName]['current_production_per_hour'] = $currentProductionPerHour;
                    $this->planetResources[$resourceName]['daily_production'] = $currentProductionPerHour * 24;
                }
            }
        } catch (\Exception $e) {
            // Silent fail for real-time updates
        }
    }
    
    /**
     * Update production rate for a specific resource (real-time UI update)
     */
    public function updateProductionRate($resourceName, $rate)
    {
        $rate = max(0, min(100, (float)$rate));
        $this->productionRates[$resourceName] = $rate;
        
        // Recalculate production per hour and daily production using the rate
        if (isset($this->planetResources[$resourceName])) {
            $baseProductionPerHour = $this->planetResources[$resourceName]['base_production_per_hour'];
            $currentProductionPerHour = ($baseProductionPerHour * $rate / 100);
            
            $this->planetResources[$resourceName]['current_production_per_hour'] = $currentProductionPerHour;
            $this->planetResources[$resourceName]['daily_production'] = $currentProductionPerHour * 24;
            $this->planetResources[$resourceName]['production_rate'] = $rate;
        }
    }

    /**
     * Activate shield protection for the planet
     */
    public function activateShieldProtection()
    {
        if (!$this->planet) {
            return;
        }

        $planet = Planet::find($this->planet['id']);
        if (!$planet) {
            return;
        }

        // Vérifier si la protection peut être activée (délai de 30 jours)
        if (!$planet->canActivateShieldProtection()) {
            $this->dispatch('toast:error', [
                'title' => 'Protection impossible',
                'text' => 'Vous devez attendre 30 jours entre chaque activation de la protection planétaire.'
            ]);
            return;
        }
        
        // Vérifier la technologie champ_inversion (niveau 5 minimum)
        $inversionField = UserTechnology::where('user_id', auth()->id())
            ->whereHas('technology', function($query) {
                $query->where('name', 'champ_inversion');
            })
            ->first();
            
        if (!$inversionField || $inversionField->level < 5) {
            $this->dispatch('toast:error', [
                'title' => 'Technologie requise',
                'text' => 'Vous devez avoir la technologie Champ d\'Inversion au niveau 5 minimum.'
            ]);
            return;
        }
        
        // Vérifier la présence de générateurs de bouclier
        $shieldGenerator = $planet->defenses()
            ->whereHas('defense', function($query) {
                $query->where('name', 'generateur_bouclier');
            })
            ->first();
            
        // Quantité requise: 10 générateurs par jour de protection (70 pour 7 jours)
        $requiredGenerators = 10 * 7; // 7 jours de protection
        
        if (!$shieldGenerator || $shieldGenerator->quantity < $requiredGenerators) {
            $this->dispatch('toast:error', [
                'title' => 'Défenses insuffisantes',
                'text' => "Vous avez besoin de {$requiredGenerators} Générateurs de Bouclier pour activer la protection pendant 7 jours."
            ]);
            return;
        }
        
        // Vérifier les ressources (deuterium)
        $deuterium = $planet->resources()
            ->whereHas('resource', function($query) {
                $query->where('name', 'deuterium');
            })
            ->first();
            
        // Coût: 5000 deuterium par jour de protection (35000 pour 7 jours)
        $requiredDeuterium = 5000 * 7; // 7 jours de protection
        
        if (!$deuterium || $deuterium->current_amount < $requiredDeuterium) {
            $this->dispatch('toast:error', [
                'title' => 'Ressources insuffisantes',
                'text' => "Vous avez besoin de {$requiredDeuterium} unités de Deuterium pour activer la protection pendant 7 jours."
            ]);
            return;
        }
        
        // Déduire le deuterium
        $deuterium->current_amount -= $requiredDeuterium;
        $deuterium->save();

        // Déduire les générateurs de bouclier
        $shieldGenerator->quantity -= $requiredGenerators;
        if ($shieldGenerator->quantity < 0) {
            $shieldGenerator->quantity = 0; // par sécurité, ne jamais négatif
        }
        $shieldGenerator->save();

        // Activer la protection pour 7 jours
        $now = now();
        $planet->shield_protection_active = true;
        $planet->shield_protection_start = $now;
        $planet->shield_protection_end = $now->copy()->addDays(7);
        $planet->last_shield_activation = $now;
        $planet->save();

        // Recharger les données de la planète
        $this->loadPlanetData();

        // Notification de succès
        $this->dispatch('toast:success', [
            'title' => 'Protection activée',
            'text' => 'La protection planétaire a été activée pour 7 jours.'
        ]);
        
        // Enregistrer l'action dans les logs
        $this->logAction(
            'activate_shield',
            'planet',
            'Activation de la protection planétaire',
            [
                'planet_id' => $planet->id,
                'duration_days' => 7,
                'deuterium_cost' => $requiredDeuterium,
                'shield_generators_spent' => $requiredGenerators
            ],
            $planet->id
        );
    }

    private function breakdownSeconds(int $seconds): array
    {
        $seconds = max(0, (int)$seconds);
        $days = intdiv($seconds, 86400);
        $seconds %= 86400;
        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;
        $minutes = intdiv($seconds, 60);
        $secs = $seconds % 60;
        return [
            'days' => $days,
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $secs,
        ];
    }

    private function formatCooldownText(int $seconds): string
    {
        if ($seconds <= 0) {
            return 'Disponible maintenant';
        }
        $b = $this->breakdownSeconds($seconds);
        $parts = [];
        if ($b['days'] > 0) {
            $parts[] = $b['days'] . ' ' . ($b['days'] > 1 ? 'jours' : 'jour');
        }
        if ($b['hours'] > 0) {
            $parts[] = $b['hours'] . ' ' . ($b['hours'] > 1 ? 'heures' : 'heure');
        }
        if ($b['minutes'] > 0) {
            $parts[] = $b['minutes'] . ' ' . ($b['minutes'] > 1 ? 'minutes' : 'minute');
        }
        if ($b['seconds'] > 0) {
            $parts[] = $b['seconds'] . ' ' . ($b['seconds'] > 1 ? 'secondes' : 'seconde');
        }
        return implode(' ', $parts);
    }

    public function render()
    {
        return view('livewire.game.manage-planet');
    }
}
