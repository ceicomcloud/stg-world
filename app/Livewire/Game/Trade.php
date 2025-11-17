<?php

namespace App\Livewire\Game;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetResource;
use App\Models\Template\TemplateResource;
use App\Models\Other\Trade as Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Traits\LogsUserActions;
use App\Support\Device;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.game')]
class Trade extends Component
{
    use LogsUserActions;
    public $planet = null;
    public $planetResources = [];
    
    // Propriétés pour le commerce
    public $tradeOffers = [];
    public $myOffers = [];
    public $tradeHistory = [];
    public $selectedResource = null;
    public $offerAmount = 0;
    public $requestedResource = null;
    public $requestedAmount = 0;
    
    // Propriétés pour la pagination
    public $tradeOffersPage = 1;
    public $myOffersPage = 1;
    public $historyPage = 1;
    public $perPage = 5;
    public $totalTradeOffers = 0;
    public $totalMyOffers = 0;
    public $totalHistory = 0;
    
    // Propriétés pour le filtrage et le tri
    public $activeTab = 'create';
    public $myOffersSortBy = 'created_at';
    public $myOffersSortDirection = 'desc';
    public $myOffersFilterResource = '';
    public $availableOffersSortBy = 'created_at';
    public $availableOffersSortDirection = 'desc';
    public $availableOffersFilterResource = '';
    public $availableOffersFilterRequestedResource = '';
    public $historyFilterType = 'all';
    public $historyFilterStatus = 'all';
    public $historySortBy = 'created_at';
    public $historySortDirection = 'desc';
    
    public function mount()
    {
        // Initialiser l'onglet actif
        $this->activeTab = 'create';
        
        // Utiliser directement la planète actuelle de l'utilisateur
        $actualPlanet = Auth::user()->getActualPlanet();
        if ($actualPlanet) {
            $this->planet = [
                'id' => $actualPlanet->id,
                'name' => $actualPlanet->name,
                'description' => $actualPlanet->description,
                'is_main_planet' => $actualPlanet->is_main_planet
            ];
            $this->loadPlanetData();
        }
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

        // Charger les ressources de la planète
        $this->loadPlanetResources($planet);
        
        // Charger les offres de commerce
        $this->loadTradeOffers();
        $this->loadMyOffers();
        $this->loadTradeHistory();
    }
    
    public function loadPlanetResources($planet)
    {
        $resources = $planet->resources()->with('resource')->get();
        
        $this->planetResources = [];
        
        foreach ($resources as $resource) {
            $templateResource = $resource->resource;
            if (!$templateResource) continue;
            
            $this->planetResources[$templateResource->name] = [
                'id' => $resource->id,
                'name' => $templateResource->display_name,
                'icon' => $templateResource->icon,
                'current_amount' => $resource->current_amount,
                'storage_capacity' => $resource->getStorageCapacity(),
            ];
        }
    }
    
    public function loadTradeOffers()
    {
        // Construire la requête de base
        $query = Model::with([
            'seller', 
            'sellerPlanet', 
            'offeredResource', 
            'requestedResource'
        ])
        ->availableFor(Auth::id())
        ->active();
        
        // Appliquer le filtre par ressource offerte si spécifié
        if (!empty($this->availableOffersFilterResource)) {
            $query->where('offered_resource_id', $this->availableOffersFilterResource);
        }
        
        // Appliquer le filtre par ressource demandée si spécifié
        if (!empty($this->availableOffersFilterRequestedResource)) {
            $query->where('requested_resource_id', $this->availableOffersFilterRequestedResource);
        }
        
        // Appliquer le tri
        $query->orderBy($this->availableOffersSortBy, $this->availableOffersSortDirection);
        
        // Compter le total des offres disponibles après filtrage
        $this->totalTradeOffers = $query->count();
        
        // Charger les offres de commerce disponibles avec pagination
        $this->tradeOffers = $query->skip(($this->tradeOffersPage - 1) * $this->perPage)
             ->take($this->perPage)
             ->get()
             ->map(function ($trade) {
                 return [
                     'id' => $trade->id,
                     'seller_name' => $trade->seller->name,
                     'seller_planet' => $trade->sellerPlanet->name,
                     'offered_resource' => [
                         'name' => $trade->offeredResource->display_name,
                         'icon' => $trade->offeredResource->icon,
                         'amount' => $trade->offered_amount,
                     ],
                     'requested_resource' => [
                         'name' => $trade->requestedResource->display_name,
                         'icon' => $trade->requestedResource->icon,
                         'amount' => $trade->requested_amount,
                     ],
                     'exchange_ratio' => $trade->getExchangeRatio(),
                     'time_remaining' => $trade->getTimeRemaining(),
                     'created_at' => $trade->created_at->diffForHumans(),
                 ];
             })
             ->toArray();
     }
    
