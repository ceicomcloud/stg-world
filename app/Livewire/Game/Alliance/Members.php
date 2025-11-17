<?php

namespace App\Livewire\Game\Alliance;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Traits\LogsUserActions;
use App\Models\Alliance\AllianceRank;
use Illuminate\Support\Facades\Auth;

#[Layout('livewire.layouts.alliance-layout')]
class Members extends Component
{
    use LogsUserActions;

    public $alliance = null;
    public $userAllianceMember = null;

    public $membersPage = 1;
    public $perPage = 10;

    public function mount()
    {
        $user = Auth::user();
        $this->alliance = $user->alliance;
        $this->userAllianceMember = $user->allianceMember;
        $this->dispatch('setAllianceTab', tab: 'members');
    }

    public function render()
    {
        $data = [
            'availablePermissions' => AllianceRank::PERMISSIONS,
            'rankLevels' => AllianceRank::LEVELS
        ];

        $data['members'] = $this->alliance->members()->with(['user', 'rank'])->paginate($this->perPage, ['*'], 'members', $this->membersPage);

        return view('livewire.game.alliance.members', $data);
    }
}
