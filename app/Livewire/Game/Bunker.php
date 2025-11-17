<?php

namespace App\Livewire\Game;

use App\Models\Planet\Planet;
use App\Models\Planet\PlanetBunker;
use App\Models\Planet\PlanetBuilding;
use App\Models\Planet\PlanetResource;
use App\Models\Planet\PlanetBunkerTransaction;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplateBuildAdvantage;
use App\Models\Template\TemplateResource;
use App\Services\DailyQuestService;
use App\Traits\LogsUserActions;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.game')]
class Bunker extends Component
{
    use LogsUserActions;
    public $bunkerResources = [];
    public $commandCenterLevel = 0;
    public $totalBunkerCapacity = 0;
    public $nextLevelBoost = 0;
    public $usedBunkerStorage = 0;
    public $globalAvailableStorage = 0;
    
    // Nouvelles propriétés pour gérer les quantités
    public $storeAmounts = [];
    public $retrieveAmounts = [];
    public $planetResources = [];
    
    // Propriétés pour l'historique des transactions et la pagination
    public $recentTransactions = [];
    public $currentPage = 1;
    public $perPage = 10;
    public $totalTransactions = 0;
    public $totalPages = 1;
    
    public function mount()
    {
        $this->loadBunkerData();
    }
    
    public function render()
    {
        return view('livewire.game.bunker');
    }
    
    public function loadBunkerData()
    {
        $user = Auth::user();
        $planet = Planet::find($user->actual_planet_id);
        
        if (!$planet) {
            return;
        }
        
        // Charger l'historique des transactions récentes
        $this->loadRecentTransactions($planet->id);
        
        // Récupérer le niveau du centre de commandement
        $commandCenter = PlanetBuilding::where('planet_id', $planet->id)
            ->whereHas('build', function($query) {
                $query->where('name', 'centre_commandement');
            })
            ->first();
        
        $this->commandCenterLevel = $commandCenter ? $commandCenter->level : 0;
        
        // Récupérer l'ID du bâtiment centre_commandement
        $commandCenterTemplate = TemplateBuild::where('name', 'centre_commandement')->first();
        $commandCenterId = $commandCenterTemplate ? $commandCenterTemplate->id : 0;
        
        // Calculer la capacité GLOBALE du bunker: 50 000 × niveau (sans base)
        $this->totalBunkerCapacity = 0;

        if ($commandCenterId && $this->commandCenterLevel > 0) {
            $bunkerBoost = TemplateBuildAdvantage::getBunkerBoost($commandCenterId, $this->commandCenterLevel);
            $this->totalBunkerCapacity += $bunkerBoost;
            
            // Calculer le boost pour le niveau suivant
            $nextLevelBoost = TemplateBuildAdvantage::getBunkerBoost($commandCenterId, $this->commandCenterLevel + 1);
            $this->nextLevelBoost = $nextLevelBoost - $bunkerBoost;
        }
        
        // Calculer l'utilisation actuelle du bunker (toutes ressources confondues)
        $this->usedBunkerStorage = PlanetBunker::where('planet_id', $planet->id)->sum('stored_amount');
        $this->globalAvailableStorage = max(0, $this->totalBunkerCapacity - $this->usedBunkerStorage);

        // Récupérer les ressources primaires (non-énergétiques)
        $primaryResources = TemplateResource::where('is_tradeable', true)
            ->where('type', '!=', 'energy')
            ->get();
        
        // Réinitialiser les tableaux
        $this->bunkerResources = [];
        $this->planetResources = [];
        $this->storeAmounts = [];
        $this->retrieveAmounts = [];
        
        // Préparer les données des ressources du bunker
        foreach ($primaryResources as $resource) {
            // Vérifier si un enregistrement de bunker existe déjà pour cette ressource
            $bunker = PlanetBunker::where('planet_id', $planet->id)
                ->where('resource_id', $resource->id)
                ->first();
            
            // Si aucun enregistrement n'existe, en créer un nouveau
            if (!$bunker) {
                $bunker = PlanetBunker::create([
                    'planet_id' => $planet->id,
                    'resource_id' => $resource->id,
                    'stored_amount' => 0,
                    // max_storage est gardé pour compatibilité, mais l'espace réel est global
                    'max_storage' => $this->totalBunkerCapacity,
                    'is_active' => true,
                    'last_update' => now()
                ]);
            } else {
                // Mettre à jour la capacité maximale affichée (égale à la capacité globale)
                if ($bunker->max_storage != $this->totalBunkerCapacity) {
                    $bunker->max_storage = $this->totalBunkerCapacity;
                    $bunker->save();
                }
            }
            
            // Récupérer la ressource correspondante sur la planète
            $planetResource = PlanetResource::where('planet_id', $planet->id)
                ->where('resource_id', $resource->id)
                ->first();
            
            // Calculer le pourcentage de remplissage par rapport à la CAPACITÉ GLOBALE
            $percentage = $this->totalBunkerCapacity > 0 ? ($bunker->stored_amount / $this->totalBunkerCapacity) * 100 : 0;
            
            $this->bunkerResources[] = [
                'id' => $bunker->id,
                'resource_id' => $resource->id,
                'name' => $resource->display_name,
                'icon' => $resource->icon,
                'stored_amount' => $bunker->stored_amount,
                'max_storage' => $bunker->max_storage,
                'percentage' => min(100, $percentage),
                // Espace disponible identique pour toutes les ressources: pool global
                'available_space' => $this->globalAvailableStorage
            ];
            
            // Initialiser les quantités à stocker/récupérer
            $this->storeAmounts[$bunker->id] = 0;
            $this->retrieveAmounts[$bunker->id] = 0;
            
            // Stocker les informations sur les ressources de la planète
            if ($planetResource) {
                $this->planetResources[$resource->id] = [
                    'id' => $planetResource->id,
                    'current_amount' => $planetResource->current_amount
                ];
            }
        }
    }
    
