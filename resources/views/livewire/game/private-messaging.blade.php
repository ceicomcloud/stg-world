<div page="private-messaging">
    <div class="messaging-container">
        <!-- Sidebar des conversations -->
        <div class="conversations-sidebar">
            <div class="sidebar-header">
                <h3>Messagerie Privée</h3>
                <div class="header-buttons">
                    <button wire:click="showNewConversationForm" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nouvelle
                    </button>
                    @if(Auth::user()->alliance_id)
                        <button wire:click="showAllianceBroadcastForm" class="btn btn-success btn-sm">
                            <i class="fas fa-bullhorn"></i> Alliance
                        </button>
                    @endif
                </div>
            </div>

            <!-- Statistiques des messages -->
            <div class="message-stats">
                <div class="stats-container">
                    <div class="stat-item">
                        <i class="fas fa-envelope"></i>
                        <span class="stat-label">Total :</span>
                        <span class="stat-value">{{ $totalMessages }}</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-envelope-open"></i>
                        <span class="stat-label">Non lus :</span>
                        <span class="stat-value unread">{{ $unreadMessages }}</span>
                    </div>
                </div>
                
                @if($unreadMessages > 0 && count($unreadByType) > 0)
                    <div class="unread-details">
                        <div class="details-header">
                            <i class="fas fa-list"></i>
                            <span>Détails par type :</span>
                        </div>
                        <div class="details-container">
                            @foreach($unreadByType as $type => $count)
                                @php
                                    $conversation = new \App\Models\Messaging\PrivateConversation(['type' => $type]);
                                @endphp
                                <div class="detail-item">
                                    <span class="type-badge type-{{ $type }}">{{ $conversation->getTypeLabel() }}</span>
                                    <span class="count-badge">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Filtre par type de message -->
            <div class="message-type-filter">
                <label for="messageTypeFilter">Filtrer par type :</label>
                <select wire:model.live="messageTypeFilter" id="messageTypeFilter" class="form-control">
                    <option value="all">Tous les messages</option>
                    <option value="player">Messages joueurs</option>
                    <option value="alliance">Messages alliance</option>
                    <option value="system">Messages système</option>
                    <option value="attack">Rapports d'attaque</option>
                    <option value="spy">Rapports d'espionnage</option>
                    <option value="colonize">Rapports de colonisation</option>
                    <option value="return">Rapports de retour</option>
                    <option value="send">Rapports d'envoi</option>
                    <option value="explore">Rapports d'exploration</option>
                </select>
            </div>

            <div class="conversations-list">
                @forelse($conversations as $conversation)
                    <div class="conversation-item {{ $selectedConversation && $selectedConversation->id == $conversation->id ? 'active' : '' }}"
                         wire:click="selectConversation({{ $conversation->id }})">
                        <div class="conversation-header">
                            <span class="conversation-title">{{ $conversation->title }}</span>
                            <span class="conversation-type type-{{ $conversation->type }}">{{ $conversation->getTypeLabel() }}</span>
                        </div>
                        <div class="conversation-preview">
                            @if($conversation->lastMessage)
                                <span class="last-message">
                                    {{ $conversation->lastMessage->user ? $conversation->lastMessage->user->name . ': ' : '' }}
                                </span>
                                <span class="last-time">{{ $conversation->lastMessage->created_at->diffForHumans() }}</span>
                            @else
                                <span class="no-messages">Aucun message</span>
                            @endif
                        </div>
                        <div class="participants-count">
                            <i class="fas fa-users"></i> {{ $conversation->participants->count() }}
                        </div>
                    </div>
                @empty
                    <div class="no-conversations">
                        <p>Aucune conversation</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Zone de conversation -->
        <div class="conversation-area">
            @if($showNewConversation)
                <!-- Formulaire nouvelle conversation -->
                <div class="new-conversation-form">
                    <div class="form-header">
                        <h4>Nouvelle Conversation</h4>
                        <button wire:click="$set('showNewConversation', false)" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="createConversation">
                        <div class="form-group">
                            <label for="conversationTitle">Titre de la conversation</label>
                            <input type="text" id="conversationTitle" wire:model="conversationTitle" class="form-control" required>
                            @error('conversationTitle') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <input type="hidden" wire:model="conversationType" value="player">
                        </div>

                        <div class="form-group">
                            <label>Participants</label>
                            <input type="text" wire:model.live="searchUsers" placeholder="Rechercher des utilisateurs..." class="form-control mb-2">
                            
                            <div class="users-list">
                                @foreach($availableUsers as $user)
                                    <div class="user-item {{ in_array($user->id, $selectedParticipants) ? 'selected' : '' }}"
                                         wire:click="toggleParticipant({{ $user->id }})">
                                        <span class="user-name">{{ $user->name }}</span>
                                        <span class="user-role">
                                            <i class="{{ $user->getRoleIcon() }}"></i>
                                            {{ $user->getRoleName() }}
                                        </span>
                                        @if(in_array($user->id, $selectedParticipants))
                                            <i class="fas fa-check"></i>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @error('selectedParticipants') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Créer la conversation</button>
                            <button type="button" wire:click="$set('showNewConversation', false)" class="btn btn-secondary">Annuler</button>
                        </div>
                    </form>
                </div>
            @elseif($showAllianceBroadcast)
                <!-- Formulaire message collectif alliance -->
                <div class="alliance-broadcast-form">
                    <div class="form-header">
                        <h4><i class="fas fa-bullhorn"></i> Message Collectif Alliance</h4>
                        <button wire:click="cancelAllianceBroadcast" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="createAllianceBroadcast">
                        <div class="form-group">
                            <label for="allianceBroadcastTitle">Titre du message</label>
                            <input type="text" id="allianceBroadcastTitle" wire:model="allianceBroadcastTitle" class="form-control" required>
                            @error('allianceBroadcastTitle') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="allianceBroadcastMessage">Message</label>
                            <textarea id="allianceBroadcastMessage" wire:model="allianceBroadcastMessage" class="form-control" rows="6" required placeholder="Votre message à tous les membres de l'alliance..."></textarea>
                            @error('allianceBroadcastMessage') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="alliance-info">
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <span>Ce message sera envoyé à tous les membres de votre alliance</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Alliance : {{ Auth::user()->alliance ? Auth::user()->alliance->name : 'Aucune' }}</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Envoyer à l'Alliance
                            </button>
                            <button type="button" wire:click="cancelAllianceBroadcast" class="btn btn-secondary">Annuler</button>
                        </div>
                    </form>
                </div>
            @elseif($selectedConversation)
                <!-- Conversation sélectionnée -->
                <div class="conversation-header">
                    <div class="conversation-info">
                        <h4>{{ $selectedConversation->title }}</h4>
                        <span class="conversation-type type-{{ $selectedConversation->type }}">{{ $selectedConversation->getTypeLabel() }}</span>
                    </div>
                    <div class="conversation-actions">
                        <div class="participants-info">
                            <i class="fas fa-users"></i>
                            @foreach($selectedConversation->participants as $participant)
                                <span class="participant">{{ $participant->name }}</span>{{ !$loop->last ? ',' : '' }}
                            @endforeach
                        </div>
                        <button wire:click="leaveConversation" class="btn btn-danger btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Quitter
                        </button>
                    </div>
                </div>

                <!-- Messages -->
                <div class="messages-container" id="messages-container">
                    @foreach($messages as $message)
                        <div class="message {{ $message->is_system_message ? 'system-message' : ($message->user_id == auth()->id() ? 'own-message' : 'other-message') }}">
                            @if($message->is_system_message)
                                <div class="system-content">
                                    <i class="fas fa-info-circle"></i>
                                    {!! $message->message !!}
                                    <span class="message-time">{{ $message->created_at->format('H:i') }}</span>
                                </div>
                            @else
                                <div class="message-header">
                                    <span class="message-author">{{ $message->user->name }}</span>
                                    <span class="message-time">{{ $message->created_at->format('H:i') }}</span>
                                    @if($message->read_at)
                                        <i class="fas fa-check-double read-indicator"></i>
                                    @endif
                                </div>
                                <div class="message-content">
                                    {{ $message->message }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Zone de saisie -->
                @if($selectedConversation->canReply())
                    <div class="message-input-area">
                        <form wire:submit.prevent="sendMessage">
                            <x-input.tinymce wire:model.live="newMessage" placeholder="Tapez votre message..."></x-input.tinymce>
                            @error('newMessage') <span class="error">{{ $message }}</span> @enderror
                            
                            <button type="submit" class="btn btn-primary mt-2">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="no-reply-notice">
                        <i class="fas fa-lock"></i>
                        Cette conversation ne permet pas de réponse.
                    </div>
                @endif
            @else
                <!-- Aucune conversation sélectionnée -->
                <div class="no-conversation-selected">
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h4>Sélectionnez une conversation</h4>
                        <p>Choisissez une conversation dans la liste ou créez-en une nouvelle.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('message-sent', () => {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    });

    // Auto-scroll to bottom when messages load
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
