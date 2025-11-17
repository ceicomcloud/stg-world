<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\BadgeService;
use Illuminate\Support\Facades\Auth;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetResource;
use App\Models\Planet\PlanetBuilding;
use App\Models\Template\TemplatePlanet;
use App\Models\Template\TemplateResource;
use App\Models\Template\TemplateBuild;
use App\Models\Server\ServerConfig;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.app')]

class Dashboard extends Component
{
    public $user;

    protected $badgeService;

    public function boot(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    public function mount()
    {
        $this->user = Auth::user();
    }

    public function handleMainAction()
    {        
        if ($this->user->main_planet_id) {
            return redirect()->route('game.index');
        } else {
            // Créer le compte joueur complet
            $this->createPlayerAccount();
            
            // Rediriger vers le jeu après création
            return redirect()->route('game.index');
        }
    }
    
    /**
     * Créer un compte joueur complet avec planète et ressources
     */
    private function createPlayerAccount()
    {
        DB::transaction(function () {
            // 1. Sélectionner une planète disponible aléatoirement
            $templatePlanet = TemplatePlanet::where('is_available', true)
                ->where('is_colonizable', true)
                ->where('type', 'planet')
                ->inRandomOrder()
                ->first();
                
            if (!$templatePlanet) {
                throw new \Exception('Aucune planète disponible pour la colonisation');
            }
            
            // 2. Créer la planète du joueur
            $planet = Planet::create([
                'user_id' => $this->user->id,
                'template_planet_id' => $templatePlanet->id,
                'name' => $templatePlanet->name ?: 'Planète Mère',
                'description' => $this->generateRandomPlanetDescription(),
                'used_fields' => 4,
                'is_main_planet' => true,
                'is_active' => true
            ]);
            
            // 3. Marquer la planète template comme occupée
            $templatePlanet->update([
                'is_occupied' => true,
                'is_available' => false
            ]);
            
            // 4. Mettre à jour l'utilisateur avec les IDs de planète
            $this->user->update([
                'main_planet_id' => $planet->id,
                'actual_planet_id' => $planet->id
            ]);
            
            // 5. Créer les ressources initiales
            $this->createInitialResources($planet);
            
            // 6. Créer les bâtiments de base
            $this->createInitialBuildings($planet);
            
            // 7. Attribuer le badge de première connexion
            $this->badgeService->awardCustomBadge($this->user, 'Premier Connexion');
            
            // Recharger l'utilisateur pour avoir les nouvelles données
            $this->user->refresh();
        });
    }
    
    /**
     * Créer les ressources initiales pour une nouvelle planète
     */
    private function createInitialResources(Planet $planet)
    {
        // Récupérer les ressources de départ depuis la configuration
        $startingResources = ServerConfig::getStartingResources();
        
        // Récupérer les templates de ressources
        $resources = TemplateResource::whereIn('name', ['metal', 'crystal', 'deuterium'])->get()->keyBy('name');
        
        foreach ($startingResources as $resourceName => $amount) {
            if (isset($resources[$resourceName])) {
                PlanetResource::create([
                    'planet_id' => $planet->id,
                    'resource_id' => $resources[$resourceName]->id,
                    'current_amount' => $amount,
                    'max_storage' => $resources[$resourceName]->base_storage ?? 10000,
                    'production_rate' => 100,
                    'last_update' => now(),
                    'is_active' => true
                ]);
            }
        }
    }

    /**
     * Créer les bâtiments de base pour une nouvelle planète
     */
    private function createInitialBuildings(Planet $planet)
    {
        // Récupérer les bâtiments de départ depuis la configuration
        $startingBuildings = ServerConfig::getStartingBuildings();
        
        // Récupérer les templates de bâtiments
        $buildings = TemplateBuild::whereIn('name', array_keys($startingBuildings))->get()->keyBy('name');
        
        foreach ($startingBuildings as $buildingName => $level) {
            if (isset($buildings[$buildingName])) {
                PlanetBuilding::create([
                    'planet_id' => $planet->id,
                    'building_id' => $buildings[$buildingName]->id,
                    'level' => $level,
                    'is_active' => true
                ]);
            }
        }
    }

    private function generateRandomPlanetDescription(): string
    {
        $descriptions = [
            'Une planète rocheuse aux vastes plaines désertiques',
            'Un monde océanique aux eaux cristallines',
            'Une planète volcanique aux paysages spectaculaires',
            'Un monde glacé aux aurores boréales magnifiques',
            'Une planète forestière aux écosystèmes luxuriants',
            'Un monde aride aux formations rocheuses uniques',
            'Une planète tropicale aux jungles denses',
            'Un monde montagneux aux pics enneigés',
            'Une planète marécageuse aux brumes mystérieuses',
            'Un monde steppique aux horizons infinis',
            'Une planète aux canyons profonds et colorés',
            'Un monde aux geysers et sources chaudes',
            'Une planète aux cratères météoritiques anciens',
            'Un monde aux aurores polaires permanentes',
            'Une planète aux formations cristallines naturelles',
            'Un monde aux vents violents et tempêtes de sable',
            'Une planète aux archipels et îles flottantes',
            'Un monde aux cavernes souterraines étendues',
            'Une planète aux champs magnétiques intenses',
            'Un monde aux saisons extrêmes et contrastées'
        ];

        return $descriptions[array_rand($descriptions)];
    }

    public function goToProfile()
    {
        return $this->redirect(route('dashboard.profile'), navigate: true);
    }

    public function goToSettings()
    {
        return $this->redirect(route('dashboard.settings'), navigate: true);
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        
        return redirect()->route('login');
    }

    public function getMainButtonText()
    {
        if ($this->user && $this->user->main_planet_id) {
            return 'Jouer';
        }
        
        return 'Créer un personnage';
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard');
    }

    /**
     * URL de l'avatar de l'utilisateur connecté (custom ou Gravatar)
     */
    public function getAvatarUrlProperty()
    {
        return $this->user ? $this->user->getUserAvatarUrl(96) : null;
    }
}