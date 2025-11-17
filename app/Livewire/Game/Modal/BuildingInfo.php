<?php

namespace App\Livewire\Game\Modal;

use App\Models\Planet\Planet;
use App\Models\Planet\PlanetBuilding;
use App\Models\Planet\PlanetUnit;
use App\Models\Planet\PlanetResource;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplateBuildAdvantage;
use LivewireUI\Modal\ModalComponent;

class BuildingInfo extends ModalComponent
{
    public $buildingId;
    public $buildingName;
    public $buildingLevel;
    public $buildingQuantity;
    public $buildingData = [];
    public $planet;
    public $type = 'building';
    public $unitsRemoveQuantity = 1;
    public $confirmDestroy = false;
    public $confirmRemoveUnits = false;

    public function mount($buildingId = null, $type = 'building')
    {
        $this->buildingId = $buildingId;
        $this->type = $type;
        $this->planet = auth()->user()->getActualPlanet();
        
        if ($this->buildingId && $this->planet) {
            $this->loadBuildingData();
        }
    }

    public function loadBuildingData()
    {
        // Récupérer le template de l'élément
        $templateBuilding = TemplateBuild::where('id', $this->buildingId)
            ->with([
                'costs.resource',
                'requirements',
                'advantages',
                'advantages.targetResource',
                'disadvantages',
                'disadvantages.targetResource'
            ])
            ->first();

        if (!$templateBuilding) {
            $this->buildingName = 'Élément inconnu';
            $this->buildingLevel = 0;
            $this->buildingQuantity = 0;
            return;
        }

        // Récupérer l'élément de la planète selon le type
        $planetItem = $this->getPlanetItem();
        
        $this->buildingName = $templateBuilding->label;
        $isQuantityBased = $templateBuilding->max_level == 0;
        
        if ($isQuantityBased) {
            $this->buildingLevel = 0;
            $this->buildingQuantity = $planetItem ? $planetItem->quantity : 0;
        } else {
            $this->buildingLevel = $planetItem ? $planetItem->level : 0;
            $this->buildingQuantity = 0;
        }
        
        // Préparer les données de l'élément
        $this->buildingData = [
            'label' => $templateBuilding->label,
            'description' => $templateBuilding->description,
            'category' => $templateBuilding->category,
            'icon' => $templateBuilding->icon,
            'max_level' => $templateBuilding->max_level,
            'is_quantity_based' => $isQuantityBased,
            'type' => $this->type,
            // Statistiques pour les unités, défenses et vaisseaux
            'life' => $templateBuilding->life,
            'attack_power' => $templateBuilding->attack_power,
            'shield_power' => $templateBuilding->shield_power,
            'speed' => $templateBuilding->speed,
            'cargo_capacity' => $templateBuilding->cargo_capacity,
            'fuel_consumption' => $templateBuilding->fuel_consumption,
            'advantages' => $templateBuilding->advantages->map(function($advantage) use ($templateBuilding) {
                // Pour les stockages, afficher directement la valeur du prochain niveau
                if (in_array($advantage->advantage_type, ['storage_bonus', 'storage_capacity', 'storage_increase'])) {
                    $resourceName = $advantage->targetResource ? ($advantage->targetResource->label ?? $advantage->targetResource->display_name ?? '') : '';
                    $bonusNext = (int) round($advantage->calculateValueForLevel($this->buildingLevel + 1));
                    $baseStorage = (int) ($advantage->targetResource->base_storage ?? 0);
                    $totalNext = max(0, $baseStorage + $bonusNext);
                    $formatted = number_format($totalNext, 0, ',', ' ');
                    $desc = $resourceName
                        ? "Stockage de {$resourceName} : +{$formatted}"
                        : "Capacité de stockage : +{$formatted}";
                    return [
                        'name' => $advantage->name,
                        'description' => $desc
                    ];
                }

                // Capacité de stockage du bunker: afficher le gain du prochain niveau (delta)
                if ($advantage->advantage_type === 'bunker_boost') {
                    // Utiliser la méthode dédiée pour respecter la progression triangulaire
                    $currentTotal = (int) round(TemplateBuildAdvantage::getBunkerBoost($templateBuilding->id, max(0, (int) $this->buildingLevel)));
                    $nextTotal = (int) round(TemplateBuildAdvantage::getBunkerBoost($templateBuilding->id, (int) $this->buildingLevel + 1));
                    $delta = max(0, $nextTotal - $currentTotal);
                    $formatted = number_format($delta, 0, ',', ' ');
                    return [
                        'name' => $advantage->name,
                        'description' => "Capacité de stockage du bunker (niveau suivant) : +{$formatted}"
                    ];
                }

                // Production de ressource: afficher valeur du prochain niveau
                if ($advantage->advantage_type === 'production_boost') {
                    $resourceName = $advantage->targetResource ? ($advantage->targetResource->label ?? $advantage->targetResource->display_name ?? '') : '';
                    $nextVal = $advantage->calculateValueForLevel($this->buildingLevel + 1);
                    $suffix = $advantage->is_percentage ? '%' : '';
                    $formatted = $advantage->is_percentage
                        ? rtrim(rtrim(number_format($nextVal, 2, ',', ' '), '0'), ',')
                        : number_format((int) round($nextVal), 0, ',', ' ');
                    $label = $resourceName ? "Production de {$resourceName}" : "Boost de production";
                    return [
                        'name' => $advantage->name,
                        'description' => "$label (niveau suivant) : +{$formatted}{$suffix}"
                    ];
                }

                // Production d'énergie: prochain niveau
                if ($advantage->advantage_type === 'energy_production') {
                    $nextVal = $advantage->calculateValueForLevel($this->buildingLevel + 1);
                    $formatted = number_format((int) round($nextVal), 0, ',', ' ');
                    return [
                        'name' => $advantage->name,
                        'description' => "Production d'énergie (niveau suivant) : +{$formatted}"
                    ];
                }

                // Expansion territoriale: prochain niveau (affiche les cases ajoutées)
                if ($advantage->advantage_type === 'territory_expansion') {
                    $nextVal = $advantage->calculateValueForLevel($this->buildingLevel + 1);
                    $formatted = number_format((int) round($nextVal), 0, ',', ' ');
                    return [
                        'name' => $advantage->name,
                        'description' => "Expansion territoriale (niveau suivant) : +{$formatted} cases"
                    ];
                }

                // Vitesse de recherche: afficher les points de recherche produits au prochain niveau
                if ($advantage->advantage_type === 'research_speed') {
                    $bonusNext = $advantage->calculateValueForLevel($this->buildingLevel + 1);
                    $baseNext = 0;
                    if (($templateBuilding->name ?? '') === 'centre_recherche') {
                        $baseNext = ($this->buildingLevel + 1) * 10; // 10 points/heure par niveau
                    }
                    $totalNext = $baseNext + $bonusNext;
                    $formatted = number_format((int) round($totalNext), 0, ',', ' ');
                    return [
                        'name' => $advantage->name,
                        'description' => "Points de recherche (niveau suivant) : +{$formatted}"
                    ];
                }

                return [
                    'name' => $advantage->name,
                    'description' => $advantage->getDescriptionAttribute()
                ];
            })->toArray(),
            'disadvantages' => $templateBuilding->disadvantages->map(function($disadvantage) {
                // Consommation d'énergie: afficher coût réel au prochain niveau
                if ($disadvantage->disadvantage_type === 'energy_consumption') {
                    $nextVal = $disadvantage->calculateValueForLevel($this->buildingLevel + 1);
                    $formatted = number_format((int) round($nextVal), 0, ',', ' ');
                    return [
                        'name' => $disadvantage->name,
                        'description' => "Consommation d'énergie (niveau suivant) : -{$formatted}"
                    ];
                }

                return [
                    'name' => $disadvantage->name,
                    'description' => $disadvantage->getDescriptionAttribute()
                ];
            })->toArray(),
            'requirements' => $this->getBuildingRequirements($templateBuilding)
        ];
    }
    