    public function loadMyOffers()
    {
        // Construire la requête de base
        $query = Model::with([
            'buyer',
            'buyerPlanet', 
            'sellerPlanet', 
            'offeredResource', 
            'requestedResource'
        ])
        ->forUser(Auth::id())
        ->active();
        
        // Appliquer le filtre par ressource offerte si spécifié
        if (!empty($this->myOffersFilterResource)) {
            $query->where('offered_resource_id', $this->myOffersFilterResource);
        }
        
        // Appliquer le tri
        $query->orderBy($this->myOffersSortBy, $this->myOffersSortDirection);
        
        // Compter le total de mes offres actives (en attente et non expirées) après filtrage
        $this->totalMyOffers = $query->count();
        
        // Charger mes offres de commerce avec pagination
        $this->myOffers = $query->skip(($this->myOffersPage - 1) * $this->perPage)
            ->take($this->perPage)
            ->get()
            ->map(function ($trade) {
                return [
                    'id' => $trade->id,
                    'status' => $trade->status,
                    'buyer_name' => $trade->buyer ? $trade->buyer->name : null,
                    'buyer_planet' => $trade->buyerPlanet ? $trade->buyerPlanet->name : null,
                    'seller_planet' => $trade->sellerPlanet->name,
                    'offered_resource' => [
                        'name' => $trade->offeredResource->display_name,
                        'icon' => $trade->offeredResource->icon,
                        'amount' => $trade->offered_amount,
                    ],
                    'requested_resource' => [
                        'name' => $trade->requestedResource->display_name,
                        'icon' => $trade->requestedResource->icon,
                        'amount' => $trade->requested_amount,
                    ],
                    'exchange_ratio' => $trade->getExchangeRatio(),
                    'time_remaining' => $trade->getTimeRemaining(),
                    'created_at' => $trade->created_at->diffForHumans(),
                    'can_be_cancelled' => $trade->status === 'pending',
                ];
            })
            ->toArray();
    }
    
    public function loadTradeHistory()
    {
        // Construire la requête de base
        $query = Model::with([
            'buyer',
            'seller',
            'buyerPlanet', 
            'sellerPlanet', 
            'offeredResource', 
            'requestedResource'
        ])
        ->where(function($q) {
            $q->where('buyer_id', Auth::id())
              ->orWhere('seller_id', Auth::id());
        });
        
        // Filtrer par type (envoyé/reçu)
        if ($this->historyFilterType === 'sent') {
            $query->where('seller_id', Auth::id());
        } elseif ($this->historyFilterType === 'received') {
            $query->where('buyer_id', Auth::id());
        }
        
        // Filtrer par statut
        if ($this->historyFilterStatus !== 'all') {
            $query->where('status', $this->historyFilterStatus);
        }
        
        // Appliquer le tri
        $query->orderBy($this->historySortBy, $this->historySortDirection);
        
        // Compter le total des transactions après filtrage
        $this->totalHistory = $query->count();
        
        // Charger l'historique des transactions avec pagination
        $this->tradeHistory = $query->skip(($this->historyPage - 1) * $this->perPage)
            ->take($this->perPage)
            ->get()
            ->map(function ($trade) {
                $isSeller = $trade->seller_id === Auth::id();
                return [
                    'id' => $trade->id,
                    'status' => $trade->status,
                    'status_label' => $this->getStatusLabel($trade->status),
                    'type' => $isSeller ? 'sent' : 'received',
                    'buyer_name' => $trade->buyer ? $trade->buyer->name : 'Inconnu',
                    'seller_name' => $trade->seller ? $trade->seller->name : 'Inconnu',
                    'buyer_planet' => $trade->buyerPlanet ? $trade->buyerPlanet->name : 'Inconnue',
                    'seller_planet' => $trade->sellerPlanet ? $trade->sellerPlanet->name : 'Inconnue',
                    'offered_resource' => [
                        'name' => $trade->offeredResource->display_name,
                        'icon' => $trade->offeredResource->icon,
                        'amount' => $trade->offered_amount,
                    ],
                    'requested_resource' => [
                        'name' => $trade->requestedResource->display_name,
                        'icon' => $trade->requestedResource->icon,
                        'amount' => $trade->requested_amount,
                    ],
                    'exchange_ratio' => $trade->getExchangeRatio(),
                    'created_at' => $trade->created_at->diffForHumans(),
                    'completed_at' => $trade->completed_at ? Carbon::parse($trade->completed_at)->diffForHumans() : null,
                ];
            })
            ->toArray();
    }
    
