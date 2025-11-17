<?php

namespace App\Livewire\Game\Alliance;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Traits\LogsUserActions;
use App\Models\Alliance\Alliance;
use App\Models\Alliance\AllianceRank;
use App\Models\Alliance\AllianceMember;
use Illuminate\Support\Facades\Auth;

#[Layout('livewire.layouts.alliance-layout')]
class Create extends Component
{
    use LogsUserActions;

    public $createAllianceName = '';
    public $createAllianceTag = '';
    public $createAllianceDescription = '';

    public function mount(): void
    {
        $this->dispatch('setAllianceTab', tab: 'create');
    }

    public function createAlliance()
    {
        $this->validate([
            'createAllianceName' => 'required|string|max:50|unique:alliances,name',
            'createAllianceTag' => 'required|string|max:10|unique:alliances,tag',
            'createAllianceDescription' => 'nullable|string|max:1000'
        ]);

        $user = Auth::user();
        
        if ($user->isInAlliance()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous êtes déjà dans une alliance.'
            ]);
            return;
        }

        $alliance = Alliance::create([
            'name' => $this->createAllianceName,
            'tag' => $this->createAllianceTag,
            'external_description' => $this->createAllianceDescription,
            'leader_id' => $user->id
        ]);

        // Créer le rang de leader
        $leaderRank = AllianceRank::create([
            'alliance_id' => $alliance->id,
            'name' => 'Leader',
            'level' => 4,
            'permissions' => array_keys(AllianceRank::PERMISSIONS)
        ]);

        AllianceMember::create([
            'alliance_id' => $alliance->id,
            'user_id' => $user->id,
            'rank_id' => $leaderRank->id,
            'joined_at' => now()
        ]);

        $user->update(['alliance_id' => $alliance->id]);

        $this->reset(['createAllianceName', 'createAllianceTag', 'createAllianceDescription']);
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Alliance créée avec succès!'
        ]);
    }

    public function render()
    {
        return view('livewire.game.alliance.create');
    }
}
