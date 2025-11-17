<?php

namespace App\Livewire\Game\Mission;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetShip;
use App\Models\Planet\PlanetUnit;
use App\Models\Planet\PlanetMission;
use App\Models\User\UserBookmark;
use App\Models\Server\ServerConfig;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.game')]
class Mission extends Component
{
    // Propriétés pour les coordonnées cibles
    public $targetGalaxy = 1;
    public $targetSystem = 1;
    public $targetPosition = 1;
    
    // Type de mission sélectionné
    public $missionType = null;
    
    // Planète de départ
    public $planet;
    public $planetId;
    
    // Liste des planètes de l'utilisateur
    public $userPlanets = [];
    public $selectedSourcePlanet = null;
    
    // Vérification de la disponibilité des missions
    public $canAttackSpatial = false;
    public $canAttackEarth = false;
    public $canSpy = false;
    public $canTransport = false;
    public $canColonize = false;
    public $canBasement = false;
    public $canExtract = false;
    public $canExplore = false;

    // Bookmarks
    public $bookmarks = [];
    public $bookmarkLimit = 0;
    public $bookmarkCount = 0;
    public $activeTab = 'mission';

    // Compteurs de flottes en vol
    public $fleetLimit = 0;
    public $fleetCurrent = 0;
    
    // Montage du composant
    public function mount()
    {
        $this->planet = auth()->user()->getActualPlanet();
        $this->planetId = $this->planet->id;
        
        // Charger les planètes de l'utilisateur
        $this->loadUserPlanets();
        
        // Vérifier si l'utilisateur peut lancer différents types de missions
        $this->checkMissionAvailability();

        // Charger les bookmarks de l'utilisateur et le compteur/limite
        $this->loadBookmarks();
        $this->bookmarkLimit = $this->getBookmarkLimit();
        $this->bookmarkCount = $this->getBookmarkCount();

        // Compter les flottes en vol et la limite autorisée
        $this->fleetCurrent = PlanetMission::countUserFlyingMissions(auth()->id());
        $this->fleetLimit = PlanetMission::getAllowedFlyingFleetsForPlanet($this->planetId);
    }
    
    // Charger les planètes de l'utilisateur
    private function loadUserPlanets()
    {
        $this->userPlanets = auth()->user()->planets()
            ->with('templatePlanet')
            ->get()
            ->map(function ($planet) {
                return [
                    'id' => $planet->id,
                    'name' => $planet->name,
                    'galaxy' => $planet->templatePlanet->galaxy,
                    'system' => $planet->templatePlanet->system,
                    'position' => $planet->templatePlanet->position,
                    'is_current' => $planet->id === $this->planetId
                ];
            })
            ->toArray();
    }
    
    // Vérifier la disponibilité des différents types de missions
    private function checkMissionAvailability()
    {
        // Vérifier si l'utilisateur a des vaisseaux de combat pour une attaque spatiale
        $this->canAttackSpatial = PlanetShip::where('planet_id', $this->planetId)
            ->whereHas('ship', function($query) {
                $query->where('attack_power', '>', 0);
            })
            ->exists();
        
        // Vérifier si l'utilisateur a des unités terrestres pour une attaque terrestre
        $this->canAttackEarth = PlanetUnit::where('planet_id', $this->planetId)
            ->whereHas('unit', function($query) {
                $query->where('attack_power', '>', 0);
            })
            ->exists();
        
        // Vérifier si l'utilisateur a des vaisseaux d'espionnage
        $this->canSpy = PlanetShip::where('planet_id', $this->planetId)
            ->whereHas('ship', function($query) {
                $query->where('name', 'scout_quantique');
            })
            ->exists();
        
        // Vérifier si l'utilisateur a des vaisseaux de transport
        $this->canTransport = PlanetShip::where('planet_id', $this->planetId)
            ->whereHas('ship', function($query) {
                $query->where('name', 'transporteur_delta');
            })
            ->exists();
        
        // Vérifier si l'utilisateur a des vaisseaux de colonisation
        $this->canColonize = PlanetShip::where('planet_id', $this->planetId)
            ->whereHas('ship', function($query) {
                $query->where('name', 'vaisseau_commandement');
            })
            ->exists();
        
        // Vérifier si l'utilisateur a d'autres planètes pour le basement
        $this->canBasement = auth()->user()->planets()->count() > 1;
        
        // Vérifier si l'utilisateur a des transporteurs delta pour l'extraction
        $this->canExtract = PlanetShip::where('planet_id', $this->planetId)
            ->whereHas('ship', function($query) {
                $query->where('name', 'transporteur_delta');
            })
            ->exists();

        // Vérifier si l'utilisateur a des vaisseaux d'exploration
        // Exploration requiert soit un Drone Stratos, soit un Scout Quantique
        $this->canExplore = PlanetShip::where('planet_id', $this->planetId)
            ->whereHas('ship', function($query) {
                $query->whereIn('name', ['drone_stratos', 'scout_quantique']);
            })
            ->exists();
    }

