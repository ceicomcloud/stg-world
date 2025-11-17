<div class="admin-messaging">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-envelope"></i> Messagerie</h1>
        <div class="admin-page-actions">
            <!-- Onglets principaux -->
            <button class="admin-tab-button {{ $activeTab === 'player' ? 'active' : '' }}" wire:click="setActiveTab('player')">
                <i class="fas fa-user"></i> Joueurs
            </button>
            <button class="admin-tab-button {{ $activeTab === 'alliance' ? 'active' : '' }}" wire:click="setActiveTab('alliance')">
                <i class="fas fa-users"></i> Alliances
            </button>
            <button class="admin-tab-button {{ $activeTab === 'system' ? 'active' : '' }}" wire:click="setActiveTab('system')">
                <i class="fas fa-robot"></i> Système
            </button>
            <button type="button" class="admin-btn admin-btn-primary ms-auto" wire:click="toggleSystemMessageForm">
                <i class="fas fa-paper-plane"></i> {{ $showSystemMessageForm ? 'Annuler' : 'Envoyer message global' }}
            </button>
        </div>
    </div>

    <div class="admin-content-body">
        <!-- Statistiques des messages -->
        <div class="admin-stats-grid">
            <div class="admin-stat-card">
                <div class="admin-stat-icon primary">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-value">{{ $stats['total'] }}</div>
                    <div class="admin-stat-label">Total des conversations</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon success">
                    <i class="fas fa-user"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-value">{{ $stats['player'] }}</div>
                    <div class="admin-stat-label">Conversations entre joueurs</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon warning">
                    <i class="fas fa-users"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-value">{{ $stats['alliance'] }}</div>
                    <div class="admin-stat-label">Conversations d'alliance</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon info">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-value">{{ $stats['system'] }}</div>
                    <div class="admin-stat-label">Messages système</div>
                </div>
            </div>
        </div>

        @if ($showSystemMessageForm)
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2 class="admin-card-title"><i class="fas fa-paper-plane"></i> Envoyer un message système global</h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="sendGlobalSystemMessage">
                        <div class="admin-form-group">
                            <label for="title" class="admin-form-label">Titre</label>
                            <input type="text" id="title" wire:model="systemMessageTitle" class="admin-form-input" required>
                            @error('systemMessageTitle') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="type">Type <span class="admin-required">*</span></label>
                            <select id="type" wire:model="systemMessageType" class="admin-select">
                                <option value="">Sélectionner un type</option>
                                @foreach($systemTypes as $type => $label)
                                    <option value="{{ $type }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('systemMessageType') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="description">Contenu</label>
                            <textarea id="description" wire:model="systemMessageContent" rows="3" class="admin-textarea"></textarea>
                            @error('systemMessageContent') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="admin-form-actions">
                            <button type="button" class="admin-btn admin-btn-secondary" wire:click="toggleSystemMessageForm">Annuler</button>
                            <button type="submit" class="admin-btn admin-btn-primary">Envoyer à tous les joueurs</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="admin-messaging-container">
            <!-- Panneau de gauche: Liste des conversations -->
            <div class="admin-messaging-sidebar">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">
                            @if($activeTab === 'player')
                                <i class="fas fa-user"></i> Conversations entre joueurs
                            @elseif($activeTab === 'alliance')
                                <i class="fas fa-users"></i> Conversations d'alliance
                            @elseif($activeTab === 'system')
                                <i class="fas fa-robot"></i> Messages système
                            @endif
                        </h2>
                    </div>
                    <div class="admin-card-body">
                        <!-- Filtres -->
                        <div class="admin-filters">
                            <div class="admin-search-container">
                                <input type="text" class="admin-search-input" placeholder="Rechercher..." wire:model.live.debounce.300ms="search">
                                <i class="fas fa-search admin-search-icon"></i>
                            </div>
                            
                            @if($activeTab === 'system')
                                <div class="admin-filter-group">
                                    <select class="admin-select" wire:model.live="typeFilter">
                                        <option value="">Tous les types</option>
                                        @foreach($systemTypes as $type => $label)
                                            <option value="{{ $type }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            
                            <div class="admin-filter-group">
                                <select class="admin-select" wire:model.live="perPage">
                                    <option value="10">10 par page</option>
                                    <option value="25">25 par page</option>
                                    <option value="50">50 par page</option>
                                    <option value="100">100 par page</option>
                                </select>
                            </div>
                        </div>

                        <!-- Liste des conversations -->
                        <div class="admin-conversation-list">
                            @forelse($conversations as $conversation)
                                <div class="admin-conversation-item {{ $selectedConversationId == $conversation->id ? 'active' : '' }}" 
                                     wire:click="selectConversation({{ $conversation->id }})">
                                    <div class="admin-conversation-header">
                                        <div class="admin-conversation-title">
                                            @if($conversation->title)
                                                {{ $conversation->title }}
                                            @else
                                                @if($conversation->type === 'player')
                                                    Conversation entre joueurs
                                                @elseif($conversation->type === 'alliance')
                                                    Conversation d'alliance
                                                @else
                                                    {{ $conversation->getTypeLabel() }}
                                                @endif
                                            @endif
                                        </div>
                                        <div class="admin-conversation-date">
                                            {{ $conversation->last_message_at ? $conversation->last_message_at->format('d/m/Y H:i') : $conversation->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                    <div class="admin-conversation-participants">
                                        <div class="admin-conversation-creator">
                                            <i class="fas fa-user-circle"></i>
                                            @if($conversation->creator)
                                                {{ $conversation->creator->name }}
                                            @else
                                                <span class="admin-text-muted">Utilisateur supprimé</span>
                                            @endif
                                        </div>
                                        <div class="admin-conversation-participant-count">
                                            <i class="fas fa-users"></i> {{ $conversation->participants->count() }} participants
                                        </div>
                                    </div>
                                    @if($conversation->lastMessage)
                                        <div class="admin-conversation-preview">
                                            @if($conversation->lastMessage->is_system_message)
                                                <i class="fas fa-robot"></i>
                                            @elseif($conversation->lastMessage->user)
                                                <span class="admin-conversation-sender">{{ $conversation->lastMessage->user->name }}:</span>
                                            @endif
                                            {{ Str::limit($conversation->lastMessage->message, 50) }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="admin-empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Aucune conversation trouvée</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Pagination -->
                        {{ $conversations->links() }}
                    </div>
                </div>
            </div>

            <!-- Panneau de droite: Détails de la conversation -->
            <div class="admin-messaging-content">
                @if($selectedConversation)
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title">
                                @if($selectedConversation->title)
                                    {{ $selectedConversation->title }}
                                @else
                                    @if($selectedConversation->type === 'player')
                                        Conversation entre joueurs
                                    @elseif($selectedConversation->type === 'alliance')
                                        Conversation d'alliance
                                    @else
                                        {{ $selectedConversation->getTypeLabel() }}
                                    @endif
                                @endif
                            </h2>
                            <div class="admin-badge {{ $selectedConversation->is_active ? 'success' : 'danger' }}">
                                {{ $selectedConversation->is_active ? 'Active' : 'Inactive' }}
                            </div>
                        </div>
                        <div class="admin-card-body">
                            <!-- Informations sur la conversation -->
                            <div class="admin-conversation-details">
                                <div class="admin-conversation-info-grid">
                                    <div class="admin-conversation-info-item">
                                        <div class="admin-conversation-info-label">ID</div>
                                        <div class="admin-conversation-info-value">{{ $selectedConversation->id }}</div>
                                    </div>
                                    <div class="admin-conversation-info-item">
                                        <div class="admin-conversation-info-label">Type</div>
                                        <div class="admin-conversation-info-value">{{ $selectedConversation->getTypeLabel() }}</div>
                                    </div>
                                    <div class="admin-conversation-info-item">
                                        <div class="admin-conversation-info-label">Créée par</div>
                                        <div class="admin-conversation-info-value">
                                            @if($selectedConversation->creator)
                                                {{ $selectedConversation->creator->name }}
                                            @else
                                                <span class="admin-text-muted">Utilisateur supprimé</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="admin-conversation-info-item">
                                        <div class="admin-conversation-info-label">Créée le</div>
                                        <div class="admin-conversation-info-value">{{ $selectedConversation->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <div class="admin-conversation-info-item">
                                        <div class="admin-conversation-info-label">Dernier message</div>
                                        <div class="admin-conversation-info-value">
                                            {{ $selectedConversation->last_message_at ? $selectedConversation->last_message_at->format('d/m/Y H:i') : 'Aucun message' }}
                                        </div>
                                    </div>
                                    <div class="admin-conversation-info-item">
                                        <div class="admin-conversation-info-label">Participants</div>
                                        <div class="admin-conversation-info-value">{{ $selectedConversation->participants->count() }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Liste des participants -->
                            <div class="admin-conversation-participants-list">
                                <h3>Participants</h3>
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>Utilisateur</th>
                                                <th>A rejoint le</th>
                                                <th>Dernière lecture</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($selectedConversation->participants as $participant)
                                                <tr>
                                                    <td>
                                                        @if($participant)
                                                            {{ $participant->name }}
                                                        @else
                                                            <span class="admin-text-muted">Utilisateur supprimé</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(is_string($participant->pivot->joined_at))
                                                            {{ $participant->pivot->joined_at }}
                                                        @elseif($participant->pivot->joined_at)
                                                            {{ $participant->pivot->joined_at->format('d/m/Y H:i') }}
                                                        @else
                                                            <span class="admin-text-muted">Inconnu</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(is_string($participant->pivot->last_read_at))
                                                            {{ $participant->pivot->last_read_at }}
                                                        @elseif($participant->pivot->last_read_at)
                                                            {{ $participant->pivot->last_read_at->format('d/m/Y H:i') }}
                                                        @else
                                                            <span class="admin-text-muted">Jamais</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($participant->pivot->is_active)
                                                            <span class="admin-badge success">Actif</span>
                                                        @else
                                                            <span class="admin-badge danger">Inactif</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="admin-table-empty">Aucun participant</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Messages de la conversation -->
                            <div class="admin-conversation-messages">
                                <h3>Messages</h3>
                                <div class="admin-messages-container">
                                    @forelse($messages as $message)
                                        <div class="admin-message {{ $message->is_system_message ? 'admin-message-system' : '' }}">
                                            <div class="admin-message-header">
                                                <div class="admin-message-sender">
                                                    @if($message->is_system_message)
                                                        <i class="fas fa-robot"></i> Système
                                                    @elseif($message->user)
                                                        <i class="fas fa-user-circle"></i> {{ $message->user->name }}
                                                    @else
                                                        <i class="fas fa-user-circle"></i> <span class="admin-text-muted">Utilisateur supprimé</span>
                                                    @endif
                                                </div>
                                                <div class="admin-message-date">
                                                    {{ $message->created_at->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                            <div class="admin-message-content">
                                                {!! nl2br(e($message->message)) !!}
                                            </div>
                                            <div class="admin-message-status">
                                                @if($message->read_at)
                                                    <span class="admin-message-read"><i class="fas fa-check-double"></i> Lu le {{ $message->read_at->format('d/m/Y H:i') }}</span>
                                                @else
                                                    <span class="admin-message-unread"><i class="fas fa-check"></i> Non lu</span>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="admin-empty-state">
                                            <i class="fas fa-comments"></i>
                                            <p>Aucun message dans cette conversation</p>
                                        </div>
                                    @endforelse
                                </div>

                                <!-- Pagination des messages -->
                                <div class="admin-pagination-container">
                                    {{ $messages->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="admin-empty-state admin-empty-state-large">
                        <i class="fas fa-comments"></i>
                        <p>Sélectionnez une conversation pour afficher les détails</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    
</div>