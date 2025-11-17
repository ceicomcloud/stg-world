<?php

namespace App\Livewire\Game\Modal;

use App\Models\Planet\PlanetMission;
use App\Models\Template\TemplateBuild;
use LivewireUI\Modal\ModalComponent;

class MissionInfo extends ModalComponent
{
    public $missionId;
    public $missionData = [];
    
    public function mount($missionId = null)
    {        
        $this->missionId = $missionId;
        
        if ($this->missionId) {
            $this->loadMissionData();
        }
    }
    
    public function loadMissionData()
    {        
        // Récupérer la mission avec les relations nécessaires
        $mission = PlanetMission::where('id', $this->missionId)
            ->where('user_id', auth()->id())
            ->with(['fromPlanet.templatePlanet', 'toPlanet.templatePlanet'])
            ->first();
            
        if (!$mission) {
            return;
        }
        
        // Préparer les données des vaisseaux
        $ships = [];
        if ($mission->ships && is_array($mission->ships)) {
            foreach ($mission->ships as $shipId => $quantity) {
                $ship = TemplateBuild::find($shipId);
                if ($ship) {
                    $ships[] = [
                        'id' => $shipId,
                        'name' => $ship->label,
                        'icon' => $ship->icon,
                        'quantity' => $quantity
                    ];
                }
            }
        }
        
        // Préparer les données des ressources
        $resources = [];
        if ($mission->resources && is_array($mission->resources)) {
            // Récupérer tous les templates de ressources pour obtenir les noms
            $templateResources = \App\Models\Template\TemplateResource::all()->keyBy('id');
            
            foreach ($mission->resources as $resourceId => $amount) {
                if (isset($templateResources[$resourceId])) {
                    $resourceName = $templateResources[$resourceId]->name;
                    $resources[] = [
                        'id' => $resourceId,
                        'name' => $templateResources[$resourceId]->display_name ?? ucfirst($resourceName),
                        'icon' => $resourceName . '.png', // Supposant que les icônes suivent la convention de nommage
                        'amount' => $amount
                    ];
                }
            }
        }
        
        // Préparer les données du résultat
        $result = [];
        if ($mission->result && is_array($mission->result)) {
            $result = $mission->result;
        }
        
        // Assembler toutes les données
        $this->missionData = [
            'id' => $mission->id,
            'type' => $mission->mission_type,
            'type_label' => $mission->getType(),
            'status' => $mission->status,
            'status_label' => $mission->getStatus(),
            'departure_time' => $mission->departure_time,
            'arrival_time' => $mission->arrival_time,
            'return_time' => $mission->return_time,
            'time_remaining' => $mission->getTimeRemaining(),
            'from_planet' => [
                'id' => $mission->fromPlanet->id ?? null,
                'name' => $mission->fromPlanet->name ?? 'Planète inconnue',
                'coordinates' => [
                    'galaxy' => $mission->fromPlanet->templatePlanet->galaxy ?? 'N/A',
                    'system' => $mission->fromPlanet->templatePlanet->system ?? 'N/A',
                    'position' => $mission->fromPlanet->templatePlanet->position ?? 'N/A',
                ]
            ],
            'to_coordinates' => [
                'galaxy' => $mission->to_galaxy,
                'system' => $mission->to_system,
                'position' => $mission->to_position,
            ],
            'to_planet' => $mission->toPlanet ? [
                'id' => $mission->toPlanet->id ?? null,
                'name' => $mission->toPlanet->name ?? 'Planète inconnue',
                'coordinates' => [
                    'galaxy' => $mission->toPlanet->templatePlanet->galaxy ?? 'N/A',
                    'system' => $mission->toPlanet->templatePlanet->system ?? 'N/A',
                    'position' => $mission->toPlanet->templatePlanet->position ?? 'N/A',
                ]
            ] : null,
            'ships' => $ships,
            'resources' => $resources,
            'result' => $result
        ];
    }
    
    public function getMissionTypeIcon()
    {
        return match($this->missionData['type'] ?? '') {
            'colonize' => 'flag',
            'attack' => 'fighter-jet',
            'spy' => 'eye',
            'transport' => 'exchange',
            'defend' => 'shield-alt',
            'harvest' => 'tractor',
            'explore' => 'search',
            'extract' => 'mining-drill',
            'basement' => 'warehouse',
            default => 'rocket'
        };
    }
    
    public function getMissionStatusIcon()
    {
        return match($this->missionData['status'] ?? '') {
            'traveling' => 'plane-departure',
            'returning' => 'plane-arrival',
            'arrived' => 'check-circle',
            'completed' => 'check-double',
            'failed' => 'times-circle',
            'cancelled' => 'ban',
            'pending' => 'clock',
            default => 'question-circle'
        };
    }
    
    public function render()
    {
        return view('livewire.game.modal.mission-info');
    }
}