<?php

namespace App\Livewire\Game\Alliance;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Traits\LogsUserActions;
use Illuminate\Support\Facades\Auth;
use App\Models\Alliance\AllianceRank;
use App\Models\Alliance\AllianceTechnology;

#[Layout('livewire.layouts.alliance-layout')]
class Technology extends Component
{
    use LogsUserActions;

    public $alliance = null;
    public $userAllianceMember = null;

    public $selectedTechnology = null;
    public $showUpgradeModal = false;

    public function mount()
    {
        $user = Auth::user();
        $this->alliance = $user->alliance;
        $this->userAllianceMember = $user->allianceMember;
        $this->dispatch('setAllianceTab', tab: 'technologies');
    }

    public function showTechnologyUpgrade($technologyType)
    {
        if (!$this->userAllianceMember || !$this->userAllianceMember->hasPermission('manage_alliance')) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas la permission de gérer les technologies.'
            ]);
            return;
        }

        $this->selectedTechnology = $this->alliance->getTechnology($technologyType);
        if (!$this->selectedTechnology) {
            // Créer la technologie si elle n'existe pas
            $this->selectedTechnology = $this->alliance->technologies()->create([
                'technology_type' => $technologyType,
                'level' => 0,
                'max_level' => AllianceTechnology::MAX_LEVEL
            ]);
        }
        
        $this->showUpgradeModal = true;
    }

    public function upgradeTechnology()
    {
        if (!$this->selectedTechnology || !$this->selectedTechnology->canUpgrade()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Cette technologie ne peut pas être améliorée.'
            ]);
            return;
        }

        $cost = $this->selectedTechnology->getUpgradeCost();
        
        if ($this->alliance->deuterium_bank < $cost) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Pas assez de deuterium en banque. Coût: ' . number_format($cost) . ' deuterium.'
            ]);
            return;
        }

        try {
            $this->alliance->decrement('deuterium_bank', $cost);
            
            $this->selectedTechnology->upgrade();
            
            $this->closeUpgradeModal();
            
            $this->dispatch('toast:success', [
                'title' => 'Succès!',
                'text' => 'Technologie améliorée !'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Erreur lors de l\'amélioration de la technologie.'
            ]);
        }
    }

    public function closeUpgradeModal()
    {
        $this->showUpgradeModal = false;
        $this->selectedTechnology = null;
    }

    public function render()
    {
        $data = [
            'availablePermissions' => AllianceRank::PERMISSIONS,
            'rankLevels' => AllianceRank::LEVELS
        ];

        $this->alliance->initializeTechnologies();
            
        $data['technologies'] = $this->alliance->technologies()->get()->keyBy('technology_type');

        return view('livewire.game.alliance.technology', $data);
    }
}
