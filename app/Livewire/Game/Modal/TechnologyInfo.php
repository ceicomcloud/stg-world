<?php

namespace App\Livewire\Game\Modal;

use App\Models\User\UserTechnology;
use App\Models\Template\TemplateBuild;
use LivewireUI\Modal\ModalComponent;

class TechnologyInfo extends ModalComponent
{
    public $technologyId;
    public $technologyName;
    public $technologyLevel;
    public $technologyData = [];
    public $user;
    public $type = 'technology';

    public function mount($technologyId = null, $type = 'technology')
    {
        $this->technologyId = $technologyId;
        $this->type = $type;
        $this->user = auth()->user();
        
        if ($this->technologyId && $this->user) {
            $this->loadTechnologyData();
        }
    }

    public function loadTechnologyData()
    {
        // Récupérer le template de la technologie
        $templateTechnology = TemplateBuild::where('id', $this->technologyId)
            ->with([
                'costs.resource',
                'requirements',
                'advantages',
                'disadvantages'
            ])
            ->first();

        if (!$templateTechnology) {
            $this->technologyName = 'Technologie inconnue';
            $this->technologyLevel = 0;
            return;
        }

        // Récupérer la technologie de l'utilisateur
        $userTechnology = $this->user->technologies()
            ->where('technology_id', $this->technologyId)
            ->first();
        
        $this->technologyName = $templateTechnology->label;
        $this->technologyLevel = $userTechnology ? $userTechnology->level : 0;
        
        // Préparer les données de la technologie
        $this->technologyData = [
            'label' => $templateTechnology->label,
            'description' => $templateTechnology->description,
            'category' => $templateTechnology->category,
            'icon' => $templateTechnology->icon,
            'max_level' => $templateTechnology->max_level,
            'current_level' => $this->technologyLevel,
            'next_level' => $this->technologyLevel + 1,
            'type' => $this->type,
            'advantages' => $templateTechnology->advantages->map(function($advantage) {
                return [
                    'name' => $advantage->name,
                    'description' => $advantage->getDescriptionAttribute()
                ];
            })->toArray(),
            'disadvantages' => $templateTechnology->disadvantages->map(function($disadvantage) {
                return [
                    'name' => $disadvantage->name,
                    'description' => $disadvantage->getDescriptionAttribute()
                ];
            })->toArray(),
            'requirements' => $this->getTechnologyRequirements($templateTechnology),
            'costs' => $this->getTechnologyCosts($templateTechnology),
            'can_research' => $this->canResearch($templateTechnology),
            'requirements_met' => $this->checkRequirements($templateTechnology)
        ];
    }
    
    public function getTechnologyRequirements($templateTechnology)
    {
        $requirements = [];
        
        foreach ($templateTechnology->requirements as $requirement) {
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
        
        $userTechnology = $this->user->technologies
            ->where('technology_id', $requiredBuild['id'])
            ->first();
        
        if (!$userTechnology) {
            return false;
        }
        
        return $userTechnology->level >= $requirement['required_level'];
    }

    public function getTechnologyCosts($technology)
    {
        $nextLevel = $this->technologyLevel + 1;
        $costs = $technology->costs;

        return $costs->map(function ($cost) use ($nextLevel) {
            // Nouveau calcul: coût de base multiplié par le niveau
            $calculatedCost = (int) ($cost->base_cost * $nextLevel);
            
            return [
                'resource_name' => $cost->resource->display_name ?? $cost->resource->name,
                'resource_icon' => $cost->resource->icon,
                'base_cost' => $cost->base_cost,
                'calculated_cost' => $calculatedCost,
                'cost_multiplier' => $cost->cost_multiplier
            ];
        })->toArray();
    }

    public function canResearch($technology)
    {
        $nextLevel = $this->technologyLevel + 1;
        
        // Vérifier si la technologie est déjà au niveau maximum
        if ($technology->max_level > 0 && $nextLevel > $technology->max_level) {
            return false;
        }

        // Vérifier les prérequis
        if (!$this->checkRequirements($technology)) {
            return false;
        }

        // Vérifier les points de recherche
        $costs = $this->getTechnologyCosts($technology);
        $totalCost = 0;
        
        foreach ($costs as $cost) {
            $totalCost += $cost['calculated_cost'];
        }
        
        return $this->user->research_points >= $totalCost;
    }

    public function checkRequirements($technology)
    {
        foreach ($technology->requirements as $requirement) {
            $userTech = $this->user->technologies()
                ->where('technology_id', $requirement->required_build_id)
                ->first();

            $currentLevel = $userTech ? $userTech->level : 0;
            
            if ($currentLevel < $requirement->required_level) {
                return false;
            }
        }

        return true;
    }
    
    public function getTypeIcon()
    {
        return match($this->technologyData['category'] ?? 'technology') {
            'research' => 'microscope',
            'military' => 'shield-alt',
            'civil' => 'city',
            'espionage' => 'eye',
            'basic' => 'cog',
            default => 'flask'
        };
    }
    
    public function getTypeLabel()
    {
        return match($this->technologyData['category'] ?? 'technology') {
            'research' => 'Recherche',
            'military' => 'Militaire',
            'civil' => 'Civil',
            'espionage' => 'Espionnage',
            'basic' => 'Basique',
            default => 'Technologie'
        };
    }

    public function render()
    {
        return view('livewire.game.modal.technology-info');
    }
}