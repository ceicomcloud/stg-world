<?php

namespace App\Livewire\Game\Alliance;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Traits\LogsUserActions;
use App\Models\Alliance\AllianceRank;
use App\Models\Alliance\AllianceWar;
use Illuminate\Support\Facades\Auth;

#[Layout('livewire.layouts.alliance-layout')]
class Wars extends Component
{
    use LogsUserActions;

    public $alliance = null;
    public $userAllianceMember = null;

    public bool $showEndWarModal = false;
    public ?int $selectedWarId = null;

    public $warsPage = 1;
    public $perPage = 10;
        
    public function mount()
    {
        $user = Auth::user();
        $this->alliance = $user->alliance;
        $this->userAllianceMember = $user->allianceMember;
        $this->dispatch('setAllianceTab', tab: 'wars');
    }

    public function confirmEndWar(int $warId): void
    {
        $this->selectedWarId = $warId;
        $this->showEndWarModal = true;
    }

    public function performEndWar(): void
    {
        $warId = $this->selectedWarId;
        $this->dismissModals();

        if (!$warId) {
            return;
        }

        $war = AllianceWar::with(['attackerAlliance', 'defenderAlliance'])
            ->find($warId);

        if (!$war) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Guerre introuvable.'
            ]);
            return;
        }

        $user = Auth::user();
        if (!$war->canBeEndedBy($user) || !$war->isActive()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous ne pouvez pas terminer cette guerre.'
            ]);
            return;
        }

        $ended = $war->end($user);
        if ($ended) {
            $this->dispatch('toast:success', [
                'title' => 'Succès!',
                'text' => 'La guerre a été terminée.'
            ]);
        } else {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Impossible de terminer la guerre.'
            ]);
        }
    }

    public function dismissModals(): void
    {
        $this->showEndWarModal = false;

        $this->selectedWarId = null;
    }    

    public function render()
    {
        $data = [
            'availablePermissions' => AllianceRank::PERMISSIONS,
            'rankLevels' => AllianceRank::LEVELS
        ];

        $data['wars'] = $this->alliance->attackerWars()
                ->union($this->alliance->defenderWars())
                ->with(['attackerAlliance', 'defenderAlliance', 'declaredBy'])
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage, ['*'], 'wars', $this->warsPage);

        return view('livewire.game.alliance.wars', $data);
    }
}
