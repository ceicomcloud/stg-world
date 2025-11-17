<?php

namespace App\Livewire\Game\Alliance;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Traits\LogsUserActions;
use App\Models\Alliance\AllianceRank;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

#[Layout('livewire.layouts.alliance-layout')]
class Rank extends Component
{
    use LogsUserActions;

    public $alliance = null;
    public $userAllianceMember = null;

    public $editingRank = null;
    public $newRankName = '';
    public $newRankLevel = 1;
    public $newRankPermissions = [];

    public function mount()
    {
        $user = Auth::user();
        $this->alliance = $user->alliance;
        $this->userAllianceMember = $user->allianceMember;
        $this->dispatch('setAllianceTab', tab: 'ranks');
    }

    public function editRank(int $rankId)
    {
        if (!$this->userAllianceMember || !$this->userAllianceMember->hasPermission('manage_ranks')) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas la permission de gérer les rangs.'
            ]);
            return;
        }

        $rank = $this->alliance->ranks()->where('id', $rankId)->first();
        if (!$rank) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Rang introuvable.'
            ]);
            return;
        }

        $this->editingRank = $rank->id;
        $this->newRankName = $rank->name;
        $this->newRankLevel = $rank->level;
        $this->newRankPermissions = $rank->permissions ?? [];
    }

    public function cancelEditRank()
    {
        $this->editingRank = null;
        $this->reset(['newRankName', 'newRankLevel', 'newRankPermissions']);
        $this->newRankLevel = 1;
    }

    public function createRank()
    {
        if (!$this->userAllianceMember || !$this->userAllianceMember->hasPermission('manage_ranks')) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas la permission de gérer les rangs.'
            ]);
            return;
        }

        $this->validate([
            'newRankName' => [
                'required', 'string', 'max:50',
                Rule::unique('alliance_ranks', 'name')->where(fn($q) => $q->where('alliance_id', $this->alliance->id))
            ],
            'newRankLevel' => 'required|integer|min:1|max:10',
            'newRankPermissions' => 'array'
        ]);

        $validPerms = array_keys(AllianceRank::PERMISSIONS);
        $perms = array_values(array_intersect($this->newRankPermissions ?? [], $validPerms));

        AllianceRank::create([
            'alliance_id' => $this->alliance->id,
            'name' => $this->newRankName,
            'level' => $this->newRankLevel,
            'permissions' => $perms,
        ]);

        $this->cancelEditRank();
        $this->alliance->refresh();
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Rang créé avec succès.'
        ]);
    }

    public function updateRank()
    {
        if (!$this->userAllianceMember || !$this->userAllianceMember->hasPermission('manage_ranks')) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas la permission de gérer les rangs.'
            ]);
            return;
        }

        if (!$this->editingRank) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Aucun rang en édition.'
            ]);
            return;
        }

        $rank = $this->alliance->ranks()->where('id', $this->editingRank)->first();
        if (!$rank) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Rang introuvable.'
            ]);
            return;
        }

        $this->validate([
            'newRankName' => [
                'required', 'string', 'max:50',
                Rule::unique('alliance_ranks', 'name')
                    ->ignore($rank->id)
                    ->where(fn($q) => $q->where('alliance_id', $this->alliance->id))
            ],
            'newRankLevel' => 'required|integer|min:1|max:10',
            'newRankPermissions' => 'array'
        ]);

        $validPerms = array_keys(AllianceRank::PERMISSIONS);
        $perms = array_values(array_intersect($this->newRankPermissions ?? [], $validPerms));

        $rank->update([
            'name' => $this->newRankName,
            'level' => $this->newRankLevel,
            'permissions' => $perms,
        ]);

        $this->cancelEditRank();
        $this->alliance->refresh();
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Rang mis à jour avec succès.'
        ]);
    }

    public function deleteRank(int $rankId)
    {
        if (!$this->userAllianceMember || !$this->userAllianceMember->hasPermission('manage_ranks')) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas la permission de gérer les rangs.'
            ]);
            return;
        }

        $rank = $this->alliance->ranks()->where('id', $rankId)->first();
        if (!$rank) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Rang introuvable.'
            ]);
            return;
        }

        if ($rank->level <= 1) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Ce rang de base ne peut pas être supprimé.'
            ]);
            return;
        }

        if ($rank->members()->exists()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Impossible de supprimer un rang assigné à des membres.'
            ]);
            return;
        }

        $rank->delete();

        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Rang supprimé avec succès.'
        ]);
    }

    public function render()
    {
        $data = [
            'availablePermissions' => AllianceRank::PERMISSIONS,
            'rankLevels' => AllianceRank::LEVELS
        ];

        $data['ranks'] = $this->alliance->ranks()->orderBy('level', 'desc')->get();

        return view('livewire.game.alliance.rank', $data);
    }
}
