<div page="alliance">
    <div class="alliance-container">
        <!-- Navigation Tabs -->
        <div class="alliance-tabs">
            <?php if($alliance): ?>
                <button class="alliance-tab <?php echo e($currentTab === 'overview' ? 'active' : ''); ?>" 
                        wire:click="switchTab('overview')">
                    📊 Vue d'ensemble
                </button>
                <button class="alliance-tab <?php echo e($currentTab === 'members' ? 'active' : ''); ?>" 
                        wire:click="switchTab('members')">
                    👥 Membres
                </button>
                <button class="alliance-tab <?php echo e($currentTab === 'bank' ? 'active' : ''); ?>" 
                        wire:click="switchTab('bank')">
                    🏦 Banque
                </button>
                <?php if($userAllianceMember && $userAllianceMember->hasPermission('manage_ranks')): ?>
                    <button class="alliance-tab <?php echo e($currentTab === 'ranks' ? 'active' : ''); ?>" 
                            wire:click="switchTab('ranks')">
                        🎖️ Rangs
                    </button>
                <?php endif; ?>
                <?php if($userAllianceMember && $userAllianceMember->hasPermission('manage_members')): ?>
                    <button class="alliance-tab <?php echo e($currentTab === 'member-management' ? 'active' : ''); ?>" 
                            wire:click="switchTab('member-management')">
                        ⚙️ Gestion Membres
                    </button>
                <?php endif; ?>
                <?php if($userAllianceMember && $userAllianceMember->hasPermission('manage_applications')): ?>
                    <button class="alliance-tab <?php echo e($currentTab === 'applications' ? 'active' : ''); ?>" 
                            wire:click="switchTab('applications')">
                        📝 Candidatures
                    </button>
                <?php endif; ?>
                <button class="alliance-tab <?php echo e($currentTab === 'wars' ? 'active' : ''); ?>" 
                        wire:click="switchTab('wars')">
                    ⚔️ Guerres
                </button>
                <?php if($userAllianceMember && $userAllianceMember->hasPermission('manage_alliance')): ?>
                    <button class="alliance-tab <?php echo e($currentTab === 'technologies' ? 'active' : ''); ?>" 
                            wire:click="switchTab('technologies')">
                        🔬 Technologies
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <button class="alliance-tab <?php echo e($currentTab === 'search' ? 'active' : ''); ?>" 
                        wire:click="switchTab('search')">
                    🔍 Rechercher
                </button>
                <button class="alliance-tab <?php echo e($currentTab === 'create' ? 'active' : ''); ?>" 
                        wire:click="switchTab('create')">
                    ➕ Créer
                </button>
            <?php endif; ?>
        </div>

        <div class="alliance-content">
            <?php if($alliance): ?>
                <!-- Alliance Overview -->
                <?php if($currentTab === 'overview'): ?>
                    <div class="alliance-overview">
                        <div class="alliance-info-simple">
                            <h3>🛡️ Informations de l'Alliance</h3>
                            
                            <?php if(!$editMode): ?>
                                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                                    <?php if($alliance->logo_url): ?>
                                        <img src="<?php echo e($alliance->logo_url); ?>" alt="Logo" class="alliance-logo">
                                    <?php endif; ?>
                                    <div>
                                        <h2 style="color: var(--stargate-primary); margin: 0;"><?php echo e($alliance->name); ?> [<?php echo e($alliance->tag); ?>]</h2>
                                        <p style="color: var(--stargate-text-secondary); margin: 5px 0;">Leader: <?php echo e($alliance->leader->name); ?></p>
                                    </div>
                                </div>
                                
                                <?php if($alliance->external_description): ?>
                                    <div style="margin-bottom: 15px;">
                                        <strong>Description:</strong>
                                        <p style="margin-top: 5px;"><?php echo $alliance->external_description; ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if($userAllianceMember && $userAllianceMember->hasPermission('view_internal_description') && $alliance->internal_description): ?>
                                    <div style="margin-bottom: 15px;">
                                        <strong>Description interne:</strong>
                                        <p style="margin-top: 5px; font-style: italic;"><?php echo $alliance->internal_description; ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if($userAllianceMember && $userAllianceMember->hasPermission('edit_alliance_info')): ?>
                                    <button class="btn btn-secondary" wire:click="toggleEditMode">
                                        ✏️ Modifier
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- Edit Mode -->
                                <div class="alliance-form">
                                    <div class="form-group">
                                        <label>Nom de l'alliance</label>
                                        <input type="text" class="form-input" wire:model="editName">
                                        <?php $__errorArgs = ['editName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Tag</label>
                                        <input type="text" class="form-input" wire:model="editTag" maxlength="10">
                                        <?php $__errorArgs = ['editTag'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Description externe</label>
                                        <?php if (isset($component)) { $__componentOriginalac67a5bc9c14e131fd19e367e45ff063 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac67a5bc9c14e131fd19e367e45ff063 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.tinymce','data' => ['wire:model.live' => 'editExternalDescription','placeholder' => 'Description visible par tous']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.tinymce'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'editExternalDescription','placeholder' => 'Description visible par tous']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac67a5bc9c14e131fd19e367e45ff063)): ?>