    public function getPlanetItem()
    {
        switch ($this->type) {
            case 'unit':
                return $this->planet->units->where('unit_id', $this->buildingId)->first();
            case 'defense':
                return $this->planet->defenses->where('defense_id', $this->buildingId)->first();
            case 'ship':
                return $this->planet->ships->where('ship_id', $this->buildingId)->first();
            default:
                return $this->planet->buildings->where('building_id', $this->buildingId)->first();
        }
    }

    public function getBuildingRequirements($templateBuilding)
    {
        $requirements = [];
        
        foreach ($templateBuilding->requirements as $requirement) {
            $requirements[] = [
                'required_build' => $requirement->requiredBuild,
                'required_level' => $requirement->required_level
            ];
        }
        
        return $requirements;
    }

    public function checkRequirement($requirement)
    {
        $requiredBuild = $requirement['required_build'];
        
        if (!$requiredBuild) {
            return false;
        }
        
        $planetBuilding = $this->planet->buildings
            ->where('building_id', $requiredBuild['id'])
            ->where('is_active', true)
            ->first();
        
        if (!$planetBuilding) {
            return false;
        }
        
        return $planetBuilding->level >= $requirement['required_level'];
    }
    
    public function getTypeIcon()
    {
        switch ($this->type) {
            case 'unit':
                return 'users';
            case 'defense':
                return 'shield-alt';
            case 'ship':
                return 'rocket';
            default:
                return 'building';
        }
    }
    