    public function updateStoreAmount($bunkerId, $amount)
    {
        $this->storeAmounts[$bunkerId] = (int) $amount;
    }
    
    public function updateRetrieveAmount($bunkerId, $amount)
    {
        $this->retrieveAmounts[$bunkerId] = (int) $amount;
    }
    
    public function storeResource($bunkerId)
    {
        $bunker = PlanetBunker::find($bunkerId);
        
        if (!$bunker) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Bunker non trouvé.'
            ]);
            return;
        }
        
        $user = Auth::user();
        $planet = Planet::find($user->actual_planet_id);
        
        if (!$planet) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Aucune planète trouvée.'
            ]);
            return;
        }
        
        // Récupérer la ressource correspondante sur la planète
        $planetResource = PlanetResource::where('planet_id', $planet->id)
            ->where('resource_id', $bunker->resource_id)
            ->first();
        
        if (!$planetResource || $planetResource->current_amount <= 0) {
            // Pas assez de ressources disponibles
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Ressources insuffisantes sur la planète.'
            ]);
            return;
        }
        
        // Déterminer la quantité à stocker avec limite GLOBALE
        $requestedAmount = $this->storeAmounts[$bunkerId] ?? 0;

        // Recalcule l'espace global disponible (précaution en cas de concurrence)
        $globalAvailable = max(0, $this->totalBunkerCapacity - PlanetBunker::where('planet_id', $planet->id)->sum('stored_amount'));

        if ($requestedAmount <= 0) {
            $amountToStore = min($globalAvailable, $planetResource->current_amount);
        } else {
            $amountToStore = min($requestedAmount, $globalAvailable, $planetResource->current_amount);
        }
        
        if ($amountToStore <= 0) {
            // Pas d'espace disponible dans le bunker ou pas assez de ressources
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Espace de stockage insuffisant dans le bunker.'
            ]);
            return;
        }
        
        // Enregistrer les valeurs avant la transaction
        $bunkerAmountBefore = $bunker->stored_amount;
        $planetAmountBefore = $planetResource->current_amount;
        
        // Transférer les ressources
        $bunker->stored_amount += $amountToStore;
        $bunker->save();
        
        $planetResource->current_amount -= $amountToStore;
        $planetResource->save();
        
        // Enregistrer la transaction
        $this->logBunkerStore(
            $planet->id,
            $bunker->id,
            $bunker->resource_id,
            $amountToStore,
            $bunkerAmountBefore,
            $bunker->stored_amount,
            $planetAmountBefore,
            $planetResource->current_amount
        );
        
        // Notification de succès
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => number_format($amountToStore) . ' ressources stockées dans le bunker.'
        ]);

        // Incrémenter la quête quotidienne de dépôt au bunker
        app(DailyQuestService::class)->incrementProgress($user, 'bunker_add_resources');
        
        // Réinitialiser la quantité à stocker
        $this->storeAmounts[$bunkerId] = 0;

        // Recharger les données
        $this->bunkerResources = [];
        $this->loadBunkerData();
    }
    
    public function retrieveResource($bunkerId)
    {
        $bunker = PlanetBunker::find($bunkerId);
        
        if (!$bunker) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Bunker non trouvé.'
            ]);
            return;
        }
        
        if ($bunker->stored_amount <= 0) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Aucune ressource stockée dans le bunker.'
            ]);
            return;
        }
        
        $user = Auth::user();
        $planet = Planet::find($user->actual_planet_id);
        
        if (!$planet) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Aucune planète trouvée.'
            ]);
            return;
        }
        
        // Récupérer la ressource correspondante sur la planète
        $planetResource = PlanetResource::where('planet_id', $planet->id)
            ->where('resource_id', $bunker->resource_id)
            ->first();
        
        if (!$planetResource) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Ressource non trouvée sur la planète.'
            ]);
            return;
        }
        
        // Déterminer la quantité à récupérer
        $requestedAmount = $this->retrieveAmounts[$bunkerId] ?? 0;
        
        // Si la quantité est 0 ou non spécifiée, récupérer tout
        if ($requestedAmount <= 0) {
            $amountToRetrieve = $bunker->stored_amount;
        } else {
            // Sinon, utiliser la quantité demandée (limitée par ce qui est stocké)
            $amountToRetrieve = min($requestedAmount, $bunker->stored_amount);
        }
        
        if ($amountToRetrieve <= 0) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Aucune ressource à récupérer.'
            ]);
            return;
        }
        
        // Enregistrer les valeurs avant la transaction
        $bunkerAmountBefore = $bunker->stored_amount;
        $planetAmountBefore = $planetResource->current_amount;
        
        // Transférer les ressources
        $bunker->stored_amount -= $amountToRetrieve;
        $bunker->save();
        
        $planetResource->current_amount += $amountToRetrieve;
        $planetResource->save();
        
        // Enregistrer la transaction
        $this->logBunkerRetrieve(
            $planet->id,
            $bunker->id,
            $bunker->resource_id,
            $amountToRetrieve,
            $bunkerAmountBefore,
            $bunker->stored_amount,
            $planetAmountBefore,
            $planetResource->current_amount
        );
        
        // Réinitialiser la quantité à récupérer
        $this->retrieveAmounts[$bunkerId] = 0;
        
        // Notification de succès
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => number_format($amountToRetrieve) . ' ressources récupérées du bunker.'
        ]);
        
        // Recharger les données
        $this->bunkerResources = [];
        $this->loadBunkerData();
    }
    
    public function setMaxStoreAmount($bunkerId)
    {
        $bunker = PlanetBunker::find($bunkerId);
        if (!$bunker) return;
        
        $resourceId = $bunker->resource_id;
        
        if (isset($this->planetResources[$resourceId])) {
            $planetAmount = $this->planetResources[$resourceId]['current_amount'];
            // Utiliser l'espace disponible GLOBAL
            $availableSpace = max(0, $this->totalBunkerCapacity - PlanetBunker::where('planet_id', $bunker->planet_id)->sum('stored_amount'));
            $this->storeAmounts[$bunkerId] = min($planetAmount, $availableSpace);
        }
    }
    
    public function setMaxRetrieveAmount($bunkerId)
    {
        $bunker = PlanetBunker::find($bunkerId);
        if (!$bunker) return;
        
        $this->retrieveAmounts[$bunkerId] = $bunker->stored_amount;
    }
    
    /**
     * Charger l'historique des transactions récentes avec pagination
     */
    private function loadRecentTransactions(int $planetId)
    {
        // Calculer le nombre total de transactions pour la pagination
        $this->totalTransactions = PlanetBunkerTransaction::where('planet_id', $planetId)->count();
        $this->totalPages = ceil($this->totalTransactions / $this->perPage);
        
        // S'assurer que la page courante est valide
        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        } elseif ($this->currentPage > $this->totalPages && $this->totalPages > 0) {
            $this->currentPage = $this->totalPages;
        }
        
        // Calculer l'offset pour la pagination
        $offset = ($this->currentPage - 1) * $this->perPage;
        
        // Récupérer les transactions pour la page courante
        $this->recentTransactions = PlanetBunkerTransaction::where('planet_id', $planetId)
            ->with(['resource', 'user'])
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($this->perPage)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->transaction_type,
                    'type_formatted' => $transaction->formatted_type,
                    'type_icon' => $transaction->type_icon,
                    'resource_name' => $transaction->resource->display_name,
                    'resource_icon' => $transaction->resource->icon,
                    'amount' => $transaction->amount,
                    'user_name' => $transaction->user->name,
                    'created_at' => $transaction->created_at->diffForHumans(),
                    'created_at_formatted' => $transaction->created_at->format('d/m/Y H:i:s'),
                ];
            })
            ->toArray();
    }
    
    /**
     * Changer de page pour la pagination des transactions
     */
    public function changePage($page)
    {
        $this->currentPage = (int) $page;
        $user = Auth::user();
        if ($user) {
            $this->loadRecentTransactions($user->actual_planet_id);
        }
    }
}