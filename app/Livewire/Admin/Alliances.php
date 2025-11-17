<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Alliance\Alliance;
use App\Models\Alliance\AllianceMember;
use App\Models\Alliance\AllianceRank;
use App\Models\Alliance\AllianceTechnology;
use App\Models\Alliance\AllianceApplication;
use App\Models\Alliance\AllianceWar;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

#[Layout('components.layouts.admin')]
class Alliances extends Component
{
    use WithPagination;
    
    // Propriétés pour la pagination et le filtrage
    public $search = '';
    public $perPage = 15;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Propriétés pour l'onglet actif
    public $activeTab = 'list';
    
    // Propriétés pour l'alliance sélectionnée
    public $selectedAlliance = null;
    public $selectedAllianceId = null;
    
    // Propriétés pour les onglets de détails
    public $allianceDetailTab = 'info';
    
    // Propriétés pour la banque d'alliance
    public $bankForm = [
        'amount' => 0,
        'operation' => 'add'
    ];
    
    // Propriétés pour les technologies d'alliance
    public $selectedTechnology = null;
    
    /**
     * Réinitialiser la pagination lors de la recherche
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Trier les résultats
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    /**
     * Changer d'onglet principal
     */
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        
        // Réinitialiser les données si nécessaire
        if ($tab === 'list') {
            $this->selectedAlliance = null;
            $this->selectedAllianceId = null;
            $this->allianceDetailTab = 'info';
        }
    }
    
    /**
     * Changer d'onglet de détails alliance
     */
    public function setAllianceDetailTab($tab)
    {
        $this->allianceDetailTab = $tab;
    }
    
    /**
     * Sélectionner une alliance pour voir ses détails
     */
    public function selectAlliance($allianceId)
    {
        $this->selectedAllianceId = $allianceId;
        $this->selectedAlliance = Alliance::with(['leader', 'members.user', 'ranks', 'technologies'])->find($allianceId);
        $this->allianceDetailTab = 'info';
        $this->activeTab = 'detail';
    }
    
    /**
     * Obtenir les membres de l'alliance sélectionnée
     */
    public function getAllianceMembers()
    {
        if (!$this->selectedAllianceId) {
            return [];
        }
        
        return AllianceMember::where('alliance_id', $this->selectedAllianceId)
            ->with(['user', 'rank'])
            ->get();
    }
    
    /**
     * Obtenir les rangs de l'alliance sélectionnée
     */
    public function getAllianceRanks()
    {
        if (!$this->selectedAllianceId) {
            return [];
        }
        
        return AllianceRank::where('alliance_id', $this->selectedAllianceId)
            ->orderBy('level', 'desc')
            ->get();
    }
    
    /**
     * Obtenir les technologies de l'alliance sélectionnée
     */
    public function getAllianceTechnologies()
    {
        if (!$this->selectedAllianceId) {
            return [];
        }
        
        return AllianceTechnology::where('alliance_id', $this->selectedAllianceId)
            ->get();
    }
    
    /**
     * Obtenir les candidatures de l'alliance sélectionnée
     */
    public function getAllianceApplications()
    {
        if (!$this->selectedAllianceId) {
            return [];
        }
        
        return AllianceApplication::where('alliance_id', $this->selectedAllianceId)
            ->with(['user', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Obtenir les guerres de l'alliance sélectionnée
     */
    public function getAllianceWars()
    {
        if (!$this->selectedAllianceId) {
            return [];
        }
        
        $attackerWars = AllianceWar::where('attacker_alliance_id', $this->selectedAllianceId)
            ->with(['defenderAlliance', 'declaredBy', 'endedBy'])
            ->get();
            
        $defenderWars = AllianceWar::where('defender_alliance_id', $this->selectedAllianceId)
            ->with(['attackerAlliance', 'declaredBy', 'endedBy'])
            ->get();
            
        return $attackerWars->merge($defenderWars)->sortByDesc('created_at');
    }
    
    /**
     * Améliorer une technologie d'alliance
     */
    public function upgradeTechnology($technologyId)
    {
        $technology = AllianceTechnology::find($technologyId);
        
        if (!$technology || $technology->alliance_id !== $this->selectedAllianceId) {
            $this->dispatch('admin:toast:error', ['message' => 'Technologie non trouvée']);
            return;
        }
        
        $upgradeCost = $technology->getUpgradeCost();
        
        if ($this->selectedAlliance->deuterium_bank < $upgradeCost) {
            $this->dispatch('admin:toast:error', ['message' => 'Pas assez de deuterium dans la banque']);
            return;
        }
        
        // Retirer le deuterium de la banque
        $this->selectedAlliance->withdrawFromDeuteriumBank($upgradeCost);
        
        // Améliorer la technologie
        $technology->upgrade();
        
        // Rafraîchir les données
        $this->selectedAlliance = Alliance::with(['leader', 'members.user', 'ranks', 'technologies'])->find($this->selectedAllianceId);
        
        $this->dispatch('admin:toast:success', ['message' => 'Technologie améliorée avec succès']);
    }
    
    /**
     * Gérer la banque d'alliance
     */
    public function manageBankOperation()
    {
        $this->validate([
            'bankForm.amount' => 'required|integer|min:1',
            'bankForm.operation' => 'required|in:add,withdraw'
        ]);
        
        $amount = (int) $this->bankForm['amount'];
        
        if ($this->bankForm['operation'] === 'add') {
            $this->selectedAlliance->addToDeuteriumBank($amount);
            $this->dispatch('admin:toast:success', ['message' => $amount . ' deuterium ajouté à la banque']);
        } else {
            if ($this->selectedAlliance->deuterium_bank < $amount) {
                $this->dispatch('admin:toast:error', ['message' => 'Pas assez de deuterium dans la banque']);
                return;
            }
            
            $this->selectedAlliance->withdrawFromDeuteriumBank($amount);
            $this->dispatch('admin:toast:success', ['message' => $amount . ' deuterium retiré de la banque']);
        }
        
        // Réinitialiser le formulaire
        $this->bankForm = [
            'amount' => 0,
            'operation' => 'add'
        ];
        
        // Rafraîchir les données
        $this->selectedAlliance = Alliance::with(['leader', 'members.user', 'ranks', 'technologies'])->find($this->selectedAllianceId);
    }
    
    /**
     * Obtenir la liste des alliances filtrée
     */
    public function getAlliances()
    {
        $query = Alliance::query()
            ->with('leader')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('tag', 'like', '%' . $this->search . '%');
                });
            });
        
        return $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
    
    /**
     * Rendu du composant
     */
    public function render()
    {
        return view('livewire.admin.alliances', [
            'alliances' => $this->getAlliances(),
            'allianceMembers' => $this->getAllianceMembers(),
            'allianceRanks' => $this->getAllianceRanks(),
            'allianceTechnologies' => $this->getAllianceTechnologies(),
            'allianceApplications' => $this->getAllianceApplications(),
            'allianceWars' => $this->getAllianceWars(),
        ])->title('Gestion des alliances');
    }
}