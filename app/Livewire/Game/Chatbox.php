<?php

namespace App\Livewire\Game;

use App\Models\Other\Chatbox as ChatboxModel;
use App\Models\User;
use App\Models\User\UserSanction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use App\Traits\LogsUserActions;
use App\Services\DailyQuestService;
use Illuminate\Support\Facades\Schema;
use App\Models\Other\ChatboxReadState;

#[Layout('components.layouts.game')]
class Chatbox extends Component
{
    use LogsUserActions;
    public $message = '';
    public $channel = 'general';
    public $messages = [];
    public $maxMessages = 50;
    public $messageFormat = [];

    public function mount()
    {
        $this->loadMessages();
        $this->markChannelAsSeen($this->channel);
    }

    public function loadMessages()
    {
        $query = ChatboxModel::with('user')
            ->channel($this->channel)
            ->recent($this->maxMessages);
            
        // Si c'est le canal alliance, filtrer par alliance de l'utilisateur
        if ($this->channel === 'alliance' && Auth::check() && Auth::user()->alliance_id) {
            $query->whereHas('user', function($q) {
                $q->where('alliance_id', Auth::user()->alliance_id);
            });
        }
        
        // Garder l'ordre décroissant (plus récent en premier)
        $this->messages = $query->get()
            ->values()
            ->toArray();

        // Marquer comme vu après chargement
        $this->markChannelAsSeen($this->channel);
    }

    public function sendMessage()
    {
        if (!Auth::check()) {
            return;
        }
        
        $user = Auth::user();
        
        // Vérifier si l'utilisateur est banni (sauf pour les modérateurs)
        if ($user->isBanned() && $user->isPlayer()) {
            $ban = $user->getActiveBan();
            $message = 'Vous êtes banni du chat.';
            if ($ban->expires_at) {
                $message .= ' Expiration: ' . $ban->expires_at->format('d/m/Y H:i');
            }
            $this->addError('message', $message);
            return;
        }
        
        // Vérifier si l'utilisateur est muté (sauf pour les modérateurs)
        if ($user->isMuted() && $user->isPlayer()) {
            $mute = $user->getActiveMute();
            $message = 'Vous êtes muté.';
            if ($mute->expires_at) {
                $message .= ' Expiration: ' . $mute->expires_at->format('d/m/Y H:i');
            }
            $this->addError('message', $message);
            return;
        }
        
        // Vérifier si l'utilisateur peut écrire dans le canal alliance
        if ($this->channel === 'alliance' && !$user->alliance_id) {
            $this->addError('message', 'Vous devez être membre d\'une alliance pour écrire dans ce canal.');
            return;
        }

        $this->validate([
            'message' => 'required|string|max:500|min:1',
        ]);

        $messageContent = trim($this->message);
        
        // Vérifier si c'est une commande
        if (str_starts_with($messageContent, '/')) {
            $this->handleCommand($messageContent);
            return;
        }

        // Appliquer le formatage au message
        $formattedMessage = $this->applyMessageFormatting($messageContent);
        
        $chatMessage = ChatboxModel::create([
            'user_id' => Auth::id(),
            'message' => $formattedMessage,
            'channel' => $this->channel,
            'is_system_message' => false,
        ]);

        // Charger la relation user pour le nouveau message
        $chatMessage->load('user');

        // Incrémenter la quête quotidienne d'envoi de messages
        app(DailyQuestService::class)->incrementProgress($user, 'chat_send_messages');

        $this->message = '';
        $this->messageFormat = [];
        $this->loadMessages();
        
        // Logger le message de chat
        $this->logAction(
            'chat_message_sent',
            'chat',
            'Envoi d\'un message dans le chat',
            [
                'channel' => $this->channel,
                'message_length' => strlen($messageContent),
                'formatted' => !empty($this->messageFormat)
            ]
        );
        
        // Émettre un événement pour mettre à jour les autres utilisateurs
        $this->dispatch('message-sent', [
            'channel' => $this->channel,
            'message' => $chatMessage->toArray()
        ]);
    }