    public function getTypeLabel()
    {
        switch ($this->type) {
            case 'unit':
                return 'Unité';
            case 'defense':
                return 'Défense';
            case 'ship':
                return 'Vaisseau';
            default:
                return 'Bâtiment';
        }
    }

    /**
     * Détruire le bâtiment et rembourser 50% des coûts cumulés
     */
    public function destroyBuilding(): void
    {
        if ($this->type !== 'building' || !$this->planet) {
            return;
        }

        $planetBuilding = $this->getPlanetItem();
        if (!$planetBuilding instanceof PlanetBuilding || $planetBuilding->level <= 0) {
            $this->dispatch('toast:error', [
                'title' => 'Bâtiment',
                'text' => 'Aucun niveau à détruire.'
            ]);
            return;
        }

        $templateBuilding = TemplateBuild::with('costs.resource')->find($this->buildingId);
        if (!$templateBuilding) {
            return;
        }

        // Calculer le remboursement: 50% du coût cumulé jusqu’au niveau actuel
        $refunds = [];
        foreach ($templateBuilding->costs as $cost) {
            $totalCost = $cost->getTotalCostToLevel((int) $planetBuilding->level);
            $refundAmount = (int) floor($totalCost * 0.5);
            $resourceName = $cost->resource->name;
            $refunds[$resourceName] = ($refunds[$resourceName] ?? 0) + $refundAmount;
        }

        // Appliquer les remboursements aux ressources de la planète (avec limite de stockage)
        foreach ($refunds as $resourceName => $amount) {
            $planetResource = $this->planet->resources()
                ->whereHas('resource', function($q) use ($resourceName) {
                    $q->where('name', $resourceName);
                })
                ->first();

            if ($planetResource instanceof PlanetResource) {
                $added = $planetResource->addResources($amount);
                // Optionnel: indiquer si capacité a limité
            }
        }

        // Ramener le bâtiment au niveau 0 (libère les champs via hook updating)
        $planetBuilding->update(['level' => 0]);

        // Rafraîchir l’UI
        $this->loadBuildingData();
        $this->dispatch('resourcesUpdated');
        $this->dispatch('toast:success', [
            'title' => 'Bâtiment détruit',
            'text' => 'Remboursement instantané de 50% des coûts cumulés.'
        ]);
    }

    /**
     * Supprimer des unités et rembourser 50% du coût de la quantité sélectionnée
     */
    public function removeUnits(): void
    {
        if ($this->type !== 'unit' || !$this->planet) {
            return;
        }

        $planetUnit = $this->getPlanetItem();
        if (!$planetUnit instanceof PlanetUnit) {
            return;
        }

        $quantity = max(1, (int) $this->unitsRemoveQuantity);
        $quantity = min($quantity, (int) ($planetUnit->quantity ?? 0));

        if ($quantity <= 0) {
            $this->dispatch('toast:error', [
                'title' => 'Unités',
                'text' => 'Quantité invalide à supprimer.'
            ]);
            return;
        }

        // Calculer coût et remboursement (50%)
        $costs = $planetUnit->getBuildCost($quantity);
        foreach ($costs as $resourceName => $costAmount) {
            $refundAmount = (int) floor($costAmount * 0.5);
            $planetResource = $this->planet->resources()
                ->whereHas('resource', function($q) use ($resourceName) {
                    $q->where('name', $resourceName);
                })
                ->first();

            if ($planetResource instanceof PlanetResource) {
                $planetResource->addResources($refundAmount);
            }
        }

        // Décrémenter les unités
        $planetUnit->removeUnits($quantity);

        // Rafraîchir
        $this->loadBuildingData();
        $this->dispatch('resourcesUpdated');
        $this->dispatch('toast:success', [
            'title' => 'Unités supprimées',
            'text' => 'Suppression effectuée. 50% du coût remboursé.'
        ]);
    }

