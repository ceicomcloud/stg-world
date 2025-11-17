<?php

namespace App\Livewire\Game\Alliance;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Traits\LogsUserActions;
use App\Models\Alliance\Alliance;
use App\Models\Alliance\AllianceApplication;
use App\Models\Alliance\AllianceMember;
use Illuminate\Support\Facades\Auth;

#[Layout('livewire.layouts.alliance-layout')]
class Join extends Component
{
    use LogsUserActions;

    public $searchQuery = '';
    public $searchResults = [];
    public $applicationMessage = '';

    public function mount(): void
    {
        $this->dispatch('setAllianceTab', tab: 'search');
    }
    
    public function updatedSearchQuery()
    {
        if (empty($this->searchQuery)) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Alliance::where('name', 'like', '%' . $this->searchQuery . '%')
            ->orWhere('tag', 'like', '%' . $this->searchQuery . '%')
            ->limit(10)
            ->get();
    }

    public function applyToAlliance($allianceId)
    {
        $user = Auth::user();
        
        if ($user->isInAlliance()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Vous êtes déjà dans une alliance.'
            ]);
            return;
        }

        $alliance = Alliance::find($allianceId);
        if (!$alliance) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Alliance introuvable.'
            ]);
            return;
        }

        if (!$alliance->canAcceptNewMembers()) {
            $this->dispatch('toast:error', [
                'title' => 'Erreur!',
                'text' => 'Cette alliance est complète.'
            ]);
            return;
        }

        $existingApplication = AllianceApplication::where('alliance_id', $allianceId)
            ->where('user_id', $user->id)
            ->first();

        if ($existingApplication) {
            if ($existingApplication->isPending()) {
                $this->dispatch('toast:error', [
                    'title' => 'Erreur!',
                    'text' => 'Vous avez déjà une candidature en attente pour cette alliance.'
                ]);
                return;
            }

            if ($existingApplication->isAccepted()) {
                $isStillMember = AllianceMember::where('alliance_id', $allianceId)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($isStillMember) {
                    $this->dispatch('toast:error', [
                        'title' => 'Erreur!',
                        'text' => 'Vous êtes déjà membre de cette alliance.'
                    ]);
                    return;
                }

                $existingApplication->update([
                    'message' => $this->applicationMessage,
                    'status' => AllianceApplication::STATUS_PENDING,
                    'reviewed_by' => null,
                    'reviewed_at' => null
                ]);
            } else {
                $existingApplication->update([
                    'message' => $this->applicationMessage,
                    'status' => AllianceApplication::STATUS_PENDING,
                    'reviewed_by' => null,
                    'reviewed_at' => null
                ]);
            }
        } else {
            AllianceApplication::create([
                'alliance_id' => $allianceId,
                'user_id' => $user->id,
                'message' => $this->applicationMessage,
                'status' => AllianceApplication::STATUS_PENDING
            ]);
        }

        $alliance = Alliance::find($allianceId);
        $this->logAllianceJoin($alliance->name, $alliance->id);

        $this->reset('applicationMessage');
        $this->dispatch('toast:success', [
            'title' => 'Succès!',
            'text' => 'Candidature envoyée avec succès!'
        ]);
    }

    public function render()
    {
        return view('livewire.game.alliance.join');
    }
}
