<?php

namespace App\Livewire\Game\Modal;

use App\Models\Planet\Planet;
use App\Models\Template\TemplatePlanet;
use App\Models\Planet\PlanetMission;
use App\Models\Planet\PlanetShip;
use App\Models\Planet\PlanetResource;
use App\Models\User\UserTechnology;
use App\Models\Template\TemplateBuild;
use App\Services\PrivateMessageService;
use App\Models\Server\ServerConfig;
use LivewireUI\Modal\ModalComponent;
use Carbon\Carbon;

class PlanetInfo extends ModalComponent
{
    public $planetId;
    public $galaxy;
    public $system;
    public $position;
    public $planetData = [];
    public $user;
    


    public function mount($planetId = null, $galaxy = null, $system = null, $position = null)
    {
        $this->planetId = $planetId;
        $this->galaxy = $galaxy;
        $this->system = $system;
        $this->position = $position;
        $this->user = auth()->user();
        
        if ($this->planetId) {
            $this->loadPlanetData();
        } elseif ($this->galaxy && $this->system && $this->position) {
            $this->loadFreePlanetData();
        }
    }

    public function openAddBookmark(): void
    {


        // Ouvrir la modal d'ajout de bookmark avec les coordonnées courantes
        $this->dispatch('openModal', component: 'game.modal.add-bookmark', arguments: [
            'title' => 'Ajouter un bookmark',
            'galaxy' => $this->galaxy,
            'system' => $this->system,
            'position' => $this->position,
            'planetId' => $this->planetId,
        ]);
    }

    public function loadPlanetData()
    {
        $planet = Planet::with(['user', 'templatePlanet', 'resources'])->find($this->planetId);
        
        if ($planet) {
            // Initialiser les coordonnées de la planète
            $this->galaxy = $planet->templatePlanet->galaxy;
            $this->system = $planet->templatePlanet->system;
            $this->position = $planet->templatePlanet->position;
            
            // Vérifier si c'est une planète bot (non colonisable)
            $isBot = $planet->templatePlanet && $planet->templatePlanet->is_colonizable === false;
            
            // Déterminer statut allié/enemi
            $isAllied = false;
            $isEnemy = false;
            if ($planet->user) {
                // Même alliance ou pacte accepté
                if ($this->user->alliance_id && $planet->user->alliance_id && $this->user->alliance_id === $planet->user->alliance_id) {
                    $isAllied = true;
                } else {
                    $relation = \App\Models\User\UserRelation::findBetween($this->user->id, $planet->user->id);
                    if ($relation && $relation->status === \App\Models\User\UserRelation::STATUS_ACCEPTED) {
                        $isAllied = true;
                    }
                }

                // Guerre d'alliance active
                if (!$isAllied && $this->user->alliance_id && $planet->user->alliance_id) {
                    $warActive = \App\Models\Alliance\AllianceWar::where('status', \App\Models\Alliance\AllianceWar::STATUS_ACTIVE)
                        ->where(function($q) use ($planet) {
                            $q->where('attacker_alliance_id', $this->user->alliance_id)
                              ->where('defender_alliance_id', $planet->user->alliance_id);
                        })
                        ->orWhere(function($q) use ($planet) {
                            $q->where('attacker_alliance_id', $planet->user->alliance_id)
                              ->where('defender_alliance_id', $this->user->alliance_id);
                        })
                        ->exists();
                    $isEnemy = $warActive;
                }
            }

            $this->planetData = [
                'id' => $planet->id,
                'name' => $planet->name,
                'image' => $planet->image,
                'type' => $planet->type,
                'size' => $planet->size,
                'temperature' => $planet->templatePlanet->min_temperature. '/' .$planet->templatePlanet->max_temperature,
                'used_fields' => $planet->used_fields,
                'max_fields' => $planet->templatePlanet ? $planet->templatePlanet->fields : 0,
                'is_main_planet' => $planet->is_main_planet,
                'coordinates' => "[{$planet->templatePlanet->galaxy}:{$planet->templatePlanet->system}:{$planet->templatePlanet->position}]",
                'user_id' => $planet->user_id,
                'user_name' => $planet->user ? $planet->user->name : 'Planète libre',
                'is_own_planet' => $planet->user_id === $this->user->id,
                'is_allied' => $isAllied,
                'is_enemy' => $isEnemy,
                'is_bot' => $isBot,
                'template' => $planet->templatePlanet,
                'created_at' => $planet->created_at,
                'last_update' => $planet->last_update,
                'is_protected' => $planet->isShieldProtectionActive(),
                'shield_protection_end' => $planet->shield_protection_end,
                'is_vacation_mode' => $planet->user && $planet->user->isInVacationMode(),
                'vacation_mode_until' => $planet->user ? $planet->user->vacation_mode_until : null
            ];
        }
    }

