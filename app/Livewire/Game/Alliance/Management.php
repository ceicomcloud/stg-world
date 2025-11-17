<?php

namespace App\Livewire\Game\Alliance;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Traits\LogsUserActions;
use Illuminate\Support\Facades\Auth;
use App\Models\Alliance\AllianceRank;
use App\Models\Alliance\AllianceMember;

#[Layout('livewire.layouts.alliance-layout')]
class Management extends Component
{
    use LogsUserActions;

    public $alliance = null;
    public $userAllianceMember = null;

    public bool $showKickModal = false;
    public ?int $selectedMemberId = null;

    public $membersPage = 1;
    public $perPage = 10;

    public function mount()
    {
        $user = Auth::user();
        $this->alliance = $user->alliance;
        $this->userAllianceMember = $user->allianceMember;
        $this->dispatch('setAllianceTab', tab: 'member-management');
    }

    public function assignRank(int $memberId, $rankId)
    {
        if (!$this->userAllianceMember || !$this->userAllianceMember->hasPermission('manage_ranks')) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas la permission d\'assigner des rangs.'
            ]);
            return;
        }

        $rankId = (int) $rankId;

        $member = $this->alliance->members()->where('id', $memberId)->first();
        if (!$member) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Membre introuvable.'
            ]);
            return;
        }

        $rank = $this->alliance->ranks()->where('id', $rankId)->first();
        if (!$rank) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Rang invalide.'
            ]);
            return;
        }

        $member->rank_id = $rank->id;
        $member->save();

        $this->alliance->refresh();
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Rang assigné avec succès.'
        ]);
    }

    public function dismissModals(): void
    {
        $this->showKickModal = false;
        $this->selectedMemberId = null;
    }    

    public function confirmKick(int $memberId): void
    {
        $this->selectedMemberId = $memberId;
        $this->showKickModal = true;
    }

    public function performKick(): void
    {
        $memberId = $this->selectedMemberId;
        $this->dismissModals();

        if (!$memberId) {
            return;
        }

        if (!$this->userAllianceMember || !$this->userAllianceMember->hasPermission('manage_members')) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous n\'avez pas la permission de gérer les membres.'
            ]);
            return;
        }

        $member = AllianceMember::where('id', $memberId)
            ->where('alliance_id', $this->alliance->id)
            ->with(['user', 'rank'])
            ->first();

        if (!$member) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Membre introuvable.'
            ]);
            return;
        }

        // Ne pas exclure le leader
        if ($member->user_id === $this->alliance->leader_id) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Impossible d\'exclure le leader.'
            ]);
            return;
        }

        try {
            $member->user->update(['alliance_id' => null]);
            $member->delete();

            $this->alliance->refresh();

            $this->dispatch('toast:success', [
                'title' => 'Succès!',
                'text' => 'Le membre a été exclu de l\'alliance.'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Erreur lors de l\'exclusion du membre.'
            ]);
        }
    }

    public function render()
    {
        $data = [
            'availablePermissions' => AllianceRank::PERMISSIONS,
            'rankLevels' => AllianceRank::LEVELS
        ];

        $data['ranks'] = $this->alliance->ranks()->orderBy('level', 'desc')->get();

        $data['members'] = $this->alliance->members()->with(['user', 'rank'])->paginate($this->perPage, ['*'], 'members', $this->membersPage);

        return view('livewire.game.alliance.management', $data);
    }
}
