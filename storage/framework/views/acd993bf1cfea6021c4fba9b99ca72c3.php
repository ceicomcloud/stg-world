<div class="admin-messaging">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-envelope"></i> Messagerie</h1>
        <div class="admin-page-actions">
            <!-- Onglets principaux -->
            <button class="admin-tab-button <?php echo e($activeTab === 'player' ? 'active' : ''); ?>" wire:click="setActiveTab('player')">
                <i class="fas fa-user"></i> Joueurs
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'alliance' ? 'active' : ''); ?>" wire:click="setActiveTab('alliance')">
                <i class="fas fa-users"></i> Alliances
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'system' ? 'active' : ''); ?>" wire:click="setActiveTab('system')">
                <i class="fas fa-robot"></i> Système
            </button>
            <button type="button" class="admin-btn admin-btn-primary ms-auto" wire:click="toggleSystemMessageForm">
                <i class="fas fa-paper-plane"></i> <?php echo e($showSystemMessageForm ? 'Annuler' : 'Envoyer message global'); ?>

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
                    <div class="admin-stat-value"><?php echo e($stats['total']); ?></div>
                    <div class="admin-stat-label">Total des conversations</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon success">
                    <i class="fas fa-user"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-value"><?php echo e($stats['player']); ?></div>
                    <div class="admin-stat-label">Conversations entre joueurs</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon warning">
                    <i class="fas fa-users"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-value"><?php echo e($stats['alliance']); ?></div>
                    <div class="admin-stat-label">Conversations d'alliance</div>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon info">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="admin-stat-content">
                    <div class="admin-stat-value"><?php echo e($stats['system']); ?></div>
                    <div class="admin-stat-label">Messages système</div>
                </div>
            </div>
        </div>

        <?php if($showSystemMessageForm): ?>
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2 class="admin-card-title"><i class="fas fa-paper-plane"></i> Envoyer un message système global</h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="sendGlobalSystemMessage">
                        <div class="admin-form-group">
                            <label for="title" class="admin-form-label">Titre</label>
                            <input type="text" id="title" wire:model="systemMessageTitle" class="admin-form-input" required>
                            <?php $__errorArgs = ['systemMessageTitle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="type">Type <span class="admin-required">*</span></label>
                            <select id="type" wire:model="systemMessageType" class="admin-select">
                                <option value="">Sélectionner un type</option>
                                <?php $__currentLoopData = $systemTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['systemMessageType'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="description">Contenu</label>
                            <textarea id="description" wire:model="systemMessageContent" rows="3" class="admin-textarea"></textarea>
                            <?php $__errorArgs = ['systemMessageContent'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="admin-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="admin-form-actions">
                            <button type="button" class="admin-btn admin-btn-secondary" wire:click="toggleSystemMessageForm">Annuler</button>
                            <button type="submit" class="admin-btn admin-btn-primary">Envoyer à tous les joueurs</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="admin-messaging-container">
            <!-- Panneau de gauche: Liste des conversations -->
            <div class="admin-messaging-sidebar">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">
                            <?php if($activeTab === 'player'): ?>
                                <i class="fas fa-user"></i> Conversations entre joueurs
                            <?php elseif($activeTab === 'alliance'): ?>
                                <i class="fas fa-users"></i> Conversations d'alliance
                            <?php elseif($activeTab === 'system'): ?>
                                <i class="fas fa-robot"></i> Messages système
                            <?php endif; ?>
                        </h2>
                    </div>
                    <div class="admin-card-body">
                        <!-- Filtres -->
                        <div class="admin-filters">
                            <div class="admin-search-container">
                                <input type="text" class="admin-search-input" placeholder="Rechercher..." wire:model.live.debounce.300ms="search">
                                <i class="fas fa-search admin-search-icon"></i>
                            </div>
                            
                            <?php if($activeTab === 'system'): ?>
                                <div class="admin-filter-group">
                                    <select class="admin-select" wire:model.live="typeFilter">
                                        <option value="">Tous les types</option>
                                        <?php $__currentLoopData = $systemTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($type); ?>"><?php echo e($label); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            
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
                            <?php $__empty_1 = true; $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conversation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="admin-conversation-item <?php echo e($selectedConversationId == $conversation->id ? 'active' : ''); ?>" 
                                     wire:click="selectConversation(<?php echo e($conversation->id); ?>)">
                                    <div class="admin-conversation-header">
                                        <div class="admin-conversation-title">
                                            <?php if($conversation->title): ?>
                                                <?php echo e($conversation->title); ?>

                                            <?php else: ?>
                                                <?php if($conversation->type === 'player'): ?>
                                                    Conversation entre joueurs
                                                <?php elseif($conversation->type === 'alliance'): ?>
                                                    Conversation d'alliance
                                                <?php else: ?>
                                                    <?php echo e($conversation->getTypeLabel()); ?>

                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="admin-conversation-date">
                                            <?php echo e($conversation->last_message_at ? $conversation->last_message_at->format('d/m/Y H:i') : $conversation->created_at->format('d/m/Y H:i')); ?>

                                        </div>
                                    </div>
                                    <div class="admin-conversation-participants">
                                        <div class="admin-conversation-creator">
                                            <i class="fas fa-user-circle"></i>
                                            <?php if($conversation->creator): ?>
                                                <?php echo e($conversation->creator->name); ?>

                                            <?php else: ?>
                                                <span class="admin-text-muted">Utilisateur supprimé</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="admin-conversation-participant-count">
                                            <i class="fas fa-users"></i> <?php echo e($conversation->participants->count()); ?> participants
                                        </div>
                                    </div>
                                    <?php if($conversation->lastMessage): ?>
                                        <div class="admin-conversation-preview">
                                            <?php if($conversation->lastMessage->is_system_message): ?>
                                                <i class="fas fa-robot"></i>
                                            <?php elseif($conversation->lastMessage->user): ?>
                                                <span class="admin-conversation-sender"><?php echo e($conversation->lastMessage->user->name); ?>:</span>
                                            <?php endif; ?>
                                            <?php echo e(Str::limit($conversation->lastMessage->message, 50)); ?>

                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="admin-empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Aucune conversation trouvée</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pagination -->
                        <?php echo e($conversations->links()); ?>

                    </div>
                </div>
            </div>

            <!-- Panneau de droite: Détails de la conversation -->
            <div class="admin-messaging-content">
                <?php if($selectedConversation): ?>
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title">
                                <?php if($selectedConversation->title): ?>
                                    <?php echo e($selectedConversation->title); ?>

                                <?php else: ?>
                                    <?php if($selectedConversation->type === 'player'): ?>
                                        Conversation entre joueurs
                                    <?php elseif($selectedConversation->type === 'alliance'): ?>
                                        Conversation d'alliance
                                    <?php else: ?>
                                        <?php echo e($selectedConversation->getTypeLabel()); ?>

                                    <?php endif; ?>
                                <?php endif; ?>
                            </h2>
                            <div class="admin-badge <?php echo e($selectedConversation->is_active ? 'success' : 'danger'); ?>">
                                <?php echo e($selectedConversation->is_active ? 'Active' : 'Inactive'); ?>

                            </div>
                        </div>
                        <div class="admin-card-body">
                            <!-- Informations sur la conversation -->
                            <div class="admin-conversation-details">
                                <div class="admin-conversation-info-grid">
                                    <div class="admin-conversation-info-item">
                                        <div class="admin-conversation-info-label">ID</div>
                                        <div class="admin-conversation-info-value"><?php echo e($selectedConversation->id); ?></div>
                                    </div>
                                    <div class="admin-conversation-info-item">
                                        <div class="admin-conversation-info-label">Type</div>
                                        <div class="admin-conversation-info-value"><?php echo e($selectedConversation->getTypeLabel()); ?></div>
                                    </div>
                                    <div class="admin-conversation-info-item">
                                        <div class="admin-conversation-info-label">Créée par</div>
                                        <div class="admin-conversation-info-value">
                                            <?php if($selectedConversation->creator): ?>
                                                <?php echo e($selectedConversation->creator->name); ?>

                                            <?php else: ?>
                                                <span class="admin-text-muted">Utilisateur supprimé</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="admin-conversation-info-item">
                                        <div class="admin-conversation-info-label">Créée le</div>
                                        <div class="admin-conversation-info-value"><?php echo e($selectedConversation->created_at->format('d/m/Y H:i')); ?></div>
                                    </div>
                                    <div class="admin-conversation-info-item">
                                        <div class="admin-conversation-info-label">Dernier message</div>
                                        <div class="admin-conversation-info-value">
                                            <?php echo e($selectedConversation->last_message_at ? $selectedConversation->last_message_at->format('d/m/Y H:i') : 'Aucun message'); ?>

                                        </div>
                                    </div>
                                    <div class="admin-conversation-info-item">
                                        <div class="admin-conversation-info-label">Participants</div>
                                        <div class="admin-conversation-info-value"><?php echo e($selectedConversation->participants->count()); ?></div>
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
                                            <?php $__empty_1 = true; $__currentLoopData = $selectedConversation->participants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $participant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td>
                                                        <?php if($participant): ?>
                                                            <?php echo e($participant->name); ?>

                                                        <?php else: ?>
                                                            <span class="admin-text-muted">Utilisateur supprimé</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if(is_string($participant->pivot->joined_at)): ?>
                                                            <?php echo e($participant->pivot->joined_at); ?>

                                                        <?php elseif($participant->pivot->joined_at): ?>
                                                            <?php echo e($participant->pivot->joined_at->format('d/m/Y H:i')); ?>

                                                        <?php else: ?>
                                                            <span class="admin-text-muted">Inconnu</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if(is_string($participant->pivot->last_read_at)): ?>
                                                            <?php echo e($participant->pivot->last_read_at); ?>

                                                        <?php elseif($participant->pivot->last_read_at): ?>
                                                            <?php echo e($participant->pivot->last_read_at->format('d/m/Y H:i')); ?>

                                                        <?php else: ?>
                                                            <span class="admin-text-muted">Jamais</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($participant->pivot->is_active): ?>
                                                            <span class="admin-badge success">Actif</span>
                                                        <?php else: ?>
                                                            <span class="admin-badge danger">Inactif</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="4" class="admin-table-empty">Aucun participant</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Messages de la conversation -->
                            <div class="admin-conversation-messages">
                                <h3>Messages</h3>
                                <div class="admin-messages-container">
                                    <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div class="admin-message <?php echo e($message->is_system_message ? 'admin-message-system' : ''); ?>">
                                            <div class="admin-message-header">
                                                <div class="admin-message-sender">
                                                    <?php if($message->is_system_message): ?>
                                                        <i class="fas fa-robot"></i> Système
                                                    <?php elseif($message->user): ?>
                                                        <i class="fas fa-user-circle"></i> <?php echo e($message->user->name); ?>

                                                    <?php else: ?>
                                                        <i class="fas fa-user-circle"></i> <span class="admin-text-muted">Utilisateur supprimé</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="admin-message-date">
                                                    <?php echo e($message->created_at->format('d/m/Y H:i')); ?>

                                                </div>
                                            </div>
                                            <div class="admin-message-content">
                                                <?php echo nl2br(e($message->message)); ?>

                                            </div>
                                            <div class="admin-message-status">
                                                <?php if($message->read_at): ?>
                                                    <span class="admin-message-read"><i class="fas fa-check-double"></i> Lu le <?php echo e($message->read_at->format('d/m/Y H:i')); ?></span>
                                                <?php else: ?>
                                                    <span class="admin-message-unread"><i class="fas fa-check"></i> Non lu</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div class="admin-empty-state">
                                            <i class="fas fa-comments"></i>
                                            <p>Aucun message dans cette conversation</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Pagination des messages -->
                                <div class="admin-pagination-container">
                                    <?php echo e($messages->links()); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="admin-empty-state admin-empty-state-large">
                        <i class="fas fa-comments"></i>
                        <p>Sélectionnez une conversation pour afficher les détails</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/messaging.blade.php ENDPATH**/ ?>