    private function loadBookmarks(): void
    {
        $this->bookmarks = auth()->user()->bookmarks()
            ->active()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($b) {
                return [
                    'id' => $b->id,
                    'label' => $b->label,
                    'galaxy' => $b->galaxy,
                    'system' => $b->system,
                    'position' => $b->position,
                    'planet_id' => $b->planet_id,
                    'mission_type' => $b->mission_type,
                ];
            })
            ->toArray();

        // Mettre à jour le compteur courant
        $this->bookmarkCount = $this->getBookmarkCount();
    }

    private function getBookmarkLimit(): int
    {
        $user = auth()->user();
        if (method_exists($user, 'vip_active') || isset($user->vip_active)) {
            return $user->vip_active ? ServerConfig::getMaxBookmarksVip() : ServerConfig::getMaxBookmarksNormal();
        }
        return ServerConfig::getMaxBookmarksNormal();
    }

    private function getBookmarkCount(): int
    {
        return auth()->user()->bookmarks()->active()->count();
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['mission', 'bookmarks']) ? $tab : 'mission';
    }

    public function selectBookmark($bookmarkId): void
    {
        if ($bookmarkId === '' || $bookmarkId === null) {
            return;
        }

        $id = (int) $bookmarkId;
        if ($id <= 0) {
            return;
        }

        $bookmark = UserBookmark::where('id', $id)
            ->where('user_id', auth()->id())
            ->active()
            ->first();

        if (!$bookmark) {
            return;
        }

        if ($bookmark->galaxy && $bookmark->system && $bookmark->position) {
            $this->targetGalaxy = $bookmark->galaxy;
            $this->targetSystem = $bookmark->system;
            $this->targetPosition = $bookmark->position;
        } elseif ($bookmark->planet_id) {
            $planet = Planet::with('templatePlanet')->find($bookmark->planet_id);
            if ($planet && $planet->templatePlanet) {
                $this->targetGalaxy = $planet->templatePlanet->galaxy;
                $this->targetSystem = $planet->templatePlanet->system;
                $this->targetPosition = $planet->templatePlanet->position;
            }
        }
    }

    public function deleteBookmark(int $bookmarkId): void
    {
        $bookmark = UserBookmark::where('id', $bookmarkId)
            ->where('user_id', auth()->id())
            ->first();
        if (!$bookmark) {
            return;
        }

        $bookmark->delete();
        $this->loadBookmarks();
        $this->bookmarkCount = $this->getBookmarkCount();

        $this->dispatch('swal:success', [
            'title' => 'Bookmark supprimé',
            'text' => 'Le bookmark a été supprimé.'
        ]);
    }
    
    // Sélectionner un type de mission
    public function selectMissionType($type)
    {
        $this->missionType = $type;
    }
    
    // Sélectionner une planète d'arrivée parmi les planètes de l'utilisateur (bookmark)
    public function selectSourcePlanet($planetId)
    {
        $planet = Planet::find($planetId);
        if ($planet && $planet->user_id === auth()->id()) {
            $this->targetGalaxy = $planet->templatePlanet->galaxy;
            $this->targetSystem = $planet->templatePlanet->system;
            $this->targetPosition = $planet->templatePlanet->position;
        }
    }
    
    // Continuer vers la mission spécifique
    public function continueMission()
    {
        // Vérifier que les coordonnées sont valides
        if ($this->targetGalaxy < 1 || $this->targetSystem < 1 || $this->targetPosition < 1) {
            $this->dispatch('swal:error', [
                'title' => 'Coordonnées invalides',
                'text' => 'Veuillez entrer des coordonnées valides.'
            ]);
            return;
        }
        
        // Récupérer la planète cible pour tous les types de mission
        $targetPlanet = Planet::whereHas('templatePlanet', function($query) {
            $query->where('galaxy', $this->targetGalaxy)
                  ->where('system', $this->targetSystem)
                  ->where('position', $this->targetPosition);
        })->with('user')->first();
        
        // Récupérer le template de planète pour la colonisation / extraction / exploration
        $templatePlanet = null;
        if (in_array($this->missionType, ['colonize', 'extract', 'explore'])) {
            $templatePlanet = \App\Models\Template\TemplatePlanet::where('galaxy', $this->targetGalaxy)
                ->where('system', $this->targetSystem)
                ->where('position', $this->targetPosition)
                ->first();
        }
        
        // Vérifications spécifiques selon le type de mission
        switch ($this->missionType) {
            case 'attack_spatial':
            case 'attack_earth':
            case 'spy':
                // Pour les attaques et l'espionnage, vérifier que la planète cible est colonisée
                if (!$targetPlanet) {
                    $this->dispatch('swal:error', [
                        'title' => 'Erreur',
                        'text' => 'Aucune planète colonisée trouvée à ces coordonnées.'
                    ]);
                    return;
                }
                
                // Vérifier que la planète n'appartient pas à l'utilisateur
                if ($targetPlanet->user_id === auth()->id()) {
                    $this->dispatch('swal:error', [
                        'title' => 'Erreur',
                        'text' => 'Vous ne pouvez pas attaquer/espionner votre propre planète.'
                    ]);
                    return;
                }
                
                // Vérifier que la planète n'appartient pas à un membre de l'alliance de l'utilisateur
                if (auth()->user()->isInAlliance() && $targetPlanet->user && $targetPlanet->user->alliance_id === auth()->user()->alliance_id) {
                    $this->dispatch('swal:error', [
                        'title' => 'Erreur',
                        'text' => 'Vous ne pouvez pas attaquer/espionner un membre de votre alliance.'
                    ]);
                    return;
                }
                break;
                
            case 'transport':
                // Pour le transport, vérifier que la planète cible est colonisée
                if (!$targetPlanet) {
                    $this->dispatch('swal:error', [
                        'title' => 'Erreur',
                        'text' => 'Aucune planète colonisée trouvée à ces coordonnées.'
                    ]);
                    return;
                }
                break;
                
            case 'colonize':
                // Pour la colonisation, vérifier que la planète cible n'est PAS colonisée
                if ($targetPlanet) {
                    $this->dispatch('swal:error', [
                        'title' => 'Erreur',
                        'text' => 'Cette planète est déjà colonisée.'
                    ]);
                    return;
                }
                
                // Vérifier que la position existe dans la galaxie
                if (!$templatePlanet) {
                    $this->dispatch('swal:error', [
                        'title' => 'Erreur',
                        'text' => 'Aucune planète n\'existe à ces coordonnées.'
                    ]);
                    return;
                }
                break;

            case 'extract':
                // Pour l'extraction, vérifier que la position existe
                if (!$templatePlanet) {
                    $this->dispatch('swal:error', [
                        'title' => 'Erreur',
                        'text' => 'Aucune planète n\'existe à ces coordonnées.'
                    ]);
                    return;
                }
                break;

            case 'basement':
                // Pour le basement, vérifier que la planète cible est colonisée
                if (!$targetPlanet) {
                    $this->dispatch('swal:error', [
                        'title' => 'Erreur',
                        'text' => 'Aucune planète colonisée trouvée à ces coordonnées.'
                    ]);
                    return;
                }
                break;

            case 'explore':
                // Pour l'exploration, vérifier simplement que la position existe
                if (!$templatePlanet) {
                    $this->dispatch('swal:error', [
                        'title' => 'Erreur',
                        'text' => 'Aucune planète n\'existe à ces coordonnées.'
                    ]);
                    return;
                }
                break;
        }
        
        // Rediriger vers la page appropriée en fonction du type de mission
        switch ($this->missionType) {
            case 'attack_spatial':
                return redirect()->route('game.mission.spatial', [
                    'targetPlanetId' => $targetPlanet->id
                ]);
                
            case 'attack_earth':
                return redirect()->route('game.mission.earth', [
                    'targetPlanetId' => $targetPlanet->id
                ]);
                
            case 'spy':
                return redirect()->route('game.mission.spy', [
                    'targetPlanetId' => $targetPlanet->id
                ]);
                
            case 'transport':
                return redirect()->route('game.mission.transport', [
                    'targetPlanetId' => $targetPlanet->id
                ]);
                
            case 'colonize':
                return redirect()->route('game.mission.colonize', [
                    'templateId' => $templatePlanet->id
                ]);
                
            case 'basement':
                return redirect()->route('game.mission.basement', [
                    'targetPlanetId' => $targetPlanet->id
                ]);
                
            case 'extract':
                return redirect()->route('game.mission.extract', [
                    'templateId' => $templatePlanet->id
                ]);

            case 'explore':
                return redirect()->route('game.mission.explore', [
                    'templateId' => $templatePlanet->id
                ]);
                
            default:
                $this->dispatch('swal:error', [
                    'title' => 'Type de mission invalide',
                    'text' => 'Veuillez sélectionner un type de mission valide.'
                ]);
                return;
        }
    }
    
    // Rendu du composant
    public function render()
    {
        return view('livewire.game.mission.mission');
    }
}