    public function loadFreePlanetData()
    {
        // Charger le template de planète pour cette position
        $template = TemplatePlanet::where('galaxy', $this->galaxy)
                                 ->where('system', $this->system)
                                 ->where('position', $this->position)
                                 ->first();
        
        if ($template) {
            // Vérifier si c'est une planète bot (non colonisable)
            $isBot = $template->is_colonizable === false;
            
            $this->planetData = [
                'id' => null,
                'name' => $isBot ? 'Planète PNJ' : 'Position libre',
                'type' => $template->type,
                'size' => $template->size,
                'temperature' => $template->min_temperature . '/' . $template->max_temperature,
                'used_fields' => 0,
                'max_fields' => $template->fields,
                'is_main_planet' => false,
                'coordinates' => "[{$this->galaxy}:{$this->system}:{$this->position}]",
                'user_id' => null,
                'user_name' => $isBot ? 'Planète PNJ' : 'Planète libre',
                'is_own_planet' => false,
                'is_bot' => $isBot,
                'template' => $template,
                'created_at' => null,
                'last_update' => null,
                'is_protected' => false,
                'image' => null,
                'is_vacation_mode' => false,
                'vacation_mode_until' => null
            ];
        }
    }

    /**
     * Redirect to colonize mission page
     */
    public function colonizePlanet()
    {
        if (!$this->planetData['user_id']) {
            $templatePlanet = TemplatePlanet::where('galaxy', $this->galaxy)
                ->where('system', $this->system)
                ->where('position', $this->position)
                ->first();

            if($templatePlanet) {
                $this->dispatch('closeModal');
                return redirect()->route('game.mission.colonize', [
                    'templateId' => $templatePlanet->id
                ]);
            } else {
                $this->dispatch('swal:error', [
                    'title' => 'Erreur',
                    'text' => 'Planète non trouvée'
                ]);
            }
        }
    }

    public function attackSpatialPlanet()
    {
        // Vérifier si la planète est en mode vacances
        if ($this->planetData['is_vacation_mode']) {
            $this->dispatch('swal:error', [
                'title' => 'Action impossible',
                'text' => 'Ce joueur est en mode vacances et ne peut pas être attaqué.'
            ]);
            return;
        }
        
        // Logique pour attaquer une planète
        if ($this->planetData['user_id'] && !$this->planetData['is_own_planet']) {
            $this->dispatch('swal:info', [
                'title' => 'Attaque',
                'text' => 'Fonctionnalité d\'attaque à venir!'
            ]);
        }

        $this->dispatch('closeModal');
        return redirect()->route('game.mission.spatial', ['targetPlanetId' => $this->planetId]);
    }

    public function attackEarthPlanet()
    {
        // Vérifier si la planète est en mode vacances
        if ($this->planetData['is_vacation_mode']) {
            $this->dispatch('swal:error', [
                'title' => 'Action impossible',
                'text' => 'Ce joueur est en mode vacances et ne peut pas être attaqué.'
            ]);
            return;
        }
        
        // Logique pour attaquer une planète
        if ($this->planetData['user_id'] && !$this->planetData['is_own_planet']) {
            $this->dispatch('swal:info', [
                'title' => 'Attaque',
                'text' => 'Fonctionnalité d\'attaque à venir!'
            ]);
        }

        $this->dispatch('closeModal');
        return redirect()->route('game.mission.earth', ['targetPlanetId' => $this->planetId]);
    }

    /**
     * Redirect to spy mission page
     */
    public function spyPlanet()
    {
        // Vérifier si la planète est en mode vacances
        if ($this->planetData['is_vacation_mode']) {
            $this->dispatch('swal:error', [
                'title' => 'Action impossible',
                'text' => 'Ce joueur est en mode vacances et ne peut pas être espionné.'
            ]);
            return;
        }
        
        if (!$this->planetData['user_id'] || $this->planetData['is_own_planet']) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous ne pouvez espionner que les planètes d\'autres joueurs.'
            ]);
            return;
        }
        
        $this->dispatch('closeModal');
        return redirect()->route('game.mission.spy', ['targetPlanetId' => $this->planetId]);
    }



    public function visitPlanet()
    {
        // Logique pour visiter sa propre planète
        if ($this->planetData['is_own_planet']) {
            // Changer la planète actuelle de l'utilisateur
            $this->user->actual_planet_id = $this->planetId;
            $this->user->save();
            
            $this->dispatch('closeModal');
            $this->dispatch('swal:success', [
                'title' => 'Planète changée',
                'text' => 'Vous êtes maintenant sur ' . $this->planetData['name']
            ]);
            
            // Rediriger vers la page d'accueil du jeu
            return redirect()->route('game.index');
        }
    }
    
    public function transportToPlanet()
    {
        // Vérifier que c'est bien notre planète et que ce n'est pas la planète actuelle
        if (!$this->planetData['is_own_planet'] || $this->planetId == $this->user->actual_planet_id) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous ne pouvez transporter des ressources que vers vos autres planètes.'
            ]);
            return;
        }
        
        $this->dispatch('closeModal');
        return redirect()->route('game.mission.transport', ['targetPlanetId' => $this->planetId]);
    }

    /**
     * Transporter des ressources vers une planète alliée
     */
    public function transportToAllyPlanet()
    {
        if (!$this->planetData['user_id'] || $this->planetData['is_own_planet'] || !$this->planetData['is_allied']) {
            $this->dispatch('swal:error', [
                'title' => 'Action impossible',
                'text' => 'Le transport est réservé aux planètes alliées non possédées.'
            ]);
            return;
        }

        $this->dispatch('closeModal');
        return redirect()->route('game.mission.transport', ['targetPlanetId' => $this->planetId]);
    }


    
    public function render()
    {
        return view('livewire.game.modal.planet-info');
    }
}