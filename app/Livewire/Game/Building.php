<?php

namespace App\Livewire\Game;

use App\Models\Planet\Planet;
use App\Models\Planet\PlanetBuilding;
use App\Models\Planet\PlanetUnit;
use App\Models\Planet\PlanetDefense;
use App\Models\Planet\PlanetShip;
use App\Models\Planet\PlanetEquip;
use App\Models\Planet\PlanetResource;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplateBuildCost;
use App\Models\Template\TemplateBuildRequired;
use App\Models\Other\Queue;
use App\Jobs\ProcessQueueJob;
use App\Services\DailyQuestService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use App\Traits\LogsUserActions;
use App\Models\Server\ServerConfig;
use App\Services\TemplateCacheService;
use App\Support\Device;

#[Layout('components.layouts.game')]
class Building extends Component
{
    use LogsUserActions;
    public $planet;
    public $planetResources = [];
    public $buildings = [];
    public $type = 'building'; // building, unit, defense, ship
    public $quantities = []; // Quantités individuelles par bâtiment

    public $timeRemaining = 0;

    // Gestion d'équipe (PlanetEquip)
    public $equipTeams = [];
    public $equipEditId = null;
    public $equipCategory = PlanetEquip::CATEGORY_EARTH; // 'earth' ou 'spatial'
    public $equipLabel = '';
    public $equipTeamIndex = 1;
    public $equipNotes = '';
    public $equipPayloadUnits = []; // [template_unit_id => quantity]
    public $equipPayloadShips = []; // [template_ship_id => quantity]
    // Stats/limites
    public $equipMaxLimit = 0;
    public $equipCountTotal = 0;
    public $equipCountEarth = 0;
    public $equipCountSpatial = 0;