    public function createOffer()
    {
        // Validation
        $this->validate([
            'selectedResource' => 'required',
            'offerAmount' => 'required|numeric|min:1',
            'requestedResource' => 'required',
            'requestedAmount' => 'required|numeric|min:1',
        ], [
            'selectedResource.required' => 'Veuillez sélectionner une ressource à offrir.',
            'offerAmount.required' => 'Veuillez indiquer la quantité à offrir.',
            'offerAmount.numeric' => 'La quantité doit être un nombre.',
            'offerAmount.min' => 'La quantité doit être supérieure à 0.',
            'requestedResource.required' => 'Veuillez sélectionner une ressource demandée.',
            'requestedAmount.required' => 'Veuillez indiquer la quantité demandée.',
            'requestedAmount.numeric' => 'La quantité doit être un nombre.',
            'requestedAmount.min' => 'La quantité doit être supérieure à 0.',
        ]);
        
        // Vérifier que le joueur a suffisamment de ressources
        if (!isset($this->planetResources[$this->selectedResource])) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur',
                'text' => 'Ressource non trouvée sur cette planète.'
            ]);
            return;
        }
        
        $availableAmount = $this->planetResources[$this->selectedResource]['current_amount'];
        if ($this->offerAmount > $availableAmount) {
            $this->dispatch('toast:error', [
                'title' => 'Ressources insuffisantes',
                'text' => 'Vous n\'avez pas assez de cette ressource.'
            ]);
            return;
        }
        
        // Récupérer les IDs des ressources
        $offeredResourceId = TemplateResource::where('name', $this->selectedResource)->first()?->id;
        $requestedResourceId = TemplateResource::where('name', $this->requestedResource)->first()?->id;
        
        if (!$offeredResourceId || !$requestedResourceId) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur',
                'text' => 'Ressource non trouvée.'
            ]);
            return;
        }
        
        try {
            // Créer l'offre de commerce
            $trade = Model::create([
                'seller_id' => Auth::id(),
                'seller_planet_id' => $this->planet['id'],
                'offered_resource_id' => $offeredResourceId,
                'offered_amount' => $this->offerAmount,
                'requested_resource_id' => $requestedResourceId,
                'requested_amount' => $this->requestedAmount,
                'status' => 'pending',
                'expires_at' => Carbon::now()->addDays(7), // Expire dans 7 jours
            ]);
            
            // Log trade offer creation
            $this->logAction(
                'trade_offer_created',
                'trade',
                'Création d\'une offre commerciale',
                [
                    'offered_resource' => $this->selectedResource,
                    'offered_amount' => $this->offerAmount,
                    'requested_resource' => $this->requestedResource,
                    'requested_amount' => $this->requestedAmount,
                    'planet_id' => $this->planet['id']
                ]
            );
            
            // Réinitialiser le formulaire
            $this->selectedResource = null;
            $this->offerAmount = 0;
            $this->requestedResource = null;
            $this->requestedAmount = 0;
            
            $this->dispatch('toast:success', [
                'title' => 'Offre créée',
                'text' => 'Votre offre de commerce a été créée avec succès.'
            ]);
            
            // Recharger les données
            $this->loadMyOffers();
            
        } catch (\Exception $e) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur',
                'text' => 'Une erreur est survenue lors de la création de l\'offre.'
            ]);
        }
    }
    
    public function acceptOffer($offerId)
    {
        if (!$this->planet) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur',
                'text' => 'Aucune planète sélectionnée.'
            ]);
            return;
        }
        
        try {
            $trade = Model::with(['sellerPlanet', 'offeredResource', 'requestedResource'])
                ->find($offerId);
            
            if (!$trade || !$trade->canBeAccepted()) {
                $this->dispatch('toast:error', [
                    'title' => 'Erreur',
                    'text' => 'Cette offre n\'est plus disponible.'
                ]);
                return;
            }
            
            $buyerPlanet = Planet::find($this->planet['id']);
            
            // Vérifier que l'acheteur a suffisamment de ressources demandées
            $buyerResource = $buyerPlanet->resources()
                ->where('resource_id', $trade->requested_resource_id)
                ->first();
            
            if (!$buyerResource || $buyerResource->current_amount < $trade->requested_amount) {
                $this->dispatch('toast:error', [
                    'title' => 'Ressources insuffisantes',
                    'text' => 'Vous n\'avez pas assez de ' . $trade->requestedResource->display_name . '.'
                ]);
                return;
            }
            
            // Vérifier que le vendeur a toujours suffisamment de ressources
            $sellerResource = $trade->sellerPlanet->resources()
                ->where('resource_id', $trade->offered_resource_id)
                ->first();
            
            if (!$sellerResource || $sellerResource->current_amount < $trade->offered_amount) {
                $this->dispatch('toast:error', [
                    'title' => 'Erreur',
                    'text' => 'Le vendeur n\'a plus suffisamment de ressources.'
                ]);
                return;
            }
            
            // Vérifier les capacités AVANT toute modification
            // Acheteur: capacité pour recevoir la ressource offerte
            $buyerOfferedResource = $buyerPlanet->resources()
                ->where('resource_id', $trade->offered_resource_id)
                ->first();

            if (!$buyerOfferedResource) {
                $this->dispatch('toast:error', [
                    'title' => 'Erreur',
                    'text' => 'Ressource offerte introuvable sur votre planète.'
                ]);
                return;
            }

            $buyerCapacity = method_exists($buyerOfferedResource, 'getStorageCapacity')
                ? $buyerOfferedResource->getStorageCapacity()
                : ($buyerOfferedResource->storage_capacity ?? null);
            if ($buyerCapacity !== null) {
                $buyerAvailableSpace = max(0, $buyerCapacity - $buyerOfferedResource->current_amount);
                if ($trade->offered_amount > $buyerAvailableSpace) {
                    $this->dispatch('toast:error', [
                        'title' => 'Capacité insuffisante',
                        'text' => 'Capacité de stockage insuffisante pour recevoir ' . $trade->offeredResource->display_name . ' (espace dispo: ' . number_format($buyerAvailableSpace) . ').'
                    ]);
                    return;
                }
            }

            // Vendeur: capacité pour recevoir la ressource demandée
            $sellerRequestedResource = $trade->sellerPlanet->resources()
                ->where('resource_id', $trade->requested_resource_id)
                ->first();

            if (!$sellerRequestedResource) {
                $this->dispatch('toast:error', [
                    'title' => 'Erreur',
                    'text' => 'Ressource demandée introuvable sur la planète du vendeur.'
                ]);
                return;
            }

            $sellerCapacity = method_exists($sellerRequestedResource, 'getStorageCapacity')
                ? $sellerRequestedResource->getStorageCapacity()
                : ($sellerRequestedResource->storage_capacity ?? null);
            if ($sellerCapacity !== null) {
                $sellerAvailableSpace = max(0, $sellerCapacity - $sellerRequestedResource->current_amount);
                if ($trade->requested_amount > $sellerAvailableSpace) {
                    $this->dispatch('toast:error', [
                        'title' => 'Capacité insuffisante',
                        'text' => 'Le vendeur n\'a pas assez de capacité pour recevoir ' . $trade->requestedResource->display_name . ' (espace dispo: ' . number_format($sellerAvailableSpace) . ').'
                    ]);
                    return;
                }
            }

            // Effectuer l'échange de façon atomique
            DB::transaction(function () use ($buyerResource, $buyerOfferedResource, $sellerResource, $sellerRequestedResource, $trade, $buyerPlanet) {
                // Retirer les ressources de l'acheteur
                $buyerResource->decrement('current_amount', $trade->requested_amount);

                // Ajouter les ressources offertes à l'acheteur
                $buyerOfferedResource->increment('current_amount', $trade->offered_amount);

                // Retirer les ressources du vendeur
                $sellerResource->decrement('current_amount', $trade->offered_amount);

                // Ajouter les ressources demandées au vendeur
                $sellerRequestedResource->increment('current_amount', $trade->requested_amount);

                // Marquer l'échange comme accepté puis complété
                $trade->accept(Auth::user(), $buyerPlanet);
                $trade->complete();
            });
            
            // Log trade completion
            $this->logAction(
                'trade_completed',
                'trade',
                'Échange commercial complété',
                [
                    'offered_resource' => $trade->offeredResource->display_name,
                    'offered_amount' => $trade->offered_amount,
                    'requested_resource' => $trade->requestedResource->display_name,
                    'requested_amount' => $trade->requested_amount,
                    'seller_name' => $trade->seller->name,
                    'planet_id' => $this->planet['id']
                ]
            );
            
            $this->dispatch('toast:success', [
                'title' => 'Échange effectué',
                'text' => 'L\'échange a été effectué avec succès.'
            ]);
            
            // Recharger les données
            $this->loadTradeOffers();
            $this->loadPlanetData();
            
        } catch (\Exception $e) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur',
                'text' => 'Une erreur est survenue lors de l\'échange.'
            ]);
        }
    }
    
    public function cancelOffer($offerId)
    {
        try {
            $trade = Model::where('id', $offerId)
                ->where('seller_id', Auth::id())
                ->first();
            
            if (!$trade) {
                $this->dispatch('toast:error', [
                    'title' => 'Erreur',
                    'text' => 'Offre non trouvée.'
                ]);
                return;
            }
            
            if ($trade->status !== 'pending') {
                $this->dispatch('toast:error', [
                    'title' => 'Erreur',
                    'text' => 'Cette offre ne peut pas être annulée.'
                ]);
                return;
            }
            
            // Log trade cancellation before deletion
            $offeredResource = TemplateResource::find($trade->offered_resource_id);
            $requestedResource = TemplateResource::find($trade->requested_resource_id);
            
            $this->logAction(
                'trade_offer_cancelled',
                'trade',
                'Annulation d\'une offre commerciale',
                [
                    'offered_resource' => $offeredResource->display_name ?? 'Ressource inconnue',
                    'offered_amount' => $trade->offered_amount,
                    'requested_resource' => $requestedResource->display_name ?? 'Ressource inconnue',
                    'requested_amount' => $trade->requested_amount,
                    'planet_id' => $this->planet['id']
                ]
            );
            
            // Supprimer définitivement l'offre de la base de données
            $trade->delete();
            
            $this->dispatch('toast:success', [
                'title' => 'Offre annulée',
                'text' => 'Votre offre a été annulée avec succès.'
            ]);
            
            // Recharger les données
            $this->loadMyOffers();
            
        } catch (\Exception $e) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur',
                'text' => 'Une erreur est survenue lors de l\'annulation.'
            ]);
        }
    }

    #[On('planet-changed')]
    public function refreshPlanetData()
    {
        $this->loadPlanetData();
    }
    
    // Méthodes de pagination pour les offres disponibles
    public function nextTradeOffersPage()
    {
        $maxPage = ceil($this->totalTradeOffers / $this->perPage);
        if ($this->tradeOffersPage < $maxPage) {
            $this->tradeOffersPage++;
            $this->loadTradeOffers();
        }
    }
    
    public function previousTradeOffersPage()
    {
        if ($this->tradeOffersPage > 1) {
            $this->tradeOffersPage--;
            $this->loadTradeOffers();
        }
    }
    
    public function goToTradeOffersPage($page)
    {
        $maxPage = ceil($this->totalTradeOffers / $this->perPage);
        if ($page >= 1 && $page <= $maxPage) {
            $this->tradeOffersPage = $page;
            $this->loadTradeOffers();
        }
    }
    
    // Méthodes de pagination pour mes offres
    public function nextMyOffersPage()
    {
        $maxPage = ceil($this->totalMyOffers / $this->perPage);
        if ($this->myOffersPage < $maxPage) {
            $this->myOffersPage++;
            $this->loadMyOffers();
        }
    }
    
    public function previousMyOffersPage()
    {
        if ($this->myOffersPage > 1) {
            $this->myOffersPage--;
            $this->loadMyOffers();
        }
    }
    
    public function goToMyOffersPage($page)
    {
        $maxPage = ceil($this->totalMyOffers / $this->perPage);
        if ($page >= 1 && $page <= $maxPage) {
            $this->myOffersPage = $page;
            $this->loadMyOffers();
        }
    }
    
    // Méthodes de pagination pour l'historique
    public function nextHistoryPage()
    {
        $maxPage = ceil($this->totalHistory / $this->perPage);
        if ($this->historyPage < $maxPage) {
            $this->historyPage++;
            $this->loadTradeHistory();
        }
    }
    
    public function previousHistoryPage()
    {
        if ($this->historyPage > 1) {
            $this->historyPage--;
            $this->loadTradeHistory();
        }
    }
    
    public function goToHistoryPage($page)
    {
        $maxPage = ceil($this->totalHistory / $this->perPage);
        if ($page >= 1 && $page <= $maxPage) {
            $this->historyPage = $page;
            $this->loadTradeHistory();
        }
    }
    
    // Méthodes utilitaires pour la pagination
    public function getTradeOffersMaxPage()
    {
        return ceil($this->totalTradeOffers / $this->perPage);
    }
    
    public function getMyOffersMaxPage()
    {
        return ceil($this->totalMyOffers / $this->perPage);
    }
    
    public function getHistoryMaxPage()
    {
        return ceil($this->totalHistory / $this->perPage);
    }
    
    // Méthodes pour changer d'onglet
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    // Méthodes pour mettre à jour les filtres et tris
    public function updatedMyOffersSortBy()
    {
        $this->loadMyOffers();
    }
    
    public function updatedMyOffersSortDirection()
    {
        $this->loadMyOffers();
    }
    
    public function updatedMyOffersFilterResource()
    {
        $this->myOffersPage = 1; // Réinitialiser la pagination
        $this->loadMyOffers();
    }
    
    public function updatedAvailableOffersSortBy()
    {
        $this->loadTradeOffers();
    }
    
    public function updatedAvailableOffersSortDirection()
    {
        $this->loadTradeOffers();
    }
    
    public function updatedAvailableOffersFilterResource()
    {
        $this->tradeOffersPage = 1; // Réinitialiser la pagination
        $this->loadTradeOffers();
    }
    
    public function updatedAvailableOffersFilterRequestedResource()
    {
        $this->tradeOffersPage = 1; // Réinitialiser la pagination
        $this->loadTradeOffers();
    }
    
    public function updatedHistoryFilterType()
    {
        $this->historyPage = 1; // Réinitialiser la pagination
        $this->loadTradeHistory();
    }
    
    public function updatedHistoryFilterStatus()
    {
        $this->historyPage = 1; // Réinitialiser la pagination
        $this->loadTradeHistory();
    }
    
    public function updatedHistorySortBy()
    {
        $this->loadTradeHistory();
    }
    
    public function updatedHistorySortDirection()
    {
        $this->loadTradeHistory();
    }
    
    /**
     * Convertit le code de statut en libellé lisible
     */
    protected function getStatusLabel($status)
    {
        return match($status) {
            'pending' => 'En attente',
            'completed' => 'Complété',
            'cancelled' => 'Annulé',
            'expired' => 'Expiré',
            default => ucfirst($status)
        };
    }

    public function render()
    {
        return view('livewire.game.trade');
    }
}