<?php $attributes = $__attributesOriginalac67a5bc9c14e131fd19e367e45ff063; ?>
<?php unset($__attributesOriginalac67a5bc9c14e131fd19e367e45ff063); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac67a5bc9c14e131fd19e367e45ff063)): ?>
<?php $component = $__componentOriginalac67a5bc9c14e131fd19e367e45ff063; ?>
<?php unset($__componentOriginalac67a5bc9c14e131fd19e367e45ff063); ?>
<?php endif; ?>
                                        <?php $__errorArgs = ['editExternalDescription'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Description interne</label>
                                        <?php if (isset($component)) { $__componentOriginalac67a5bc9c14e131fd19e367e45ff063 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac67a5bc9c14e131fd19e367e45ff063 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.tinymce','data' => ['wire:model.live' => 'editInternalDescription','placeholder' => 'Description visible uniquement par les membres']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.tinymce'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'editInternalDescription','placeholder' => 'Description visible uniquement par les membres']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac67a5bc9c14e131fd19e367e45ff063)): ?>
<?php $attributes = $__attributesOriginalac67a5bc9c14e131fd19e367e45ff063; ?>
<?php unset($__attributesOriginalac67a5bc9c14e131fd19e367e45ff063); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac67a5bc9c14e131fd19e367e45ff063)): ?>
<?php $component = $__componentOriginalac67a5bc9c14e131fd19e367e45ff063; ?>
<?php unset($__componentOriginalac67a5bc9c14e131fd19e367e45ff063); ?>
<?php endif; ?>
                                        <?php $__errorArgs = ['editInternalDescription'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Nombre maximum de membres</label>
                                        <input type="number" class="form-input" wire:model="editMaxMembers" min="1" max="100">
                                        <?php $__errorArgs = ['editMaxMembers'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Logo de l'alliance</label>
                                        <input type="file" class="form-input" wire:model="logo" accept="image/*">
                                        <?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="form-checkbox">
                                            <input type="checkbox" wire:model="editOpenRecruitment" id="openRecruitment">
                                            <label for="openRecruitment">Recrutement ouvert</label>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; gap: 10px;">
                                        <button class="btn btn-primary" wire:click="saveAllianceInfo">
                                            💾 Sauvegarder
                                        </button>
                                        <button class="btn btn-secondary" wire:click="toggleEditMode">
                                            ❌ Annuler
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="alliance-stats-simple">
                            <h3>📊 Statistiques</h3>
                            <ul class="stats-list">
                                <li class="stat-line">
                                    <span class="stat-icon">👥</span>
                                    <span class="stat-text"><?php echo e($alliance->member_count); ?> Membres</span>
                                </li>
                                <li class="stat-line">
                                    <span class="stat-icon">⚡</span>
                                    <span class="stat-text"><?php echo e(number_format($alliance->deuterium_bank)); ?> Deuterium</span>
                                </li>
                                <li class="stat-line">
                                    <span class="stat-icon">🕒</span>
                                    <span class="stat-text">Créée <?php echo e($alliance->created_at->diffForHumans()); ?></span>
                                </li>
                            </ul>
                            
                            <?php if(!Auth::user()->isAllianceLeader()): ?>
                                <div style="margin-top: 20px; text-align: center;">
                                    <button class="btn btn-danger" wire:click="confirmLeave">
                                        🚪 Quitter l'alliance
                                    </button>
                                </div>
                            <?php else: ?>
                                <div style="margin-top: 20px; text-align: center;">
                                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                                        <button class="btn btn-warning" wire:click="showTransferLeadershipModal">
                                            👑 Céder l'alliance
                                        </button>
                                        <button class="btn btn-danger" wire:click="confirmDelete">
                                            🗑️ Supprimer l'alliance
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Members Tab -->
                <?php if($currentTab === 'members'): ?>
                    <h3>👥 Membres de l'Alliance (<?php echo e($members->total()); ?>)</h3>
                    
                    <div class="members-list">
                        <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="member-item">
                                <div class="member-info">
                                    <span class="member-name"><?php echo e($member->user->name); ?></span>
                                    <?php if($member->rank): ?>
                                        <span class="member-rank"><?php echo e($member->rank->name); ?></span>
                                    <?php endif; ?>
                                    <?php if($member->user_id === $alliance->leader_id): ?>
                                        <span style="color: gold;">👑</span>
                                    <?php endif; ?>
                                </div>
                                <div class="member-stats">
                                    <span>Rejoint: <?php echo e($member->joined_at->format('d/m/Y')); ?></span>
                                    <span>Contribution: <?php echo e(number_format($member->contributed_deuterium)); ?> D</span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    
                    <?php echo e($members->links()); ?>

                <?php endif; ?>
                
                <!-- Bank Tab -->
                <?php if($currentTab === 'bank'): ?>
                    <h3 class="bank-title">🏦 Banque de l'Alliance</h3>
                    
                    <div class="bank-section">
                        <div class="bank-card">
                            <h4>💰 Solde Actuel</h4>
                            <div class="bank-balance">
                                <span class="bank-balance-value"><?php echo e(number_format($alliance->deuterium_bank)); ?></span>
                                <span class="bank-balance-label">Deuterium</span>
                            </div>
                            <div class="bank-capacity" style="margin-top: 8px; color: var(--stargate-text-secondary);">
                                <span>Capacité maximale:</span>
                                <span style="color: var(--stargate-accent); font-weight: 600; margin-left: 6px;"><?php echo e(number_format($alliance->getMaxDeuteriumStorage())); ?></span>
                                <span style="margin-left: 4px;">Deuterium</span>
                            </div>
                        </div>
                        
                        <div class="bank-card">
                            <h4>📥 Déposer du Deuterium</h4>
                            <div class="bank-actions">
                                <input type="number" class="bank-input" wire:model="bankDepositAmount" 
                                    placeholder="Quantité" min="1">
                                <button class="btn btn-primary" wire:click="depositToDeuteriumBank">
                                    Déposer
                                </button>
                            </div>
                            <?php $__errorArgs = ['bankDepositAmount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <?php if($userAllianceMember && $userAllianceMember->hasPermission('manage_bank')): ?>
                            <div class="bank-card">
                                <h4>📤 Retirer du Deuterium</h4>
                                <div class="bank-actions">
                                    <input type="number" class="bank-input" wire:model="bankWithdrawAmount" 
                                        placeholder="Quantité" min="1">
                                    <button class="btn btn-danger" wire:click="withdrawFromDeuteriumBank">
                                        Retirer
                                    </button>
                                </div>
                                <?php $__errorArgs = ['bankWithdrawAmount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Wars Tab -->
                <?php if($currentTab === 'wars'): ?>
                    <h3>⚔️ Guerres</h3>
                    
                    <?php if($wars->count() > 0): ?>
                        <div class="wars-list">
                            <?php $__currentLoopData = $wars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $war): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="war-item">
                                    <div class="war-header">
                                        <div class="war-alliances">
                                            <?php echo e($war->attackerAlliance->name); ?> ⚔️ <?php echo e($war->defenderAlliance->name); ?>

                                        </div>
                                        <span class="war-status <?php echo e($war->status); ?>">
                                            <?php echo e($war->formatted_status); ?>

                                        </span>
                                    </div>
                                    
                                    <?php if($war->reason): ?>
                                        <p><strong>Raison:</strong> <?php echo e($war->reason); ?></p>
                                    <?php endif; ?>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                        <small style="color: var(--stargate-text-secondary);">
                                            Déclarée par <?php echo e($war->declaredBy->name); ?> le <?php echo e($war->created_at->format('d/m/Y H:i')); ?>

                                        </small>
                                        
                                        <?php if($war->isActive() && $war->canBeEndedBy(Auth::user())): ?>
                                            <button class="btn btn-sm btn-secondary" 
                                                    wire:click="confirmEndWar(<?php echo e($war->id); ?>)">
                                                🏳️ Terminer
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        
                        <?php echo e($wars->links()); ?>

                    <?php else: ?>
                        <p style="text-align: center; color: var(--stargate-text-secondary); padding: 40px;">
                            Aucune guerre en cours
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Technologies Tab -->
                <?php if($currentTab === 'technologies'): ?>
                    <h3>🔬 Technologies d'Alliance</h3>
                    
                    <div class="technologies-section">
                        <div class="alliance-info-card">
                            <p style="color: var(--stargate-text-secondary); margin-bottom: 20px;">
                                Les technologies d'alliance améliorent les capacités de votre alliance. 
                                Chaque amélioration coûte du deuterium de la banque d'alliance.
                            </p>
                            
                            <div class="bank-info" style="margin-bottom: 30px;">
                                <strong>💰 Deuterium en banque: </strong>
                                <span style="color: var(--stargate-accent);"><?php echo e(number_format($alliance->deuterium_bank)); ?></span>
                            </div>
                        </div>
                        
                        <div class="technologies-grid">
                            <!-- Technology: Members -->
                            <?php
                                $membersTech = $technologies['members'] ?? null;
                            ?>
                            <div class="technology-card">
                                <div class="technology-header">
                                    <div class="technology-icon">👥</div>
                                    <div class="technology-info">
                                        <h4><?php echo e($membersTech ? $membersTech->getName() : 'Expansion des Membres'); ?></h4>
                                        <p><?php echo e($membersTech ? $membersTech->getDescription() : 'Augmente la capacité maximale de membres de l\'alliance'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="technology-stats">
                                    <div class="stat-row">
                                        <span>Niveau actuel:</span>
                                        <span class="stat-value"><?php echo e($membersTech ? $membersTech->level : 0); ?>/15</span>
                                    </div>
                                    <div class="stat-row">
                                        <span>Bonus actuel:</span>
                                        <span class="stat-value">+<?php echo e($membersTech ? $membersTech->getBonus() : 0); ?> membres</span>
                                    </div>
                                    <div class="stat-row">
                                        <span>Capacité totale:</span>
                                        <span class="stat-value"><?php echo e($alliance->getMaxMembers()); ?> membres</span>
                                    </div>
                                    <?php if($membersTech && $membersTech->canUpgrade()): ?>
                                        <div class="stat-row">
                                            <span>Prochain niveau:</span>
                                            <span class="stat-value">+<?php echo e($membersTech->getNextLevelBonus()); ?> membres</span>
                                        </div>
                                        <div class="stat-row">
                                            <span>Coût:</span>
                                            <span class="stat-value cost"><?php echo e(number_format($membersTech->getUpgradeCost())); ?> deuterium</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if($membersTech && $membersTech->canUpgrade()): ?>
                                    <button class="btn btn-primary" 
                                            wire:click="showTechnologyUpgrade('members')"
                                            <?php echo e($alliance->deuterium_bank < $membersTech->getUpgradeCost() ? 'disabled' : ''); ?>>
                                        🔬 Améliorer
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                        ✅ Niveau Maximum
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Technology: Bank -->
                            <?php
                                $bankTech = $technologies['bank'] ?? null;
                            ?>
                            <div class="technology-card">
                                <div class="technology-header">
                                    <div class="technology-icon">🏦</div>
                                    <div class="technology-info">
                                        <h4><?php echo e($bankTech ? $bankTech->getName() : 'Stockage Avancé'); ?></h4>
                                        <p><?php echo e($bankTech ? $bankTech->getDescription() : 'Augmente la capacité de stockage de deuterium de la banque'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="technology-stats">
                                    <div class="stat-row">
                                        <span>Niveau actuel:</span>
                                        <span class="stat-value"><?php echo e($bankTech ? $bankTech->level : 0); ?>/15</span>
                                    </div>
                                    <div class="stat-row">
                                        <span>Bonus actuel:</span>
                                        <span class="stat-value">+<?php echo e($bankTech ? number_format($bankTech->getBonus()) : 0); ?> deuterium</span>
                                    </div>
                                    <div class="stat-row">
                                        <span>Capacité totale:</span>
                                        <span class="stat-value"><?php echo e(number_format($alliance->getMaxDeuteriumStorage())); ?> deuterium</span>
                                    </div>
                                    <?php if($bankTech && $bankTech->canUpgrade()): ?>
                                        <div class="stat-row">
                                            <span>Prochain niveau:</span>
                                            <span class="stat-value">+<?php echo e(number_format($bankTech->getNextLevelBonus())); ?> deuterium</span>
                                        </div>
                                        <div class="stat-row">
                                            <span>Coût:</span>
                                            <span class="stat-value cost"><?php echo e(number_format($bankTech->getUpgradeCost())); ?> deuterium</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if($bankTech && $bankTech->canUpgrade()): ?>
                                    <button class="btn btn-primary" 
                                            wire:click="showTechnologyUpgrade('bank')"
                                            <?php echo e($alliance->deuterium_bank < $bankTech->getUpgradeCost() ? 'disabled' : ''); ?>>
                                        🔬 Améliorer
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                        ✅ Niveau Maximum
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Ranks Tab -->
                <?php if($currentTab === 'ranks'): ?>
                    <h3>🎖️ Gestion des Rangs</h3>
                    
                    <div class="ranks-section">
                        <!-- Existing Ranks -->
                        <div class="ranks-list">
                            <h4>Rangs existants</h4>
                            <?php $__currentLoopData = $ranks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="rank-item">
                                    <div class="rank-info">
                                        <span class="rank-name"><?php echo e($rank->name); ?></span>
                                        <span class="rank-level">Niveau <?php echo e($rank->level); ?></span>
                                    </div>
                                    <div class="rank-permissions">
                                        <?php $__currentLoopData = $rank->permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="permission-badge"><?php echo e($availablePermissions[$permission] ?? $permission); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <div class="rank-actions">
                                        <button class="btn btn-sm btn-secondary" wire:click="editRank(<?php echo e($rank->id); ?>)">
                                            ✏️ Modifier
                                        </button>
                                        <?php if($rank->level > 1): ?>
                                            <button class="btn btn-sm btn-danger" wire:click="deleteRank(<?php echo e($rank->id); ?>)"
                                                    onclick="return confirm('Supprimer ce rang ?')">
                                                🗑️ Supprimer
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        
                        <!-- Create/Edit Rank Form -->
                        <div class="rank-form">
                            <h4><?php echo e($editingRank ? 'Modifier le rang' : 'Créer un nouveau rang'); ?></h4>
                            
                            <div class="form-group">
                                <label>Nom du rang</label>
                                <input type="text" class="form-input" wire:model="newRankName" 
                                    placeholder="Nom du rang">
                                <?php $__errorArgs = ['newRankName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="form-group">
                                <label>Niveau (1-10)</label>
                                <input type="number" class="form-input" wire:model="newRankLevel" 
                                    min="1" max="10">
                                <?php $__errorArgs = ['newRankLevel'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="form-group">
                                <label>Permissions</label>
                                <div class="permissions-grid">
                                    <?php $__currentLoopData = $availablePermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <label class="permission-checkbox">
                                            <input type="checkbox" wire:model="newRankPermissions" value="<?php echo e($key); ?>">
                                            <span><?php echo e($label); ?></span>
                                        </label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button class="btn btn-primary" wire:click="<?php echo e($editingRank ? 'updateRank' : 'createRank'); ?>">
                                    <?php echo e($editingRank ? '💾 Mettre à jour' : '➕ Créer le rang'); ?>

                                </button>
                                <?php if($editingRank): ?>
                                    <button class="btn btn-secondary" wire:click="cancelEditRank">
                                        ❌ Annuler
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Member Management Tab -->
                <?php if($currentTab === 'member-management'): ?>
                    <h3>👥 Gestion des Membres</h3>
                    
                    <div class="member-management-section">
                        <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="member-management-item">
                                <div class="member-basic-info">
                                    <div class="member-avatar">
                                        <span class="member-initial"><?php echo e(substr($member->user->name, 0, 1)); ?></span>
                                    </div>
                                    <div class="member-details">
                                        <span class="member-name"><?php echo e($member->user->name); ?></span>
                                        <span class="member-current-rank">
                                            <?php if($member->rank): ?>
                                                <?php echo e($member->rank->name); ?>

                                            <?php else: ?>
                                                Aucun rang
                                            <?php endif; ?>
                                        </span>
                                        <?php if($member->user_id === $alliance->leader_id): ?>
                                            <span class="leader-badge">👑 Leader</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="member-stats-detailed">
                                    <div class="stat-item">
                                        <span class="stat-label">Rejoint:</span>
                                        <span class="stat-value"><?php echo e($member->joined_at->format('d/m/Y')); ?></span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Contribution:</span>
                                        <span class="stat-value"><?php echo e(number_format($member->contributed_deuterium)); ?> D</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Dernière activité:</span>
                                        <span class="stat-value"><?php echo e($member->user->last_activity ? $member->user->last_activity->diffForHumans() : 'Inconnue'); ?></span>
                                    </div>
                                </div>
                                
                                <?php if($member->user_id !== $alliance->leader_id && $userAllianceMember && $userAllianceMember->hasPermission('manage_members')): ?>
                                    <div class="member-actions">
                                        <div class="rank-assignment">
                                            <select class="form-select" wire:change="assignRank(<?php echo e($member->id); ?>, $event.target.value)">
                                                <option value="">Aucun rang</option>
                                                <?php $__currentLoopData = $ranks->where('level', '<', $userAllianceMember->rank->level ?? 999); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($rank->id); ?>" <?php echo e($member->rank_id == $rank->id ? 'selected' : ''); ?>>
                                                        <?php echo e($rank->name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        
                                        <button class="btn btn-sm btn-danger" 
                                                wire:click="confirmKick(<?php echo e($member->id); ?>)">
                                            🚫 Exclure
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        
                        <?php echo e($members->links()); ?>

                    </div>
                <?php endif; ?>
                
                <!-- Applications Tab -->
                <?php if($currentTab === 'applications'): ?>
                    <h3>📝 Candidatures</h3>
                    
                    <div class="applications-section">
                        <?php if(isset($pendingApplications) && $pendingApplications->count() > 0): ?>
                            <?php $__currentLoopData = $pendingApplications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="application-item">
                                    <div class="application-header">
                                        <div class="applicant-info">
                                            <div class="applicant-avatar">
                                                <span class="applicant-initial"><?php echo e(substr($application->user->name, 0, 1)); ?></span>
                                            </div>
                                            <div class="applicant-details">
                                                <h4 class="applicant-name"><?php echo e($application->user->name); ?></h4>
                                                <span class="application-date">Candidature du <?php echo e($application->created_at->format('d/m/Y à H:i')); ?></span>
                                            </div>
                                        </div>
                                        <div class="application-status">
                                            <span class="status-badge status-pending">En attente</span>
                                        </div>
                                    </div>
                                    
                                    <?php if($application->message): ?>
                                        <div class="application-message">
                                            <h5>💬 Message de candidature:</h5>
                                            <p><?php echo e($application->message); ?></p>
                                        </div>
                                    <?php endif; ?>
                                                                        
                                    <div class="application-actions">
                                        <button class="btn btn-success" 
                                                wire:click="confirmAcceptApplication(<?php echo e($application->id); ?>)">
                                            ✅ Accepter
                                        </button>
                                        <button class="btn btn-danger" 
                                                wire:click="confirmRejectApplication(<?php echo e($application->id); ?>)">
                                            ❌ Rejeter
                                        </button>
                                        <button class="btn btn-secondary" 
                                                wire:click="viewUserProfile(<?php echo e($application->user->id); ?>)">
                                            👤 Voir le profil
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            
                            <!-- Pagination -->
                            <div class="applications-pagination">
                                <?php echo e($pendingApplications->links()); ?>

                            </div>
                        <?php else: ?>
                            <div class="no-applications">
                                <div class="empty-state">
                                    <div class="empty-icon">📭</div>
                                    <h4>Aucune candidature en attente</h4>
                                    <p>Il n'y a actuellement aucune candidature à examiner.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Modales de confirmation réutilisables -->
                <?php if (isset($component)) { $__componentOriginal3347679a0053d4c83e098c2ad5c70893 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3347679a0053d4c83e098c2ad5c70893 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.modal-confirmation','data' => ['wire:model' => 'showLeaveModal','wire:key' => 'alliance-modal-leave','title' => 'Quitter l\'alliance','message' => 'Êtes-vous sûr de vouloir quitter l\'alliance ?','icon' => 'fas fa-question-circle text-warning','confirmText' => 'Oui, quitter','cancelText' => 'Rester dans l\'alliance','onConfirm' => 'performLeave','onCancel' => 'dismissModals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.modal-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'showLeaveModal','wire:key' => 'alliance-modal-leave','title' => 'Quitter l\'alliance','message' => 'Êtes-vous sûr de vouloir quitter l\'alliance ?','icon' => 'fas fa-question-circle text-warning','confirmText' => 'Oui, quitter','cancelText' => 'Rester dans l\'alliance','onConfirm' => 'performLeave','onCancel' => 'dismissModals']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $attributes = $__attributesOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $component = $__componentOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__componentOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal3347679a0053d4c83e098c2ad5c70893 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3347679a0053d4c83e098c2ad5c70893 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.modal-confirmation','data' => ['wire:model' => 'showDeleteModal','wire:key' => 'alliance-modal-delete','title' => 'Supprimer l\'alliance','message' => 'Êtes-vous sûr de vouloir supprimer définitivement l\'alliance ? Cette action est irréversible.','icon' => 'fas fa-exclamation-triangle text-danger','confirmText' => 'Oui, supprimer','cancelText' => 'Annuler','onConfirm' => 'performDelete','onCancel' => 'dismissModals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.modal-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'showDeleteModal','wire:key' => 'alliance-modal-delete','title' => 'Supprimer l\'alliance','message' => 'Êtes-vous sûr de vouloir supprimer définitivement l\'alliance ? Cette action est irréversible.','icon' => 'fas fa-exclamation-triangle text-danger','confirmText' => 'Oui, supprimer','cancelText' => 'Annuler','onConfirm' => 'performDelete','onCancel' => 'dismissModals']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $attributes = $__attributesOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $component = $__componentOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__componentOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal3347679a0053d4c83e098c2ad5c70893 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3347679a0053d4c83e098c2ad5c70893 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.modal-confirmation','data' => ['wire:model' => 'showEndWarModal','wire:key' => 'alliance-modal-endwar','title' => 'Terminer la guerre','message' => 'Terminer cette guerre ?','icon' => 'fas fa-flag text-warning','confirmText' => 'Oui, terminer','cancelText' => 'Continuer la guerre','onConfirm' => 'performEndWar','onCancel' => 'dismissModals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.modal-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'showEndWarModal','wire:key' => 'alliance-modal-endwar','title' => 'Terminer la guerre','message' => 'Terminer cette guerre ?','icon' => 'fas fa-flag text-warning','confirmText' => 'Oui, terminer','cancelText' => 'Continuer la guerre','onConfirm' => 'performEndWar','onCancel' => 'dismissModals']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $attributes = $__attributesOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $component = $__componentOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__componentOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal3347679a0053d4c83e098c2ad5c70893 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3347679a0053d4c83e098c2ad5c70893 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.modal-confirmation','data' => ['wire:model' => 'showKickModal','wire:key' => 'alliance-modal-kick','title' => 'Exclure le membre','message' => 'Êtes-vous sûr de vouloir exclure ce membre de l\'alliance ?','icon' => 'fas fa-user-slash text-danger','confirmText' => 'Oui, exclure','cancelText' => 'Annuler','onConfirm' => 'performKick','onCancel' => 'dismissModals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.modal-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'showKickModal','wire:key' => 'alliance-modal-kick','title' => 'Exclure le membre','message' => 'Êtes-vous sûr de vouloir exclure ce membre de l\'alliance ?','icon' => 'fas fa-user-slash text-danger','confirmText' => 'Oui, exclure','cancelText' => 'Annuler','onConfirm' => 'performKick','onCancel' => 'dismissModals']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $attributes = $__attributesOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $component = $__componentOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__componentOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal3347679a0053d4c83e098c2ad5c70893 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3347679a0053d4c83e098c2ad5c70893 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.modal-confirmation','data' => ['wire:model' => 'showAcceptAppModal','wire:key' => 'alliance-modal-accept-app','title' => 'Accepter la candidature','message' => 'Accepter cette candidature ?','icon' => 'fas fa-check-circle text-success','confirmText' => 'Accepter','cancelText' => 'Annuler','onConfirm' => 'performAcceptApplication','onCancel' => 'dismissModals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.modal-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'showAcceptAppModal','wire:key' => 'alliance-modal-accept-app','title' => 'Accepter la candidature','message' => 'Accepter cette candidature ?','icon' => 'fas fa-check-circle text-success','confirmText' => 'Accepter','cancelText' => 'Annuler','onConfirm' => 'performAcceptApplication','onCancel' => 'dismissModals']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $attributes = $__attributesOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $component = $__componentOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__componentOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal3347679a0053d4c83e098c2ad5c70893 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3347679a0053d4c83e098c2ad5c70893 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.modal-confirmation','data' => ['wire:model' => 'showRejectAppModal','wire:key' => 'alliance-modal-reject-app','title' => 'Rejeter la candidature','message' => 'Rejeter cette candidature ?','icon' => 'fas fa-times-circle text-danger','confirmText' => 'Rejeter','cancelText' => 'Annuler','onConfirm' => 'performRejectApplication','onCancel' => 'dismissModals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input.modal-confirmation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'showRejectAppModal','wire:key' => 'alliance-modal-reject-app','title' => 'Rejeter la candidature','message' => 'Rejeter cette candidature ?','icon' => 'fas fa-times-circle text-danger','confirmText' => 'Rejeter','cancelText' => 'Annuler','onConfirm' => 'performRejectApplication','onCancel' => 'dismissModals']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $attributes = $__attributesOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__attributesOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3347679a0053d4c83e098c2ad5c70893)): ?>
<?php $component = $__componentOriginal3347679a0053d4c83e098c2ad5c70893; ?>
<?php unset($__componentOriginal3347679a0053d4c83e098c2ad5c70893); ?>
<?php endif; ?>

            <?php else: ?>
                <!-- Search Alliances -->
                <?php if($currentTab === 'search'): ?>
                    <h3>🔍 Rechercher une Alliance</h3>
                    
                    <div class="alliance-search">
                        <input type="text" class="search-input" wire:model.live="searchQuery" 
                            placeholder="Rechercher par nom ou tag...">
                    </div>
                    
                    <?php if(count($searchResults) > 0): ?>
                        <div class="search-results">
                            <?php $__currentLoopData = $searchResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="alliance-result">
                                    <div class="alliance-result-info">
                                        <h4><?php echo e($result->name); ?> [<?php echo e($result->tag); ?>]</h4>
                                        <p>Leader: <?php echo e($result->leader->name); ?></p>
                                        <?php if($result->external_description): ?>
                                            <p><?php echo Str::limit($result->external_description, 100); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="alliance-result-stats">
                                        <div class="result-stat">
                                            <span class="result-stat-value"><?php echo e($result->member_count); ?></span>
                                            <span class="result-stat-label">Membres</span>
                                        </div>
                                        <div class="result-stat">
                                            <span class="result-stat-value"><?php echo e($result->open_recruitment ? 'Ouvert' : 'Fermé'); ?></span>
                                            <span class="result-stat-label">Recrutement</span>
                                        </div>
                                        
                                        <?php if($result->open_recruitment && $result->canAcceptNewMembers()): ?>
                                            <button class="btn btn-primary" 
                                                    wire:click="applyToAlliance(<?php echo e($result->id); ?>)">
                                                📝 Candidater
                                            </button>
                                        <?php else: ?>
                                            <span style="color: var(--stargate-text-secondary); font-size: 12px;">
                                                <?php echo e($result->canAcceptNewMembers() ? 'Recrutement fermé' : 'Alliance complète'); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php elseif(!empty($searchQuery)): ?>
                        <p style="text-align: center; color: var(--stargate-text-secondary); padding: 40px;">
                            Aucune alliance trouvée
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Create Alliance -->
                <?php if($currentTab === 'create'): ?>
                    <h3>➕ Créer une Alliance</h3>
                    
                    <div class="alliance-form">
                        <div class="form-group">
                            <label>Nom de l'alliance</label>
                            <input type="text" class="form-input" wire:model="createAllianceName" 
                                placeholder="Nom de votre alliance">
                            <?php $__errorArgs = ['createAllianceName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group">
                            <label>Tag (3-10 caractères)</label>
                            <input type="text" class="form-input" wire:model="createAllianceTag" 
                                placeholder="TAG" maxlength="10">
                            <?php $__errorArgs = ['createAllianceTag'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group">
                            <label>Description (optionnelle)</label>
                            <textarea class="form-input form-textarea" wire:model="createAllianceDescription" 
                                    placeholder="Décrivez votre alliance..."></textarea>
                            <?php $__errorArgs = ['createAllianceDescription'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #dc3545; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <button class="btn btn-primary btn-lg" wire:click="createAlliance">
                            🛡️ Créer l'Alliance
                        </button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Modal de transfert de leadership -->
        <?php if($showTransferModal): ?>
            <div class="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 1000; display: flex; align-items: center; justify-content: center;">
                <div class="modal-content" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-radius: 15px; padding: 30px; max-width: 500px; width: 90%; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <div class="modal-header" style="text-align: center; margin-bottom: 25px;">
                        <h3 style="color: #fff; margin: 0; font-size: 1.5rem; font-weight: 600;">
                            👑 Transfert de Leadership
                        </h3>
                        <p style="color: #b0b0b0; margin: 10px 0 0 0; font-size: 0.9rem;">
                            Sélectionnez le nouveau leader de l'alliance
                        </p>
                    </div>

                    <div class="modal-body">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; color: #fff; margin-bottom: 10px; font-weight: 500;">
                                Nouveau Leader :
                            </label>
                            <select wire:model="selectedNewLeaderId" 
                                    style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2); background: rgba(255, 255, 255, 0.1); color: #fff; font-size: 1rem;">
                                <option value="">-- Sélectionner un membre --</option>
                                <?php if($alliance && $alliance->members): ?>
                                    <?php $__currentLoopData = $alliance->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($member->user_id !== auth()->id()): ?>
                                            <option value="<?php echo e($member->user_id); ?>" style="background: #2a2a3e; color: #fff;">
                                                <?php echo e($member->user->name); ?> 
                                                <?php if($member->rank): ?>
                                                    (<?php echo e($member->rank->name); ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                            <?php $__errorArgs = ['selectedNewLeaderId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span style="color: #ff6b6b; font-size: 0.8rem; margin-top: 5px; display: block;"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div style="background: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                <span style="color: #ffc107; font-size: 1.2rem;">⚠️</span>
                                <strong style="color: #ffc107;">Attention</strong>
                            </div>
                            <p style="color: #fff; margin: 0; font-size: 0.9rem; line-height: 1.4;">
                                En transférant le leadership, vous perdrez tous vos privilèges de leader et serez rétrogradé au rang de membre normal. Cette action est irréversible.
                            </p>
                        </div>
                    </div>

                    <div class="modal-footer" style="display: flex; gap: 15px; justify-content: flex-end;">
                        <button wire:click="closeTransferModal" 
                                style="padding: 10px 20px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.3); background: transparent; color: #fff; cursor: pointer; transition: all 0.3s ease;">
                            Annuler
                        </button>
                        <button wire:click="transferLeadership" 
                                style="padding: 10px 20px; border-radius: 8px; border: none; background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%); color: #000; font-weight: 600; cursor: pointer; transition: all 0.3s ease;"
                                onclick="return confirm('Êtes-vous absolument sûr de vouloir transférer le leadership ? Cette action est irréversible.')">
                            👑 Transférer le Leadership
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Technology Upgrade Modal -->
        <?php if($showUpgradeModal && $selectedTechnology): ?>
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center; z-index: 1000; animation: fadeIn 0.3s ease;">
                <div style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.95) 100%); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 30px; max-width: 500px; width: 90%; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5); animation: slideIn 0.3s ease;">
                    <h3 style="color: #fff; margin-bottom: 20px; text-align: center;">
                        🔬 Améliorer <?php echo e($selectedTechnology->getName()); ?>

                    </h3>
                    
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="color: var(--stargate-text-secondary);">Niveau actuel:</span>
                            <span style="color: #fff; font-weight: 600;"><?php echo e($selectedTechnology->level); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="color: var(--stargate-text-secondary);">Niveau suivant:</span>
                            <span style="color: var(--stargate-accent); font-weight: 600;"><?php echo e($selectedTechnology->level + 1); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="color: var(--stargate-text-secondary);">Bonus actuel:</span>
                            <span style="color: #fff;"><?php echo e($selectedTechnology->technology_type === 'members' ? '+' . $selectedTechnology->getBonus() . ' membres' : '+' . number_format($selectedTechnology->getBonus()) . ' deuterium'); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <span style="color: var(--stargate-text-secondary);">Nouveau bonus:</span>
                            <span style="color: var(--stargate-accent); font-weight: 600;"><?php echo e($selectedTechnology->technology_type === 'members' ? '+' . $selectedTechnology->getNextLevelBonus() . ' membres' : '+' . number_format($selectedTechnology->getNextLevelBonus()) . ' deuterium'); ?></span>
                        </div>
                        <hr style="border: none; border-top: 1px solid rgba(255, 255, 255, 0.1); margin: 15px 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--stargate-text-secondary);">Coût d'amélioration:</span>
                            <span style="color: #ffc107; font-weight: 700; font-size: 18px;"><?php echo e(number_format($selectedTechnology->getUpgradeCost())); ?> deuterium</span>
                        </div>
                    </div>
                    
                    <?php if($alliance->deuterium_bank < $selectedTechnology->getUpgradeCost()): ?>
                        <div style="background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                            <p style="color: #dc3545; margin: 0; text-align: center;">
                                ⚠️ Deuterium insuffisant en banque
                            </p>
                        </div>
                    <?php else: ?>
                        <div style="background: rgba(40, 167, 69, 0.1); border: 1px solid rgba(40, 167, 69, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                            <p style="color: #28a745; margin: 0; text-align: center;">
                                ✅ Amélioration disponible
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 15px; justify-content: center;">
                        <button wire:click="closeUpgradeModal" 
                                style="padding: 12px 24px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.3); background: transparent; color: #fff; cursor: pointer; transition: all 0.3s ease; font-weight: 500;">
                            Annuler
                        </button>
                        <button wire:click="upgradeTechnology" 
                                <?php echo e($alliance->deuterium_bank < $selectedTechnology->getUpgradeCost() ? 'disabled' : ''); ?>

                                style="padding: 12px 24px; border-radius: 8px; border: none; background: <?php echo e($alliance->deuterium_bank < $selectedTechnology->getUpgradeCost() ? 'rgba(108, 117, 125, 0.5)' : 'linear-gradient(135deg, #007bff 0%, #0056b3 100%)'); ?>; color: #fff; font-weight: 600; cursor: <?php echo e($alliance->deuterium_bank < $selectedTechnology->getUpgradeCost() ? 'not-allowed' : 'pointer'); ?>; transition: all 0.3s ease;">
                            🔬 Améliorer
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/alliance.blade.php ENDPATH**/ ?>