    public function mount($type = 'building')
    {
        $this->type = $type;
        
        // Récupérer la planète actuelle de l'utilisateur avec toutes les relations nécessaires
        $this->planet = auth()->user()->getActualPlanet();
        
        if (!$this->planet) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Aucune planète trouvée.'
            ]);
            return;
        }

        // Charger toutes les relations en une seule fois pour optimiser les requêtes
        $relations = [
            'buildings.build.costs.resource',
            'buildings.build.requirements',
            'units.unit.costs.resource',
            'units.unit.requirements',
            'defenses.defense.costs.resource', 
            'defenses.defense.requirements',
            'ships.ship.costs.resource',
            'ships.ship.requirements',
            'resources.resource'
        ];
        
        $this->planet->load($relations);

        $this->loadPlanetResources();
        if ($this->type === 'equip') {
            $this->loadEquipTeams();
            $this->prepareEquipPayloadSources();
            $this->buildings = [];
        } else {
            $this->loadBuildings();
        }
    }
    
    public function loadBuildings(TemplateCacheService $templateCache = null)
    {
        $templateCache = $templateCache ?? app(TemplateCacheService::class);
        // Récupérer les éléments selon le type
        $templateBuildings = $templateCache->getTemplateBuildsByType($this->type);

        // Normaliser en tableau si on obtient une Collection Eloquent
        if ($templateBuildings instanceof \Illuminate\Support\Collection) {
            $templateBuildings = $templateBuildings->all();
        }

        // Trier par ordre de déblocage (prérequis les plus faibles en premier)
        usort($templateBuildings, function ($a, $b) {
            $scoreA = $this->getUnlockScore($a);
            $scoreB = $this->getUnlockScore($b);
            if ($scoreA === $scoreB) {
                // Départager par nombre de prérequis puis label
                $countA = $a->requirements?->count() ?? 0;
                $countB = $b->requirements?->count() ?? 0;
                if ($countA === $countB) {
                    return strcmp($a->label, $b->label);
                }
                return $countA <=> $countB;
            }
            return $scoreA <=> $scoreB;
        });

        $this->buildings = [];
        
        foreach ($templateBuildings as $templateBuilding) {
            // Récupérer l'élément de la planète selon le type
            $planetItem = $this->getPlanetItem($templateBuilding->id);
            
            $isQuantityBased = $templateBuilding->max_level == 0;
            $nextLevel = $isQuantityBased ? 1 : ($planetItem ? $planetItem->level + 1 : 1);

            $building = [
                'id' => $templateBuilding->id,
                'name' => $templateBuilding->name,
                'label' => $templateBuilding->label,
                'description' => $templateBuilding->description,
                'category' => $templateBuilding->category,
                'icon' => $templateBuilding->icon,
                'level' => $isQuantityBased ? 0 : ($planetItem ? $planetItem->level : 0),
                'quantity' => $isQuantityBased ? ($planetItem ? $planetItem->quantity : 0) : 0,
                'max_level' => $templateBuilding->max_level,
                'is_quantity_based' => $isQuantityBased,
                'is_constructing' => $this->isBuildingInQueue($templateBuilding->id),
                'build_end_time' => $this->getBuildingEndTime($templateBuilding->id),
                'can_upgrade' => $this->canBuild($templateBuilding, $planetItem),
                'has_insufficient_resources' => !$this->hasEnoughResources($templateBuilding, $nextLevel, $templateBuilding->id),
                'has_insufficient_fields' => $this->type === 'building' && !$planetItem && !$this->planet->hasAvailableFields(1),
                'costs' => $this->getBuildingCosts($templateBuilding, $nextLevel),
                'build_time' => $this->getBuildingTime($templateBuilding, $nextLevel),
                'requirements' => $this->getBuildingRequirements($templateBuilding),
                'advantages' => $templateBuilding->advantages->map(function($advantage) {
                    $array = $advantage->toArray();
                    $array['description'] = $advantage->getDescriptionAttribute();
                    return $array;
                })->toArray(),
                'disadvantages' => $templateBuilding->disadvantages->map(function($disadvantage) {
                    $array = $disadvantage->toArray();
                    $array['description'] = $disadvantage->getDescriptionAttribute();
                    return $array;
                })->toArray(),
                'planet_item' => $planetItem
            ];

            $this->buildings[] = $building;
        }

        // Trier les éléments affichés par identifiant croissant
        usort($this->buildings, function ($a, $b) {
            return ($a['id'] ?? 0) <=> ($b['id'] ?? 0);
        });
    }

    /**
     * Calcul d'un score d'ordre de déblocage basé sur les prérequis
     * - 0 si aucun prérequis
     * - max(required_level) + léger tie-breaker par nb de prérequis
     */
    private function getUnlockScore($templateBuilding): float
    {
        $reqs = $templateBuilding->requirements ?? collect();
        if ($reqs->isEmpty()) {
            return 0.0;
        }
        $maxLevel = $reqs->max('required_level') ?? 0;
        $count = $reqs->count();
        // Ajout d'un très faible poids au nombre de prérequis pour un tri stable
        return (float) ($maxLevel + ($count * 0.01));
    }
    
    public function getPlanetItem($itemId)
    {
        switch ($this->type) {
            case 'unit':
                return $this->planet->units->where('unit_id', $itemId)->first();
            case 'defense':
                return $this->planet->defenses->where('defense_id', $itemId)->first();
            case 'ship':
                return $this->planet->ships->where('ship_id', $itemId)->first();
            default:
                return $this->planet->buildings->where('building_id', $itemId)->first();
        }
    }


    public function updateConstructionTimer()
    {
        // Vérifier si une construction est terminée
        $completedItems = Queue::getCompletedItems($this->planet->id, $this->type);
        
        foreach ($completedItems as $item) {
            $item->complete();
        }
        
        // Recharger après traitement
        if ($completedItems->isNotEmpty()) {
            $this->mount($this->type);
        }
    }

    public function isBuildingInQueue($buildingId)
    {
        return Queue::active()
            ->notCompleted()
            ->forPlanet($this->planet->id)
            ->ofType($this->type)
            ->where('item_id', $buildingId)
            ->exists();
    }
    
    public function getBuildingEndTime($buildingId)
    {
        $queueItem = Queue::active()
            ->notCompleted()
            ->forPlanet($this->planet->id)
            ->ofType($this->type)
            ->where('item_id', $buildingId)
            ->first();
            
        return $queueItem ? $queueItem->end_time : null;
    }

    public function loadPlanetResources()
    {
        // Utiliser la relation Eloquent pour charger les ressources
        $this->planetResources = $this->planet->resources->mapWithKeys(function ($planetResource) {
            return [$planetResource->resource->name => $planetResource->current_amount];
        })->toArray();
    }

    public function canBuild($templateBuilding, $planetItem)
    {
        // Vérifier les prérequis
        foreach ($templateBuilding->requirements as $requirement) {
            if (!$this->checkRequirement($requirement)) {
                return false;
            }
        }
        
        // Pour les bâtiments avec max_level > 0, vérifier le niveau max
        if ($templateBuilding->max_level > 0 && $planetItem && $planetItem->level >= $templateBuilding->max_level) {
            return false;
        }
        
        // Pour les nouveaux bâtiments, vérifier les champs disponibles
        if ($this->type === 'building' && !$planetItem && !$this->planet->hasAvailableFields(1)) {
            return false;
        }
        
        return true;
    }

    public function hasEnoughResources($templateBuilding, $level, $buildingId = null)
    {
        $costs = $this->getBuildingCosts($templateBuilding, $level);
        $quantity = $buildingId ? $this->getQuantity($buildingId) : 1;
        
        foreach ($costs as $resourceName => $costData) {
            $cost = $costData['amount'];
            $totalCost = $cost * $quantity;
            if (($this->planetResources[$resourceName] ?? 0) < $totalCost) {
                return false;
            }
        }
        
        return true;
    }
    
    public function setQuantity($buildingId, $quantity)
    {
        $this->quantities[$buildingId] = max(1, min(999, (int)$quantity));
    }
    
    public function getQuantity($buildingId)
    {
        return $this->quantities[$buildingId] ?? 1;
    }

    public function checkRequirement($requirement)
    {
        // Utiliser la relation pour vérifier les prérequis
        $requiredBuilding = $this->planet->buildings
            ->where('building_id', $requirement['required_build_id'])
            ->where('is_active', true)
            ->first();
            
        if (!$requiredBuilding) {
            return false;
        }
        
        return $requiredBuilding->level >= $requirement['required_level'];
    }

    public function getBuildingCosts($templateBuilding, $level)
    {
        $costs = [];
        
        foreach ($templateBuilding->costs as $cost) {
            $baseCost = $cost->calculateCostForLevel($level);
            
            // Appliquer le bonus de faction pour le coût des bâtiments
            $finalCost = $baseCost;
            $user = $this->planet->user;
            
            if ($user && $user->faction) {
                $buildingCostBonus = $user->faction->getBonusBuildingCost();
                if ($buildingCostBonus < 0) { // Bonus négatif = réduction de coût
                    $finalCost = $baseCost * (1 + $buildingCostBonus / 100);
                }
            }
            
            $costs[$cost->resource->name] = [
                'amount' => (int) $finalCost,
                'icon' => $cost->resource->icon,
                'color' => $cost->resource->color
            ];
        }
        
        return $costs;
    }

    public function getBuildingTime($templateBuilding, $level)
    {
        $baseTime = $templateBuilding->base_build_time;
        $calculatedTime = $baseTime * pow(1.2, $level - 1);
        
        // Appliquer le bonus de faction pour la vitesse de construction
        $user = $this->planet->user;
        if ($user && $user->faction) {
            $buildingSpeedBonus = $user->faction->getBonusBuildingSpeed();
            if ($buildingSpeedBonus > 0) {
                // Bonus positif = réduction du temps de construction
                $calculatedTime = $calculatedTime * (1 - $buildingSpeedBonus / 100);
            }
        }
        
        // Appliquer le bonus de vitesse de construction basé sur le niveau des bâtiments spécifiques
        $targetType = '';
        
        if ($templateBuilding->type === 'building') {
            $targetType = \App\Models\Template\TemplateBuildAdvantage::TARGET_BUILD;
        } else {
            switch ($templateBuilding->type) {
                case 'unit':
                    $targetType = \App\Models\Template\TemplateBuildAdvantage::TARGET_UNIT;
                break;
                case 'defense':
                    $targetType = \App\Models\Template\TemplateBuildAdvantage::TARGET_DEFENSE;
                break;
                case 'ship':
                    $targetType = \App\Models\Template\TemplateBuildAdvantage::TARGET_SHIP;
                break;
            }
        }

        if (!empty($targetType)) {
            $buildSpeedBonus = \App\Models\Template\TemplateBuildAdvantage::getBuildSpeedBonus($this->planet->id, $targetType);
            if ($buildSpeedBonus > 0) {
                $calculatedTime = $calculatedTime - $buildSpeedBonus;
            }
        }
        // Durée minimale de 30 secondes pour éviter 00:00:00
        return (int) max(30, $calculatedTime);
    }

    public function getBuildingRequirements($templateBuilding)
    {
        $requirements = [];
        
        foreach ($templateBuilding->requirements as $requirement) {
            $requiredBuilding = TemplateBuild::find($requirement->required_build_id);
            $planetBuilding = $this->planet->buildings
                ->where('building_id', $requirement->required_build_id)
                ->where('is_active', true)
                ->first();
                
            $requirements[] = [
                'required_build' => $requiredBuilding,
                'required_build_id' => $requirement->required_build_id,
                'required_level' => $requirement->required_level,
                'current_level' => $planetBuilding ? $planetBuilding->level : 0,
                'is_met' => $planetBuilding && $planetBuilding->level >= $requirement->required_level
            ];
        }
        
        return $requirements;
    }

    public function openBuildingModal($buildingId)
    {
        $this->dispatch('openModal', component: 'game.modal.building-info', arguments: [
            'title' => $this->getModalTitle(),
            'buildingId' => $buildingId,
            'type' => $this->type,
        ]);
    }



    public function upgradeBuilding($buildingId)
    {
        // Vérifier les limites de construction concurrentes
        if ($this->type === 'building') {
            $activeCount = \App\Models\Other\Queue::countActiveQueue($this->planet->id, 'building');
            $user = \App\Models\User::find($this->planet->user_id);
            $limit = $user ? $user->getMaxConcurrentBuildingCount() : 1;
            if ($activeCount >= $limit) {
                $this->dispatch('toast:error', [
                    'title' => 'Limite atteinte',
                    'text' => 'Limite de constructions: ' . ($user && $user->vip_active ? 'VIP 3' : 'Hors VIP 1')
                ]);
                return;
            }
        } else {
            // Pour unités/défense/vaisseaux: appliquer la même limite que VIP bâtiments
            $activeCount = \App\Models\Other\Queue::countActiveQueue($this->planet->id, $this->type);
            $user = \App\Models\User::find($this->planet->user_id);
            $limit = $user ? $user->getMaxConcurrentProductionCount() : 1;
            if ($activeCount >= $limit) {
                $this->dispatch('toast:error', [
                    'title' => 'Limite atteinte',
                    'text' => 'Limite de productions: ' . ($user && $user->vip_active ? 'VIP 3' : 'Hors VIP 1')
                ]);
                return;
            }
        }

        $building = collect($this->buildings)->firstWhere('id', $buildingId);
        
        if (!$building) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Élément non trouvé.'
            ]);
            return;
        }

        // Vérifier les ressources avec quantité
        foreach ($building['costs'] as $resourceName => $costData) {
            $cost = $costData['amount'];
            $totalCost = $cost * $this->getQuantity($buildingId);
            if (($this->planetResources[$resourceName] ?? 0) < $totalCost) {
                $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Ressources insuffisantes pour cette construction.'
            ]);
                return;
            }
        }

            DB::transaction(function () use ($building, $buildingId) {
                // Préparer les données pour le log
                $resourcesSpent = [];
                
                // Consommer les ressources
                foreach ($building['costs'] as $resourceName => $costData) {
                    $cost = $costData['amount'];
                    $totalCost = $cost * $this->getQuantity($buildingId);
                    $resourcesSpent[$resourceName] = $totalCost;
                    
                    $planetResource = $this->planet->resources()
                        ->whereHas('resource', function($query) use ($resourceName) {
                            $query->where('name', $resourceName);
                        })
                        ->first();
                        
                    if ($planetResource) {
                        $planetResource->decrement('current_amount', $totalCost);
                    }
                }

                // Créer ou mettre à jour l'élément de la planète
                $planetItem = $this->createOrUpdatePlanetItem($buildingId, $building);

                // Calculer la durée totale pour les éléments basés sur la quantité
                $totalDuration = $building['is_quantity_based'] 
                    ? $building['build_time'] * $this->getQuantity($buildingId)
                    : $building['build_time'];

                // Ajouter à la file de construction
                Queue::addToQueue([
                    'planet_id' => $this->planet->id,
                    'user_id' => null,
                    'type' => $this->type,
                    'item_id' => $buildingId,
                    'level' => $building['is_quantity_based'] ? 1 : ($planetItem->level ?? 0) + 1,
                    'quantity' => $building['is_quantity_based'] ? $this->getQuantity($buildingId) : 1,
                    'duration' => $totalDuration,
                    'cost' => $building['costs']
                ]);

                // Log resource spending
                $this->logResourceSpend($resourcesSpent, $this->planet->id);

                // Enregistrer les ressources dépensées pour l'événement (construction)
                $spentTotal = (int) array_sum(array_map('intval', array_values($resourcesSpent)));
                if ($spentTotal > 0 && $this->planet && $this->planet->user_id) {
                    app(\App\Services\EventService::class)->recordConstructionSpent($this->planet->user_id, $spentTotal);
                }
                
                // Log building action
                $this->logBuildingPurchase(
                    $building['label'],
                    $buildingId,
                    $resourcesSpent,
                    $this->planet->id
                );

                $this->dispatch('resource-refresh'); 

                // Programmer le traitement de la file
                ProcessQueueJob::dispatch($this->planet->id, $this->type)
                    ->delay(now()->addSeconds($totalDuration));
            });

            $typeLabel = match($this->type) {
                'unit' => 'unité',
                'defense' => 'défense',
                'ship' => 'vaisseau',
                default => 'bâtiment'
            };
            
            $this->dispatch('toast:success', [
                'title' => 'Succès!',
                'text' => 'Construction de ' . $building['label'] . ' (' . $typeLabel . ') ajoutée à la file !'
            ]);

            // Incrémenter la quête quotidienne correspondante (construction ou production)
            $user = Auth::user();
            if ($user) {
                if ($this->type === 'building') {
                    app(DailyQuestService::class)->incrementProgress($user, 'start_building');
                } else {
                    $map = [
                        'unit' => 'produce_unit',
                        'defense' => 'produce_defense',
                        'ship' => 'produce_ship',
                    ];
                    if (isset($map[$this->type])) {
                        app(DailyQuestService::class)->incrementProgress($user, $map[$this->type]);
                    }
                }
            }

            $this->dispatch('resourcesUpdated');
            
            // Recharger les données
            $this->mount($this->type);
            
     
    }
    
    private function createOrUpdatePlanetItem($itemId, $building)
    {
        switch ($this->type) {
            case 'unit':
                return PlanetUnit::firstOrCreate(
                    ['planet_id' => $this->planet->id, 'unit_id' => $itemId],
                    ['quantity' => 0, 'is_active' => true]
                );
            case 'defense':
                return PlanetDefense::firstOrCreate(
                    ['planet_id' => $this->planet->id, 'defense_id' => $itemId],
                    ['quantity' => 0, 'is_active' => true]
                );
            case 'ship':
                return PlanetShip::firstOrCreate(
                    ['planet_id' => $this->planet->id, 'ship_id' => $itemId],
                    ['quantity' => 0, 'is_active' => true]
                );
            default:
                return PlanetBuilding::firstOrCreate(
                    ['planet_id' => $this->planet->id, 'building_id' => $itemId],
                    ['level' => 0, 'is_active' => true]
                );
        }
    }

    public function formatTime($seconds)
    {
        if ($seconds <= 0) {
            return '00:00:00';
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function formatNumber($number)
    {
        return number_format($number, 0, ',', ' ');
    }

    public function getPageTitle()
    {
        return match($this->type) {
            'unit' => 'Unités',
            'defense' => 'Défenses',
            'ship' => 'Vaisseaux',
            default => 'Bâtiments'
        };
    }
    
    public function getPageIcon()
    {
        return match($this->type) {
            'unit' => 'fas fa-users',
            'defense' => 'fas fa-shield-alt',
            'ship' => 'fas fa-rocket',
            default => 'fas fa-building'
        };
    }
    
    public function getModalTitle()
    {
        return match($this->type) {
            'unit' => 'Informations sur l\'unité',
            'defense' => 'Informations sur la défense',
            'ship' => 'Informations sur le vaisseau',
            default => 'Informations sur le bâtiment',
        };
    }

    public function render()
    {
        return view('livewire.game.building');
    }

    // === Gestion des équipes PlanetEquip ===

    public function loadEquipTeams(): void
    {
        $teams = PlanetEquip::byPlanet($this->planet->id)
            ->orderBy('category')
            ->orderBy('team_index')
            ->get();

        $this->equipTeams = $teams->map(function ($team) {
            return [
                'id' => $team->id,
                'label' => $team->label,
                'category' => $team->category,
                'team_index' => $team->team_index,
                'notes' => $team->notes,
                'payload' => $team->payload ?? [],
                'is_active' => (bool) $team->is_active,
            ];
        })->toArray();

        // Calculer limites et compteurs
        $user = Auth::user();
        $this->equipMaxLimit = (int) ($user && $user->vip_active
            ? ServerConfig::getMaxPlanetEquipsVip()
            : ServerConfig::getMaxPlanetEquipsNormal());
        $this->equipCountTotal = count($this->equipTeams);
        $this->equipCountEarth = collect($this->equipTeams)->where('category', PlanetEquip::CATEGORY_EARTH)->count();
        $this->equipCountSpatial = collect($this->equipTeams)->where('category', PlanetEquip::CATEGORY_SPATIAL)->count();
    }

    private function prepareEquipPayloadSources(): void
    {
        // Préparer les clés dans le payload pour UI
        $this->equipPayloadUnits = $this->equipPayloadUnits ?: [];
        $this->equipPayloadShips = $this->equipPayloadShips ?: [];
    }

    public function startNewTeam(string $category = PlanetEquip::CATEGORY_EARTH): void
    {
        $this->equipEditId = null;
        $this->equipCategory = in_array($category, [PlanetEquip::CATEGORY_EARTH, PlanetEquip::CATEGORY_SPATIAL])
            ? $category : PlanetEquip::CATEGORY_EARTH;
        $this->equipLabel = '';
        // Calculer automatiquement le prochain index disponible selon la catégorie
        $this->equipTeamIndex = $this->getNextTeamIndex($this->equipCategory);
        $this->equipNotes = '';
        $this->equipPayloadUnits = [];
        $this->equipPayloadShips = [];
    }

    public function editTeam(int $id): void
    {
        $team = PlanetEquip::byPlanet($this->planet->id)->where('id', $id)->first();
        if (!$team) {
            $this->dispatch('toast:error', [
                'title' => 'Équipe introuvable',
                'text' => "L'équipe sélectionnée n'existe pas."
            ]);
            return;
        }

        $this->equipEditId = $team->id;
        $this->equipCategory = $team->category;
        $this->equipLabel = $team->label;
        $this->equipTeamIndex = $team->team_index;
        $this->equipNotes = $team->notes ?? '';
        $payload = $team->payload ?? [];
        $this->equipPayloadUnits = (array)($payload['units'] ?? []);
        $this->equipPayloadShips = (array)($payload['ships'] ?? []);
    }

    public function saveTeam(): void
    {
        // Validation simple (l'index est désormais auto-géré)
        if (!$this->equipLabel) {
            $this->dispatch('toast:error', [
                'title' => 'Champs requis',
                'text' => 'Le label de l’équipe est requis.'
            ]);
            return;
        }

        if (!in_array($this->equipCategory, [PlanetEquip::CATEGORY_EARTH, PlanetEquip::CATEGORY_SPATIAL])) {
            $this->equipCategory = PlanetEquip::CATEGORY_EARTH;
        }

        // Construire le payload selon la catégorie
        $payload = [];
        if ($this->equipCategory === PlanetEquip::CATEGORY_EARTH) {
            // units: [template_unit_id => qty]
            $payload['units'] = collect($this->equipPayloadUnits)
                ->filter(fn($q) => (int)$q > 0)
                ->map(fn($q) => (int)$q)
                ->toArray();
        } else {
            // ships: [template_ship_id => qty]
            $payload['ships'] = collect($this->equipPayloadShips)
                ->filter(fn($q) => (int)$q > 0)
                ->map(fn($q) => (int)$q)
                ->toArray();
        }

        // Déterminer l'index automatiquement
        // - En création: prendre le prochain index disponible dans la catégorie
        // - En édition: conserver l'index existant, mais si collision après changement de catégorie, réassigner automatiquement
        $teamIndex = null;
        if ($this->equipEditId) {
            $currentIndex = PlanetEquip::byPlanet($this->planet->id)
                ->where('id', $this->equipEditId)
                ->value('team_index');
            $teamIndex = (int) ($currentIndex ?? $this->equipTeamIndex);

            $collision = PlanetEquip::byPlanet($this->planet->id)
                ->where('category', $this->equipCategory)
                ->where('team_index', $teamIndex)
                ->where('id', '!=', $this->equipEditId)
                ->exists();
            if ($collision) {
                $teamIndex = $this->getNextTeamIndex($this->equipCategory);
            }
        } else {
            $teamIndex = $this->getNextTeamIndex($this->equipCategory);
        }

        // Vérifier la limite d'équipes par planète (VIP vs Normal)
        // On applique la limite uniquement lors de la création d'une nouvelle équipe
        if (!$this->equipEditId) {
            $user = Auth::user();
            $maxLimit = (int) ($user && $user->vip_active
                ? ServerConfig::getMaxPlanetEquipsVip()
                : ServerConfig::getMaxPlanetEquipsNormal());

            $currentCount = PlanetEquip::byPlanet($this->planet->id)->count();
            if ($currentCount >= $maxLimit) {
                $this->dispatch('toast:error', [
                    'title' => 'Limite atteinte',
                    'text' => "Vous avez atteint la limite d'équipes (" . $maxLimit . ") pour cette planète" . (($user && $user->vip_active) ? ' [VIP]' : ' [Normal]')
                ]);
                return;
            }
        }

        if ($this->equipEditId) {
            PlanetEquip::where('id', $this->equipEditId)
                ->update([
                    'category' => $this->equipCategory,
                    'label' => $this->equipLabel,
                    'team_index' => (int)$teamIndex,
                    'notes' => $this->equipNotes,
                    'payload' => $payload,
                    'is_active' => true,
                ]);
        } else {
            PlanetEquip::create([
                'planet_id' => $this->planet->id,
                'category' => $this->equipCategory,
                'label' => $this->equipLabel,
                'team_index' => (int)$teamIndex,
                'notes' => $this->equipNotes,
                'payload' => $payload,
                'is_active' => true,
            ]);
        }

        $this->dispatch('toast:success', [
            'title' => 'Équipe sauvegardée',
            'text' => "L'équipe a été enregistrée avec succès."
        ]);

        $this->startNewTeam($this->equipCategory);
        $this->loadEquipTeams();
    }

    /**
     * Retourne le plus petit index positif disponible pour une catégorie donnée
     */
    private function getNextTeamIndex(string $category): int
    {
        $existing = PlanetEquip::byPlanet($this->planet->id)
            ->where('category', $category)
            ->pluck('team_index')
            ->filter(fn($v) => $v !== null)
            ->map(fn($v) => (int)$v)
            ->unique()
            ->sort()
            ->values();

        $i = 1;
        foreach ($existing as $idx) {
            if ($idx > $i) {
                break; // Trouvé un trou
            }
            if ($idx === $i) {
                $i++;
            }
        }
        return $i;
    }

    public function deleteTeam(int $id): void
    {
        $team = PlanetEquip::byPlanet($this->planet->id)->where('id', $id)->first();
        if (!$team) {
            $this->dispatch('toast:error', [
                'title' => 'Équipe introuvable',
                'text' => "Impossible de supprimer: équipe inexistante."
            ]);
            return;
        }
        $team->delete();
        $this->dispatch('toast:success', [
            'title' => 'Supprimé',
            'text' => 'Équipe supprimée.'
        ]);
        $this->loadEquipTeams();
    }

    public function toggleTeamActive(int $id): void
    {
        $team = PlanetEquip::byPlanet($this->planet->id)->where('id', $id)->first();
        if (!$team) {
            $this->dispatch('toast:error', [
                'title' => 'Équipe introuvable',
                'text' => "Impossible de changer l'état: équipe inexistante."
            ]);
            return;
        }
        $team->is_active = !$team->is_active;
        $team->save();
        $this->loadEquipTeams();
    }

    // --- Actions UI: Max ---
    public function setMaxUnit(int $unitId): void
    {
        // Trouver la quantité disponible pour l'unité sur la planète
        $planetUnit = $this->planet->units->firstWhere('unit_id', $unitId);
        if ($planetUnit) {
            $this->equipPayloadUnits[$unitId] = (int) $planetUnit->quantity;
        }
    }

    public function setMaxShip(int $shipId): void
    {
        // Trouver la quantité disponible pour le vaisseau sur la planète
        $planetShip = $this->planet->ships->firstWhere('ship_id', $shipId);
        if ($planetShip) {
            $this->equipPayloadShips[$shipId] = (int) $planetShip->quantity;
        }
    }
}