    private function applyMessageFormatting($message)
    {
        if (empty($this->messageFormat)) {
            return $message;
        }
        
        $formatted = $message;
        
        // Appliquer le gras
        if (isset($this->messageFormat['bold']) && $this->messageFormat['bold']) {
            $formatted = '<strong>' . $formatted . '</strong>';
        }
        
        // Appliquer l'italique
        if (isset($this->messageFormat['italic']) && $this->messageFormat['italic']) {
            $formatted = '<em>' . $formatted . '</em>';
        }
        
        // Appliquer la couleur
        if (isset($this->messageFormat['color']) && $this->messageFormat['color']) {
            $formatted = '<span style="color: ' . $this->messageFormat['color'] . ';">' . $formatted . '</span>';
        }
        
        return $formatted;
    }
    
    private function handleCommand($command)
    {
        $user = Auth::user();
        $parts = explode(' ', $command, 3);
        $cmd = strtolower($parts[0]);
        
        switch ($cmd) {
            case '/ban':
                $this->handleBanCommand($parts, $user);
                break;
            case '/unban':
                $this->handleUnbanCommand($parts, $user);
                break;
            case '/mute':
                $this->handleMuteCommand($parts, $user);
                break;
            case '/unmute':
                $this->handleUnmuteCommand($parts, $user);
                break;
            case '/annonce':
                $this->handleAnnonceCommand($parts, $user);
                break;
            default:
                $this->addError('message', 'Commande inconnue: ' . $cmd);
        }
        
        $this->message = '';
    }
    
    private function handleBanCommand($parts, $user)
    {
        if (!$user->canBanUsers()) {
            $this->addError('message', 'Vous n\'avez pas les permissions pour bannir des utilisateurs.');
            return;
        }
        
        if (count($parts) < 2) {
            $this->addError('message', 'Usage: /ban <username> [raison]');
            return;
        }
        
        $targetUser = User::where('name', $parts[1])->first();
        if (!$targetUser) {
            $this->addError('message', 'Utilisateur introuvable: ' . $parts[1]);
            return;
        }
        
        if ($targetUser->hasModeratorRights() && !$user->hasAdminRights()) {
            $this->addError('message', 'Vous ne pouvez pas bannir un modérateur.');
            return;
        }
        
        $reason = $parts[2] ?? 'Aucune raison spécifiée';
        
        UserSanction::create([
            'user_id' => $targetUser->id,
            'sanctioned_by' => $user->id,
            'type' => 'ban',
            'reason' => $reason,
            'expires_at' => null, // Ban permanent
            'is_active' => true
        ]);
        
        $this->sendSystemMessage($targetUser->name . ' a été banni par ' . $user->name . '. Raison: ' . $reason);
    }
    
    private function handleMuteCommand($parts, $user)
    {
        if (!$user->canMuteUsers()) {
            $this->addError('message', 'Vous n\'avez pas les permissions pour muter des utilisateurs.');
            return;
        }
        
        if (count($parts) < 2) {
            $this->addError('message', 'Usage: /mute <username> [raison]');
            return;
        }
        
        $targetUser = User::where('name', $parts[1])->first();
        if (!$targetUser) {
            $this->addError('message', 'Utilisateur introuvable: ' . $parts[1]);
            return;
        }
        
        if ($targetUser->hasModeratorRights() && !$user->hasAdminRights()) {
            $this->addError('message', 'Vous ne pouvez pas muter un modérateur.');
            return;
        }
        
        $reason = $parts[2] ?? 'Aucune raison spécifiée';
        
        UserSanction::create([
            'user_id' => $targetUser->id,
            'sanctioned_by' => $user->id,
            'type' => 'mute',
            'reason' => $reason,
            'expires_at' => Carbon::now()->addHours(24), // Mute 24h par défaut
            'is_active' => true
        ]);
        
        $this->sendSystemMessage($targetUser->name . ' a été muté par ' . $user->name . ' pour 24h. Raison: ' . $reason);
    }
    
