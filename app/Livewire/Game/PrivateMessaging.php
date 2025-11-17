<?php

namespace App\Livewire\Game;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Messaging\PrivateConversation;
use App\Models\Messaging\PrivateMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\PrivateMessageService;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.game')]
class PrivateMessaging extends Component
{
    use LogsUserActions;
    public $conversations = [];
    public $selectedConversation = null;
    public $messages = [];
    public $newMessage = '';
    public $showNewConversation = false;
    public $selectedParticipants = [];
    public $conversationTitle = '';
    public $conversationType = 'player';
    public $searchUsers = '';
    public $availableUsers = [];
    public $messageTypeFilter = 'all'; // Nouveau filtre pour les types de messages
    public $totalMessages = 0;
    public $unreadMessages = 0;
    public $unreadByType = [];
    public $showAllianceBroadcast = false;
    public $allianceBroadcastTitle = '';
    public $allianceBroadcastMessage = '';
    
    public function mount()
    {
        $this->loadConversations();
        $this->loadAvailableUsers();
        $this->loadMessageStats();
    }

    public function loadConversations()
    {
        $query = PrivateConversation::forUser(Auth::user())
            ->with(['lastMessage.user', 'participants']);
            
        // Filtrer par type si un filtre est sélectionné
        if ($this->messageTypeFilter !== 'all') {
            $query->where('type', $this->messageTypeFilter);
        }
        
        $this->conversations = $query->orderBy('last_message_at', 'desc')->get();
        $this->loadMessageStats();
    }

