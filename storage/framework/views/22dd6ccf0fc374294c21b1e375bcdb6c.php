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
                    <?php if(Auth::user()->alliance_id): ?>
                        <button wire:click="showAllianceBroadcastForm" class="btn btn-success btn-sm">
                            <i class="fas fa-bullhorn"></i> Alliance
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistiques des messages -->
            <div class="message-stats">
                <div class="stats-container">
                    <div class="stat-item">
                        <i class="fas fa-envelope"></i>
                        <span class="stat-label">Total :</span>
                        <span class="stat-value"><?php echo e($totalMessages); ?></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-envelope-open"></i>
                        <span class="stat-label">Non lus :</span>
                        <span class="stat-value unread"><?php echo e($unreadMessages); ?></span>
                    </div>
                </div>
                
                <?php if($unreadMessages > 0 && count($unreadByType) > 0): ?>
                    <div class="unread-details">
                        <div class="details-header">
                            <i class="fas fa-list"></i>
                            <span>Détails par type :</span>
                        </div>
                        <div class="details-container">
                            <?php $__currentLoopData = $unreadByType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $conversation = new \App\Models\Messaging\PrivateConversation(['type' => $type]);
                                ?>
                                <div class="detail-item">
                                    <span class="type-badge type-<?php echo e($type); ?>"><?php echo e($conversation->getTypeLabel()); ?></span>
                                    <span class="count-badge"><?php echo e($count); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
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
                <?php $__empty_1 = true; $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conversation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="conversation-item <?php echo e($selectedConversation && $selectedConversation->id == $conversation->id ? 'active' : ''); ?>"
                         wire:click="selectConversation(<?php echo e($conversation->id); ?>)">
                        <div class="conversation-header">
                            <span class="conversation-title"><?php echo e($conversation->title); ?></span>
                            <span class="conversation-type type-<?php echo e($conversation->type); ?>"><?php echo e($conversation->getTypeLabel()); ?></span>
                        </div>
                        <div class="conversation-preview">
                            <?php if($conversation->lastMessage): ?>
                                <span class="last-message">
                                    <?php echo e($conversation->lastMessage->user ? $conversation->lastMessage->user->name . ': ' : ''); ?>

                                </span>
                                <span class="last-time"><?php echo e($conversation->lastMessage->created_at->diffForHumans()); ?></span>
                            <?php else: ?>
                                <span class="no-messages">Aucun message</span>
                            <?php endif; ?>
                        </div>
                        <div class="participants-count">
                            <i class="fas fa-users"></i> <?php echo e($conversation->participants->count()); ?>

                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="no-conversations">
                        <p>Aucune conversation</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Zone de conversation -->
        <div class="conversation-area">
            <?php if($showNewConversation): ?>
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
                            <?php $__errorArgs = ['conversationTitle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <input type="hidden" wire:model="conversationType" value="player">
                        </div>

                        <div class="form-group">
                            <label>Participants</label>
                            <input type="text" wire:model.live="searchUsers" placeholder="Rechercher des utilisateurs..." class="form-control mb-2">
                            
                            <div class="users-list">
                                <?php $__currentLoopData = $availableUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="user-item <?php echo e(in_array($user->id, $selectedParticipants) ? 'selected' : ''); ?>"
                                         wire:click="toggleParticipant(<?php echo e($user->id); ?>)">
                                        <span class="user-name"><?php echo e($user->name); ?></span>
                                        <span class="user-role">
                                            <i class="<?php echo e($user->getRoleIcon()); ?>"></i>
                                            <?php echo e($user->getRoleName()); ?>

                                        </span>
                                        <?php if(in_array($user->id, $selectedParticipants)): ?>
                                            <i class="fas fa-check"></i>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php $__errorArgs = ['selectedParticipants'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Créer la conversation</button>
                            <button type="button" wire:click="$set('showNewConversation', false)" class="btn btn-secondary">Annuler</button>
                        </div>
                    </form>
                </div>
            <?php elseif($showAllianceBroadcast): ?>
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
                            <?php $__errorArgs = ['allianceBroadcastTitle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="allianceBroadcastMessage">Message</label>
                            <textarea id="allianceBroadcastMessage" wire:model="allianceBroadcastMessage" class="form-control" rows="6" required placeholder="Votre message à tous les membres de l'alliance..."></textarea>
                            <?php $__errorArgs = ['allianceBroadcastMessage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="alliance-info">
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <span>Ce message sera envoyé à tous les membres de votre alliance</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Alliance : <?php echo e(Auth::user()->alliance ? Auth::user()->alliance->name : 'Aucune'); ?></span>
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
            <?php elseif($selectedConversation): ?>
                <!-- Conversation sélectionnée -->
                <div class="conversation-header">
                    <div class="conversation-info">
                        <h4><?php echo e($selectedConversation->title); ?></h4>
                        <span class="conversation-type type-<?php echo e($selectedConversation->type); ?>"><?php echo e($selectedConversation->getTypeLabel()); ?></span>
                    </div>
                    <div class="conversation-actions">
                        <div class="participants-info">
                            <i class="fas fa-users"></i>
                            <?php $__currentLoopData = $selectedConversation->participants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $participant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="participant"><?php echo e($participant->name); ?></span><?php echo e(!$loop->last ? ',' : ''); ?>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <button wire:click="leaveConversation" class="btn btn-danger btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Quitter
                        </button>
                    </div>
                </div>

                <!-- Messages -->
                <div class="messages-container" id="messages-container">
                    <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="message <?php echo e($message->is_system_message ? 'system-message' : ($message->user_id == auth()->id() ? 'own-message' : 'other-message')); ?>">
                            <?php if($message->is_system_message): ?>
                                <div class="system-content">
                                    <i class="fas fa-info-circle"></i>
                                    <?php echo $message->message; ?>

                                    <span class="message-time"><?php echo e($message->created_at->format('H:i')); ?></span>
                                </div>
                            <?php else: ?>
                                <div class="message-header">
                                    <span class="message-author"><?php echo e($message->user->name); ?></span>
                                    <span class="message-time"><?php echo e($message->created_at->format('H:i')); ?></span>
                                    <?php if($message->read_at): ?>
                                        <i class="fas fa-check-double read-indicator"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="message-content">
                                    <?php echo e($message->message); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <!-- Zone de saisie -->
                <?php if($selectedConversation->canReply()): ?>
                    <div class="message-input-area">
                        <form wire:submit.prevent="sendMessage">
                            <?php if (isset($component)) { $__componentOriginalac67a5bc9c14e131fd19e367e45ff063 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac67a5bc9c14e131fd19e367e45ff063 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.tinymce','data' => ['wire:model.live' => 'newMessage','placeholder' => 'Tapez votre message...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.tinymce'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'newMessage','placeholder' => 'Tapez votre message...']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac67a5bc9c14e131fd19e367e45ff063)): ?>
<?php $attributes = $__attributesOriginalac67a5bc9c14e131fd19e367e45ff063; ?>
<?php unset($__attributesOriginalac67a5bc9c14e131fd19e367e45ff063); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac67a5bc9c14e131fd19e367e45ff063)): ?>
<?php $component = $__componentOriginalac67a5bc9c14e131fd19e367e45ff063; ?>
<?php unset($__componentOriginalac67a5bc9c14e131fd19e367e45ff063); ?>
<?php endif; ?>
                            <?php $__errorArgs = ['newMessage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            
                            <button type="submit" class="btn btn-primary mt-2">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="no-reply-notice">
                        <i class="fas fa-lock"></i>
                        Cette conversation ne permet pas de réponse.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Aucune conversation sélectionnée -->
                <div class="no-conversation-selected">
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h4>Sélectionnez une conversation</h4>
                        <p>Choisissez une conversation dans la liste ou créez-en une nouvelle.</p>
                    </div>
                </div>
            <?php endif; ?>
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
<?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/private-messaging.blade.php ENDPATH**/ ?>