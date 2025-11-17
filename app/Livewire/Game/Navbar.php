<?php

namespace App\Livewire\Game;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Messaging\PrivateMessage;
use App\Models\Planet\PlanetMission;
use App\Models\Other\Queue;
use App\Models\Other\Chatbox;
use App\Models\Other\ChatboxReadState;
use Illuminate\Support\Facades\Schema;
use App\Jobs\ProcessPlanetMissionsJob;
use App\Jobs\ProcessQueueJob;
use Carbon\Carbon;

class Navbar extends Component
{
    public $unreadMessagesCount = 0;
    public $unreadChatCount = 0;
    public $unreadAllianceChatCount = 0;
    
    protected $listeners = [
        'refreshResources' => 'refreshResourcesEvent'
    ];

    public function mount()
    {
        $this->unreadMessagesCount = Auth::user()->getUnreadMessagesCount();
        $this->refreshChatUnreadCount();
    }
    
    /**
     * Événement de mise à jour des ressources
     */
    public function refreshResourcesEvent()
    {
        $user = Auth::user();
        $currentPlanet = $user->getActualPlanet();
        
        if ($currentPlanet) {
            // Dispatch le job pour mettre à jour les ressources
            \App\Jobs\UpdateResourcesJob::dispatch($user->id);
            
            // Émettre un événement pour mettre à jour l'interface
            $this->dispatch('resourcesUpdated');
            $this->dispatch('toast:success', [
                'title' => 'Ressources',
                'text' => 'Les ressources ont été mises à jour.'
            ]);
        }
    }

    public function render()
    {
        // rafraîchir à chaque rendu pour rester à jour
        $this->refreshChatUnreadCount();
        return view('livewire.game.navbar');
    }

    private function refreshChatUnreadCount(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->unreadChatCount = 0;
            $this->unreadAllianceChatCount = 0;
            return;
        }

        if (!Schema::hasTable('chatbox_read_states')) {
            $this->unreadChatCount = 0;
            return;
        }

        // Calculer séparément pour chaque canal
        $generalState = ChatboxReadState::where('user_id', $user->id)
            ->where('channel', 'general')
            ->first();

        $generalQuery = Chatbox::where('channel', 'general')
            ->where('is_system_message', false);

        if ($generalState && $generalState->last_seen_at) {
            $generalQuery->where('created_at', '>', $generalState->last_seen_at);
        } else {
            $generalQuery->where('created_at', '>', now()->subDays(7));
        }

        $this->unreadChatCount = $generalQuery->count();

        // Canal alliance (non affiché pour l'instant, mais utile si on veut une seconde pastille)
        if ($user->alliance_id) {
            $allianceState = ChatboxReadState::where('user_id', $user->id)
                ->where('channel', 'alliance')
                ->first();

            $allianceQuery = Chatbox::where('channel', 'alliance')
                ->where('is_system_message', false);

            if ($allianceState && $allianceState->last_seen_at) {
                $allianceQuery->where('created_at', '>', $allianceState->last_seen_at);
            } else {
                $allianceQuery->where('created_at', '>', now()->subDays(7));
            }

            $this->unreadAllianceChatCount = $allianceQuery->count();
        } else {
            $this->unreadAllianceChatCount = 0;
        }
    }
}