    private function handleUnbanCommand($parts, $user)
    {
        if (!$user->canBanUsers()) {
            $this->addError('message', 'Vous n\'avez pas les permissions pour débannir des utilisateurs.');
            return;
        }

        if (count($parts) < 2) {
            $this->addError('message', 'Usage: /unban <username>');
            return;
        }

        $targetUser = User::where('name', $parts[1])->first();
        if (!$targetUser) {
            $this->addError('message', 'Utilisateur introuvable: ' . $parts[1]);
            return;
        }

        $activeBan = $targetUser->getActiveBan();
        if (!$activeBan) {
            $this->addError('message', $targetUser->name . ' n\'est pas actuellement banni.');
            return;
        }

        $activeBan->update(['is_active' => false]);

        $this->sendSystemMessage($targetUser->name . ' a été débanni par ' . $user->name . '.');
    }

    private function handleUnmuteCommand($parts, $user)
    {
        if (!$user->canMuteUsers()) {
            $this->addError('message', 'Vous n\'avez pas les permissions pour démuter des utilisateurs.');
            return;
        }

        if (count($parts) < 2) {
            $this->addError('message', 'Usage: /unmute <username>');
            return;
        }

        $targetUser = User::where('name', $parts[1])->first();
        if (!$targetUser) {
            $this->addError('message', 'Utilisateur introuvable: ' . $parts[1]);
            return;
        }

        $activeMute = $targetUser->getActiveMute();
        if (!$activeMute) {
            $this->addError('message', $targetUser->name . ' n\'est pas actuellement muté.');
            return;
        }

        $activeMute->update(['is_active' => false]);

        $this->sendSystemMessage($targetUser->name . ' a été démuté par ' . $user->name . '.');
    }

    private function handleAnnonceCommand($parts, $user)
    {
        if (!$user->canSendAnnouncements()) {
            $this->addError('message', 'Vous n\'avez pas les permissions pour envoyer des annonces.');
            return;
        }

        if (count($parts) < 2) {
            $this->addError('message', 'Usage: /annonce <message>');
            return;
        }

        $announcement = implode(' ', array_slice($parts, 1));

        $this->sendSystemMessage('[ANNONCE] ' . $announcement, true);
    }
    
    private function sendSystemMessage($message, $isAnnouncement = false)
    {
        $chatMessage = ChatboxModel::create([
            'user_id' => null,
            'message' => $message,
            'channel' => $this->channel,
            'is_system_message' => true,
        ]);
        
        $this->loadMessages();
        
        // Si c'est une annonce, l'envoyer sur tous les canaux
        if ($isAnnouncement) {
            $channels = ['general', 'alliance'];
            foreach ($channels as $channel) {
                if ($channel !== $this->channel) {
                    ChatboxModel::create([
                        'user_id' => null,
                        'message' => $message,
                        'channel' => $channel,
                        'is_system_message' => true,
                    ]);
                }
            }
        }
        
        $this->dispatch('message-sent', [
            'channel' => $this->channel,
            'message' => $chatMessage->toArray()
        ]);
    }
    
    #[On('message-received')]
    public function messageReceived($data)
    {
        if ($data['channel'] === $this->channel) {
            $this->loadMessages();
        }
    }

    public function switchChannel($newChannel)
    {
        $this->channel = $newChannel;
        $this->loadMessages();
        $this->markChannelAsSeen($newChannel);
    }

    public function render()
    {
        return view('livewire.game.chatbox', [
            'currentUser' => Auth::user()
        ]);
    }

    private function markChannelAsSeen(string $channel): void
    {
        if (!Auth::check()) {
            return;
        }

        // Sécurité: ne pas planter si la table n'existe pas encore
        if (!Schema::hasTable('chatbox_read_states')) {
            return;
        }

        ChatboxReadState::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'channel' => $channel,
            ],
            [
                'last_seen_at' => now(),
            ]
        );
    }
}