    /**
     * Aperçu des remboursements de destruction du bâtiment (50%)
     */
    public function getBuildingRefundPreview(): array
    {
        if ($this->type !== 'building') {
            return [];
        }
        $planetBuilding = $this->getPlanetItem();
        if (!$planetBuilding instanceof PlanetBuilding || $planetBuilding->level <= 0) {
            return [];
        }
        $templateBuilding = TemplateBuild::with('costs.resource')->find($this->buildingId);
        if (!$templateBuilding) {
            return [];
        }
        $preview = [];
        foreach ($templateBuilding->costs as $cost) {
            $totalCost = $cost->getTotalCostToLevel((int) $planetBuilding->level);
            $refundAmount = (int) floor($totalCost * 0.5);
            $preview[] = [
                'resource' => ($cost->resource->label ?? $cost->resource->display_name ?? $cost->resource->name),
                'amount' => $refundAmount,
            ];
        }
        return $preview;
    }

    /**
     * Aperçu des remboursements pour suppression d’unités (50%)
     */
    public function getUnitRefundPreview(): array
    {
        if ($this->type !== 'unit') {
            return [];
        }
        $planetUnit = $this->getPlanetItem();
        if (!$planetUnit instanceof PlanetUnit) {
            return [];
        }
        $quantity = max(1, (int) $this->unitsRemoveQuantity);
        $quantity = min($quantity, (int) ($planetUnit->quantity ?? 0));
        if ($quantity <= 0) {
            return [];
        }
        $costs = $planetUnit->getBuildCost($quantity);
        $preview = [];
        foreach ($costs as $resourceName => $costAmount) {
            $refundAmount = (int) floor($costAmount * 0.5);
            // Trouver le label de la ressource
            $templateRes = optional($this->planet->resources->first(function($res) use ($resourceName) {
                return $res->resource && $res->resource->name === $resourceName;
            }))->resource;
            $label = $templateRes?->label ?? $templateRes?->display_name ?? $resourceName;
            $preview[] = [
                'resource' => $label,
                'amount' => $refundAmount,
            ];
        }
        return $preview;
    }

    public function render()
    {
        return view('livewire.game.modal.building-info');
    }

    /**
     * UI: demander confirmation de destruction
     */
    public function requestDestroyBuilding(): void
    {
        if ($this->type !== 'building') return;
        $planetBuilding = $this->getPlanetItem();
        if (!$planetBuilding instanceof PlanetBuilding || $planetBuilding->level <= 0) return;
        $this->confirmDestroy = true;
    }

    public function cancelDestroyBuilding(): void
    {
        $this->confirmDestroy = false;
    }

    public function confirmDestroyBuilding(): void
    {
        $this->confirmDestroy = false;
        $this->destroyBuilding();
    }

    /**
     * UI: demander confirmation de suppression d’unités
     */
    public function requestRemoveUnits(): void
    {
        if ($this->type !== 'unit') return;
        $planetUnit = $this->getPlanetItem();
        if (!$planetUnit instanceof PlanetUnit || $planetUnit->quantity <= 0) return;
        $this->confirmRemoveUnits = true;
    }

    public function cancelRemoveUnits(): void
    {
        $this->confirmRemoveUnits = false;
    }

    public function confirmRemoveUnits(): void
    {
        $this->confirmRemoveUnits = false;
        $this->removeUnits();
    }

    // Variante pour éviter un conflit potentiel avec la propriété du même nom
    public function confirmRemoveUnitsAction(): void
    {
        $this->confirmRemoveUnits = false;
        $this->removeUnits();
    }
}