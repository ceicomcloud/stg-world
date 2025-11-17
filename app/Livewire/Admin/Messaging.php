<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Messaging\PrivateConversation;
use App\Models\Messaging\PrivateMessage;
use App\Models\Messaging\ConversationParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsUserActions;

#[Layout('components.layouts.admin')]
class Messaging extends Component
{
    use WithPagination, LogsUserActions;
    
    // Propriétés pour la pagination et le filtrage
    public $perPage = 10;
    public $search = '';
    public $typeFilter = '';
    public $sortField = 'last_message_at';
    public $sortDirection = 'desc';
    
    // Propriétés pour l'envoi de messages système
    public $systemMessageTitle = '';
    public $systemMessageContent = '';
    public $systemMessageType = 'system';
    
    // Propriétés pour l'affichage des conversations et messages
    public $selectedConversation = null;
    public $selectedConversationId = null;
    public $activeTab = 'player'; // Onglet actif par défaut (player, alliance, system)
    
    // Propriétés pour la pagination des messages
    public $messagesPerPage = 20;
    
    // Propriété pour contrôler l'affichage du formulaire de message système
    public $showSystemMessageForm = false;
    
    /**
     * Règles de validation pour les propriétés
     */
    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:100',
            'typeFilter' => 'nullable|string',
            'perPage' => 'integer|min:5|max:100',
            'systemMessageTitle' => 'required|string|min:3|max:100',
            'systemMessageContent' => 'required|string|min:5|max:1000',
            'systemMessageType' => 'required|string|in:system,attack,spy,colonize,return,send,extract,basement',
        ];
    }
    
    /**
     * Méthode pour changer l'onglet actif
     */
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->selectedConversation = null;
        $this->selectedConversationId = null;
        $this->resetPage();
    }
    
    /**
     * Affiche ou masque le formulaire de message système
     */
    public function toggleSystemMessageForm()
    {
        $this->showSystemMessageForm = !$this->showSystemMessageForm;
        if (!$this->showSystemMessageForm) {
            $this->reset(['systemMessageTitle', 'systemMessageContent']);
        }
    }
    
    /**
     * Méthode pour sélectionner une conversation
     */
    public function selectConversation($conversationId)
    {
        $this->selectedConversationId = $conversationId;
        $this->selectedConversation = PrivateConversation::with(['creator', 'participants'])
            ->findOrFail($conversationId);
    }
    
    /**
     * Méthode pour trier les résultats
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }
    
    /**
     * Méthode pour obtenir les conversations filtrées
     */
    public function getConversationsProperty()
    {
        $query = PrivateConversation::query()
            ->with(['creator', 'participants', 'lastMessage'])
            ->orderBy($this->sortField, $this->sortDirection);
        
        // Filtrer par type de conversation
        if ($this->activeTab === 'player') {
            $query->where('type', 'player');
        } elseif ($this->activeTab === 'alliance') {
            $query->where('type', 'alliance');
        } elseif ($this->activeTab === 'system') {
            $query->whereIn('type', ['system', 'attack', 'spy', 'colonize', 'return', 'send', 'extract', 'basement']);
        }
        
        // Filtrer par recherche
        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhereHas('creator', function ($q) use ($search) {
                      $q->where('name', 'like', $search);
                  })
                  ->orWhereHas('participants', function ($q) use ($search) {
                      $q->where('name', 'like', $search);
                  });
            });
        }
        
        // Filtrer par type spécifique (pour l'onglet système)
        if ($this->activeTab === 'system' && !empty($this->typeFilter)) {
            $query->where('type', $this->typeFilter);
        }
        
        return $query->paginate($this->perPage);
    }
    
    /**
     * Méthode pour obtenir les messages d'une conversation
     */
    public function getMessagesProperty()
    {
        if (!$this->selectedConversationId) {
            return collect();
        }
        
        return PrivateMessage::where('conversation_id', $this->selectedConversationId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($this->messagesPerPage);
    }
    
    /**
     * Méthode pour obtenir les types de conversations système disponibles
     */
    public function getSystemTypesProperty()
    {
        return [
            'system' => 'Système',
            'attack' => 'Attaque',
            'spy' => 'Espionnage',
            'colonize' => 'Colonisation',
            'return' => 'Retour',
            'send' => 'Envoi',
            'extract' => 'Extraction',
            'basement' => 'Basement'
        ];
    }
    
    /**
     * Méthode pour obtenir les statistiques des conversations
     */
    public function getStatsProperty()
    {
        return [
            'player' => PrivateConversation::where('type', 'player')->count(),
            'alliance' => PrivateConversation::where('type', 'alliance')->count(),
            'system' => PrivateConversation::whereIn('type', ['system', 'attack', 'spy', 'colonize', 'return', 'send', 'extract', 'basement'])->count(),
            'total' => PrivateConversation::count(),
            'messages' => PrivateMessage::count()
        ];
    }
    
    /**
     * Méthode pour réinitialiser la pagination lors d'une recherche
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Méthode pour réinitialiser la pagination lors d'un changement de filtre
     */
    public function updatedTypeFilter()
    {
        $this->resetPage();
    }
    
    /**
     * Méthode pour réinitialiser la pagination
     */
    public function resetPage()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }
    
    /**
     * Envoie un message système global à tous les joueurs
     */
    public function sendGlobalSystemMessage()
    {
        $this->validate([
            'systemMessageTitle' => 'required|string|min:3|max:100',
            'systemMessageContent' => 'required|string|min:5|max:1000',
            'systemMessageType' => 'required|string|in:system,attack,spy,colonize,return,send,extract,basement',
        ]);
        
        // Récupérer tous les utilisateurs actifs
        $users = \App\Models\User::where('is_active', true)->get();
        
        // Créer une seule conversation système pour tous les utilisateurs
        $conversation = \App\Models\Messaging\PrivateConversation::create([
            'title' => $this->systemMessageTitle,
            'type' => $this->systemMessageType,
            'created_by' => auth()->id(),
            'last_message_at' => now(),
            'is_active' => true
        ]);
        
        // Ajouter tous les utilisateurs comme participants
        foreach ($users as $user) {
            $conversation->addParticipant($user);
        }
        
        // Ajouter le message système
        $conversation->messages()->create([
            'user_id' => auth()->id(),
            'message' => $this->systemMessageContent,
            'is_system_message' => true
        ]);
        
        // Ajouter un log pour l'envoi de message système global
        $this->logAction(
            'Envoi de message système global',
            'admin',
            'Envoi d\'un message système global avec le titre "' . $this->systemMessageTitle . '"',
            [
                'type' => $this->systemMessageType,
                'recipients_count' => $users->count()
            ]
        );
        
        // Réinitialiser les champs du formulaire et masquer le formulaire
        $this->reset(['systemMessageTitle', 'systemMessageContent']);
        $this->showSystemMessageForm = false;
        
        // Afficher un message de succès
        $this->dispatch('admin:toast:success', ['message' => 'Message système envoyé à tous les joueurs avec succès.']);
    }
    
    /**
     * Rendu du composant
     */
    public function render()
    {
        return view('livewire.admin.messaging', [
            'conversations' => $this->conversations,
            'messages' => $this->selectedConversationId ? $this->messages : collect(),
            'stats' => $this->stats,
            'systemTypes' => $this->systemTypes
        ]);
    }
}