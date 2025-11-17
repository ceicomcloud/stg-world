<?php

namespace App\Services;

use App\Models\Planet\Planet;
use App\Models\Planet\PlanetResource;
use App\Models\Template\TemplateResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResourceService
{
    /**
     * Mettre à jour les ressources d'une planète en temps réel
     *
     * @param Planet $planet
     * @param bool $skipResearchPoints Si vrai, n'actualise pas les research_points (utile pour batchs)
     */
    public function updatePlanetResources(Planet $planet, bool $skipResearchPoints = false): array
    {
        try {
            DB::beginTransaction();

            $updatedResources = [];
            $planetResources = PlanetResource::where('planet_id', $planet->id)->get();
            
            // Vérifier si l'utilisateur est en mode vacances
            $user = $planet->user;
            $isInVacationMode = $user && $user->isInVacationMode();
            
            foreach ($planetResources as $planetResource) {
                $templateResource = TemplateResource::find($planetResource->resource_id);
                
                if (!$templateResource) {
                    continue;
                }

                // Mise à jour en temps réel toutes les 2 secondes
                $now = Carbon::now();
                $lastUpdate = Carbon::parse($planetResource->last_update);
                $secondsElapsed = $lastUpdate->diffInSeconds($now, true);
                
                // Si l'utilisateur est en mode vacances, pas de production
                if ($isInVacationMode) {
                    // Mettre à jour uniquement la date de dernière mise à jour
                    $planetResource->update([
                        'last_update' => $now
                    ]);
                    
                    continue;
                }
                
                // Calculer la production même si moins de 2 secondes se sont écoulées
                // Convertir en heures pour le calcul de production
                $hoursElapsed = $secondsElapsed / 3600;
                // Calculer la production basée sur les bâtiments
                $productionRate = $this->calculateProductionRate($planet, $templateResource);
                
                // Calculer la nouvelle quantité
                $production = $productionRate * $hoursElapsed;
                $newAmount = max(0, $planetResource->current_amount + $production);
                
                // Vérifier la capacité de stockage
                $storageCapacity = $this->calculateStorageCapacity($planet, $templateResource);
                $finalAmount = min($newAmount, $storageCapacity);

                // Mise à jour temps réel: appliquer dès qu'au moins 1 seconde s'est écoulée
                // ou si une production non nulle est calculée
                if ($production != 0.0 || $secondsElapsed >= 1) {
                    $planetResource->update([
                        'current_amount' => $finalAmount,
                        'last_update' => $now
                    ]);

                    $updatedResources[] = [
                        'id' => $planetResource->id,
                        'name' => $templateResource->name,
                        'previous_amount' => $planetResource->current_amount,
                        'new_amount' => $finalAmount,
                        'production' => $production,
                        'production_rate' => $productionRate,
                        'storage_capacity' => $storageCapacity
                    ];
                }
            }

            // Mettre à jour les research_points de l'utilisateur seulement s'il n'est pas en mode vacances
            // et si on ne demande pas explicitement de sauter cette étape (optimisation batch)
            if (!$isInVacationMode && !$skipResearchPoints) {
                $this->updateUserResearchPoints($planet);
            }

            DB::commit();
            
            if ($isInVacationMode) {
                Log::info('Pas de mise à jour des ressources pour la planète (mode vacances)', [
                    'planet_id' => $planet->id,
                    'planet_name' => $planet->name,
                    'user_id' => $user->id,
                    'user_name' => $user->name
                ]);
            } else {
                Log::info('Ressources mises à jour pour la planète', [
                    'planet_id' => $planet->id,
                    'planet_name' => $planet->name,
                    'resources_updated' => count($updatedResources)
                ]);
            }

            return $updatedResources;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour des ressources', [
                'planet_id' => $planet->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour toutes les ressources de toutes les planètes d'un utilisateur
     */
    public function updateAllUserResources(int $userId): array
    {
        $planets = Planet::where('user_id', $userId)->get();
        $allUpdatedResources = [];

        foreach ($planets as $planet) {
            $updatedResources = $this->updatePlanetResources($planet);
            $allUpdatedResources[$planet->id] = [
                'planet_name' => $planet->name,
                'resources' => $updatedResources
            ];
        }

        return $allUpdatedResources;
    }

    /**
     * Calculate production rate for a planet resource
     */
    private function calculateProductionRate(Planet $planet, TemplateResource $templateResource): float
    {
        return $planet->getResourceProductionRate($templateResource->id);
    }

    /**
     * Calculate storage capacity for a planet resource
     */
    private function calculateStorageCapacity(Planet $planet, TemplateResource $templateResource): int
    {
        return $planet->getStorageCapacity($templateResource->id);
    }

    /**
     * Obtenir un résumé des ressources d'une planète
     */
    public function getPlanetResourcesSummary(Planet $planet): array
    {
        $planetResources = PlanetResource::with('templateResource')
            ->where('planet_id', $planet->id)
            ->get();

        $summary = [
            'total_production' => 0,
            'total_energy_consumption' => 0,
            'total_energy_remaining' => 0,
            'resources' => []
        ];

        foreach ($planetResources as $planetResource) {
            $templateResource = $planetResource->templateResource;
            
            if (!$templateResource) {
                continue;
            }

            $resourceData = [
                'id' => $planetResource->id,
                'name' => $templateResource->name,
                'icon' => $templateResource->icon,
                'current_amount' => $planetResource->current_amount,
                'production_rate' => $planetResource->production_rate,
                'storage_capacity' => $planetResource->getStorageCapacity(),
                'is_energy' => $templateResource->is_energy ?? false
            ];

            $summary['resources'][] = $resourceData;

            // Calculer les totaux
            if ($templateResource->is_energy) {
                if ($planetResource->production_rate < 0) {
                    $summary['total_energy_consumption'] += abs($planetResource->production_rate);
                } else {
                    $summary['total_energy_remaining'] += $planetResource->production_rate;
                }
            } else {
                $summary['total_production'] += max(0, $planetResource->production_rate);
            }
        }

        return $summary;
    }

    /**
     * Vérifier si une planète a suffisamment de ressources pour une action
     */
    public function hasEnoughResources(Planet $planet, array $requiredResources): bool
    {
        foreach ($requiredResources as $resourceId => $requiredAmount) {
            $planetResource = PlanetResource::where('planet_id', $planet->id)
                ->where('template_resource_id', $resourceId)
                ->first();

            if (!$planetResource || $planetResource->current_amount < $requiredAmount) {
                return false;
            }
        }

        return true;
    }

    /**
     * Consommer des ressources d'une planète
     */
    public function consumeResources(Planet $planet, array $resourcesToConsume): bool
    {
        try {
            DB::beginTransaction();

            foreach ($resourcesToConsume as $resourceId => $amount) {
                $planetResource = PlanetResource::where('planet_id', $planet->id)
                    ->where('template_resource_id', $resourceId)
                    ->first();

                if (!$planetResource || $planetResource->current_amount < $amount) {
                    DB::rollBack();
                    return false;
                }

                $planetResource->decrement('current_amount', $amount);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la consommation des ressources', [
                'planet_id' => $planet->id,
                'resources' => $resourcesToConsume,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Mettre à jour les research_points de l'utilisateur basé sur la production de toutes ses planètes
     */
    public function updateUserResearchPoints(Planet $planet): void
    {
        $user = $planet->user;
        if (!$user) {
            return;
        }

        // Calculer la production totale de research_points de toutes les planètes de l'utilisateur
        $totalResearchProduction = 0;
        $userPlanets = $user->planets;

        foreach ($userPlanets as $userPlanet) {
            $researchCenter = $userPlanet->buildings()
                ->where('is_active', true)
                ->whereHas('build', function($query) {
                    $query->where('name', 'centre_recherche');
                })
                ->first();

            if ($researchCenter && $researchCenter->level > 0) {
                // Production de base : 10 points par niveau par heure
                $baseProduction = $researchCenter->level * 10;
                
                // Appliquer les bonus
                $productionBonus = \App\Models\Template\TemplateBuildAdvantage::getResearchPointsProduction($userPlanet->id);
                
                $totalResearchProduction += $baseProduction + $productionBonus;
            }
        }

        // Mise à jour en temps réel des research_points
        // Utiliser un cache par utilisateur pour mémoriser le dernier tick et un buffer de fractions
        $cacheKeyLast = 'research:last_tick:user:' . $user->id;
        $cacheKeyBuffer = 'research:buffer:user:' . $user->id;

        $now = Carbon::now();
        $lastTick = \Illuminate\Support\Facades\Cache::get($cacheKeyLast);
        if (!$lastTick) {
            // Initialiser sans ajouter de points au premier passage
            \Illuminate\Support\Facades\Cache::put($cacheKeyLast, $now, 86400);
            \Illuminate\Support\Facades\Cache::put($cacheKeyBuffer, 0.0, 86400);
            return;
        }

        $secondsElapsed = Carbon::parse($lastTick)->diffInSeconds($now, true);
        if ($secondsElapsed <= 0) {
            return;
        }

        $pointsToAddFloat = $totalResearchProduction * ($secondsElapsed / 3600.0);
        $buffer = (float) \Illuminate\Support\Facades\Cache::get($cacheKeyBuffer, 0.0);
        $accumulated = $buffer + $pointsToAddFloat;
        $pointsInt = (int) floor($accumulated);
        $remainder = $accumulated - $pointsInt;

        if ($pointsInt > 0) {
            $user->addResearchPoints($pointsInt);
            Log::info('Research points mis à jour (temps réel)', [
                'user_id' => $user->id,
                'points_added' => $pointsInt,
                'remainder' => $remainder,
                'total_production_per_hour' => $totalResearchProduction,
                'seconds_elapsed' => $secondsElapsed
            ]);
        }

        // Mettre à jour le cache
        \Illuminate\Support\Facades\Cache::put($cacheKeyLast, $now, 86400);
        \Illuminate\Support\Facades\Cache::put($cacheKeyBuffer, $remainder, 86400);
    }
}