    public function loadMessageStats()
    {
        $conversationIds = PrivateConversation::forUser(Auth::user())->pluck('id');
        
        // Compter le nombre total de messages dans les conversations de l'utilisateur
        $this->totalMessages = PrivateMessage::whereIn('conversation_id', $conversationIds)->count();
        
        // Compter le nombre de messages non lus basé sur last_read_at dans conversation_participants
        $this->unreadMessages = PrivateMessage::whereIn('conversation_id', $conversationIds)
            ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('conversation_participants')
                    ->whereColumn('conversation_participants.conversation_id', 'private_messages.conversation_id')
                    ->where('conversation_participants.user_id', Auth::id())
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('conversation_participants.last_read_at')
                            ->orWhereColumn('private_messages.created_at', '>', 'conversation_participants.last_read_at');
                    });
            })
            ->count();
            
        // Compter les messages non lus par type de conversation basé sur last_read_at
        $this->unreadByType = PrivateMessage::whereIn('conversation_id', $conversationIds)
            ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('conversation_participants')
                    ->whereColumn('conversation_participants.conversation_id', 'private_messages.conversation_id')
                    ->where('conversation_participants.user_id', Auth::id())
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('conversation_participants.last_read_at')
                            ->orWhereColumn('private_messages.created_at', '>', 'conversation_participants.last_read_at');
                    });
            })
            ->join('private_conversations', 'private_messages.conversation_id', '=', 'private_conversations.id')
            ->selectRaw('private_conversations.type, COUNT(*) as count')
            ->groupBy('private_conversations.type')
            ->pluck('count', 'type')
            ->toArray();
    }

    public function loadAvailableUsers()
    {
        $query = User::where('id', '!=', Auth::id());
        
        if ($this->searchUsers) {
            $query->where('name', 'like', '%' . $this->searchUsers . '%');
        }
        
        $this->availableUsers = $query->limit(10)->get();
    }

    public function updatedSearchUsers()
    {
        $this->loadAvailableUsers();
    }

    public function selectConversation($conversationId)
    {
        $this->selectedConversation = PrivateConversation::with(['messages.user', 'participants'])
            ->find($conversationId);
        
        if ($this->selectedConversation) {
            $this->messages = $this->selectedConversation->messages->sortByDesc('created_at')->values();
            $this->markMessagesAsRead();
        }
        
        $this->showNewConversation = false;
    }

    public function markMessagesAsRead()
    {
        if ($this->selectedConversation) {
            $this->selectedConversation->messages()
                ->where('user_id', '!=', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => Carbon::now()]);

            $participant = $this->selectedConversation->participants()
                ->where('user_id', Auth::id())
                ->first();
            
            if ($participant) {
                $participant->pivot->update(['last_read_at' => Carbon::now()]);
            }
            
            // Mettre à jour les statistiques après avoir marqué les messages comme lus
            $this->loadMessageStats();
        }
    }

    public function sendMessage()
    {
        if (!$this->selectedConversation || !trim($this->newMessage)) {
            return;
        }

        if (!$this->selectedConversation->canReply()) {
            $this->addError('newMessage', 'Vous ne pouvez pas répondre à ce type de conversation.');
            return;
        }

        // Validation: empêcher les messages vides (HTML-only)
        $this->validate([
            'newMessage' => ['required', 'string', function($attribute, $value, $fail) {
                $text = trim(strip_tags($value));
                if ($text === '') {
                    $fail('Le message ne peut pas être vide.');
                }
            }],
        ]);

        $message = PrivateMessage::create([
            'conversation_id' => $this->selectedConversation->id,
            'user_id' => Auth::id(),
            'message' => trim($this->newMessage),
            'is_system_message' => false
        ]);

        // Log private message sending
        $participants = $this->selectedConversation->participants
            ->where('id', '!=', Auth::id())
            ->pluck('name')
            ->toArray();
        
        $this->logPrivateMessageSent(
            $this->selectedConversation->title,
            count($participants),
            $participants
        );

        $this->selectedConversation->updateLastMessageTime();
        $this->newMessage = '';
        $this->messages = $this->selectedConversation->fresh()->messages->sortByDesc('created_at')->values();
        $this->loadConversations();
        $this->loadMessageStats();

        $this->dispatch('message-sent');
    }

    public function showNewConversationForm($type = null)
    {
        $this->showNewConversation = true;
        $this->selectedConversation = null;
        $this->selectedParticipants = [];
        $this->conversationTitle = '';
        
        // Set default type based on system message types for automated scripts
        $systemTypes = ['attack', 'spy', 'colonize', 'return', 'send', 'extract', 'basement', 'explore'];
        if ($type && in_array($type, $systemTypes)) {
            $this->conversationType = $type;
        } else {
            $this->conversationType = 'player';
        }
    }

    public function toggleParticipant($userId)
    {
        if (in_array($userId, $this->selectedParticipants)) {
            $this->selectedParticipants = array_diff($this->selectedParticipants, [$userId]);
        } else {
            $this->selectedParticipants[] = $userId;
        }
    }

    public function createConversation()
    {
        $this->validate([
            'conversationTitle' => 'required|string|max:255',
            'selectedParticipants' => 'required|array|min:1',
            'conversationType' => 'required|in:player,alliance,system,attack,spy,colonize,return,send,extract,basement,explore'
        ]);

        $conversation = PrivateConversation::create([
            'title' => $this->conversationTitle,
            'type' => $this->conversationType,
            'created_by' => Auth::id(),
            'last_message_at' => Carbon::now(),
            'is_active' => true
        ]);

        // Ajouter le créateur
        $conversation->addParticipant(Auth::user());

        // Ajouter les participants sélectionnés
        foreach ($this->selectedParticipants as $userId) {
            $user = User::find($userId);
            if ($user) {
                $conversation->addParticipant($user);
            }
        }

        // Message système de création
        PrivateMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => null,
            'message' => 'Conversation créée par ' . Auth::user()->name,
            'is_system_message' => true
        ]);

        // Log conversation creation
        $participantNames = User::whereIn('id', $this->selectedParticipants)
            ->pluck('name')
            ->toArray();
        
        $this->logPrivateMessageSent(
            $this->conversationTitle,
            count($this->selectedParticipants),
            $participantNames
        );

        $this->loadConversations();
        $this->selectConversation($conversation->id);
        $this->showNewConversation = false;
    }

    // Method for automated scripts to create system conversations
    public function createSystemConversation($type, $title, $participants, $message = null)
    {
        $systemTypes = ['attack', 'spy', 'colonize', 'return', 'send', 'extract', 'basement', 'explore'];
        
        if (!in_array($type, $systemTypes)) {
            throw new \InvalidArgumentException('Invalid system conversation type');
        }

        $conversation = PrivateConversation::create([
            'title' => $title,
            'type' => $type,
            'created_by' => Auth::id(),
            'last_message_at' => Carbon::now(),
            'is_active' => true
        ]);

        // Add participants
        foreach ($participants as $userId) {
            $user = User::find($userId);
            if ($user) {
                $conversation->addParticipant($user);
            }
        }

        // Add initial system message if provided
        if ($message) {
            PrivateMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
                'message' => $message,
                'is_system_message' => true
            ]);
        }

        return $conversation;
    }

    public function leaveConversation()
    {
        if ($this->selectedConversation) {
            $this->selectedConversation->removeParticipant(Auth::user());
            
            PrivateMessage::create([
                'conversation_id' => $this->selectedConversation->id,
                'user_id' => null,
                'message' => Auth::user()->name . ' a quitté la conversation',
                'is_system_message' => true
            ]);

            $this->selectedConversation = null;
            $this->messages = [];
            $this->loadConversations();
        }
    }

    public function updatedMessageTypeFilter()
    {
        $this->loadConversations();
        // Réinitialiser la conversation sélectionnée si elle ne correspond plus au filtre
        if ($this->selectedConversation && $this->messageTypeFilter !== 'all' && $this->selectedConversation->type !== $this->messageTypeFilter) {
            $this->selectedConversation = null;
            $this->messages = [];
        }
        $this->loadMessageStats();
    }

    public function showAllianceBroadcastForm()
    {
        // Vérifier que l'utilisateur fait partie d'une alliance
        if (!Auth::user()->alliance_id) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Vous devez faire partie d\'une alliance pour envoyer un message collectif.'
            ]);
            return;
        }
        
        $this->showAllianceBroadcast = true;
        $this->showNewConversation = false;
        $this->selectedConversation = null;
        $this->allianceBroadcastTitle = '';
        $this->allianceBroadcastMessage = '';
    }

    public function createAllianceBroadcast()
    {
        $this->validate([
            'allianceBroadcastTitle' => 'required|string|max:255',
            'allianceBroadcastMessage' => ['required','string','max:2000', function($attribute, $value, $fail) {
                $text = trim(strip_tags($value));
                if ($text === '') {
                    $fail('Le message ne peut pas être vide.');
                }
            }],
        ]);

        try {
            $privateMessageService = app(PrivateMessageService::class);
            $conversation = $privateMessageService->createAllianceBroadcast(
                Auth::user(),
                $this->allianceBroadcastTitle,
                $this->allianceBroadcastMessage
            );

            $this->dispatch('swal:success', [
                'title' => 'Message envoyé !',
                'text' => 'Le message collectif a été envoyé à tous les membres de votre alliance.'
            ]);

            $this->loadConversations();
            $this->selectConversation($conversation->id);
            $this->showAllianceBroadcast = false;
            
        } catch (\Exception $e) {
            $this->dispatch('swal:error', [
                'title' => 'Erreur',
                'text' => 'Erreur lors de l\'envoi du message: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelAllianceBroadcast()
    {
        $this->showAllianceBroadcast = false;
        $this->allianceBroadcastTitle = '';
        $this->allianceBroadcastMessage = '';
    }

    #[On('refresh-conversations')]
    public function refreshConversations()
    {
        $this->loadConversations();
        
        if ($this->selectedConversation) {
            $this->messages = $this->selectedConversation->fresh()->messages->sortByDesc('created_at')->values();
        }
    }

    public function render()
    {
        return view('livewire.game.private-messaging')
            ->layout('layouts.game', ['page' => 'private']);
    }
}
