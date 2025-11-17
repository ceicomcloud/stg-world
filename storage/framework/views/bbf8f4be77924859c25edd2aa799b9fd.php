<div class="admin-settings">
    <div class="admin-page-header">
        <h1>Options du serveur</h1>
        <div class="admin-page-actions">
            <!-- Actions éventuelles à ajouter ici -->
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Trêve du serveur</h2>
        </div>
        <div class="admin-card-body">
            <div class="admin-form">
                <div class="admin-form-grid admin-form-grid-2">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Activer la trêve</label>
                        <label class="admin-switch">
                            <input type="checkbox" wire:model.live="truceEnabled">
                            <span class="admin-switch-slider"></span>
                        </label>
                        <div class="admin-help">Bloque les actions selon les paramètres ci-dessous et affiche le message global.</div>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Actions rapides</label>
                        <div class="admin-form-actions">
                            <button type="button" class="admin-btn admin-btn-primary" wire:click="enableAllBlocks">Tout bloquer</button>
                            <button type="button" class="admin-btn admin-btn-outline" wire:click="disableAllBlocks">Ne rien bloquer</button>
                        </div>
                    </div>
                </div>

                <div class="admin-form-grid admin-form-grid-3">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Bloquer attaques terrestres</label>
                        <label class="admin-switch">
                            <input type="checkbox" wire:model.live="truceBlockEarth">
                            <span class="admin-switch-slider"></span>
                        </label>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Bloquer attaques spatiales</label>
                        <label class="admin-switch">
                            <input type="checkbox" wire:model.live="truceBlockSpatial">
                            <span class="admin-switch-slider"></span>
                        </label>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Bloquer espionnage</label>
                        <label class="admin-switch">
                            <input type="checkbox" wire:model.live="truceBlockSpy">
                            <span class="admin-switch-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Message de trêve</label>
                    <textarea rows="3" wire:model.live="truceMessage" class="admin-form-textarea" placeholder="Ex: Trêve active sur le serveur jusqu'à demain."></textarea>
                    <div class="admin-help">S’affiche en bannière globale et dans les messages de blocage.</div>
                </div>

                <div class="admin-form-actions">
                    <button type="button" class="admin-btn admin-btn-primary" wire:click="save">Sauvegarder</button>
                    <?php if($saveStatus === 'saved'): ?>
                        <span class="admin-badge admin-badge-success">Options sauvegardées</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bonus globaux de production et stockage -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Bonus globaux</h2>
            <p class="admin-card-subtitle">Ajustez les multiplicateurs globaux appliqués à toutes les planètes.</p>
        </div>
        <div class="admin-card-body">
            <div class="admin-form-grid admin-form-grid-2">
                <div class="admin-form-group">
                    <label class="admin-form-label">Bonus production (%)</label>
                    <input type="number" step="0.1" min="0" class="admin-form-input" wire:model.live="globalProductionBonusPercent" placeholder="Ex: 25">
                    <div class="admin-help">Ex: 25 = +25% sur la production globale.</div>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Bonus stockage (%)</label>
                    <input type="number" step="0.1" min="0" class="admin-form-input" wire:model.live="globalStorageBonusPercent" placeholder="Ex: 50">
                    <div class="admin-help">Ex: 50 = +50% sur la capacité de stockage globale.</div>
                </div>
            </div>
            <div class="admin-form-actions">
                <button type="button" class="admin-btn admin-btn-primary" wire:click="saveBonuses">Appliquer les bonus</button>
            </div>
        </div>
    </div>

    <!-- Boutique (Shop) -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Boutique (Shop)</h2>
            <p class="admin-card-subtitle">Activez/désactivez les achats et appliquez un bonus Happy Hours.</p>
        </div>
        <div class="admin-card-body">
            <div class="admin-form-grid admin-form-grid-2">
                <div class="admin-form-group">
                    <label class="admin-form-label">Activer la boutique</label>
                    <label class="admin-switch">
                        <input type="checkbox" wire:model.live="shopEnabled">
                        <span class="admin-switch-slider"></span>
                    </label>
                    <div class="admin-help">Désactiver coupe toute création de commande.</div>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Bonus Happy Hours (%)</label>
                    <input type="number" step="0.1" min="0" class="admin-form-input" wire:model.live="globalShopBonusPercent" placeholder="Ex: 25">
                    <div class="admin-help">Ex: 25 = +25% d'or sur les achats.</div>
                </div>
            </div>
            <div class="admin-form-actions">
                <button type="button" class="admin-btn admin-btn-primary" wire:click="saveShopOptions">Sauvegarder boutique</button>
            </div>
        </div>
    </div>

    <!-- Planification automatique: trêves et bonus -->
    <div class="admin-card" x-data="{ type: $wire.entangle('scheduleType') }">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Planification automatique</h2>
            <p class="admin-card-subtitle">Créez des trêves pour Noël/Nouvel An, ou des périodes de bonus.</p>
        </div>
        <div class="admin-card-body">
            <!-- Préréglages rapides -->
            <div class="admin-form-group">
                <label class="admin-form-label">Préréglages rapides</label>
                <div class="admin-form-actions">
                    <button type="button" class="admin-btn admin-btn-outline" wire:click="applyPreset('noel')">Noël</button>
                    <button type="button" class="admin-btn admin-btn-outline" wire:click="applyPreset('nouvel_an')">Nouvel An</button>
                    <button type="button" class="admin-btn admin-btn-outline" wire:click="applyPreset('halloween')">Halloween</button>
                    <button type="button" class="admin-btn admin-btn-outline" wire:click="applyPreset('saint_valentin')">Saint-Valentin</button>
                    <button type="button" class="admin-btn admin-btn-outline" wire:click="applyPreset('fete_nationale')">Fête nationale</button>
                </div>
                <div class="admin-help">Ces préréglages remplissent les dates et le message. Vous pouvez ajuster ensuite.</div>
            </div>
            <div class="admin-form-grid admin-form-grid-2">
                <div class="admin-form-group">
                    <label class="admin-form-label">Type</label>
                    <select class="admin-form-select" x-model="type" wire:model.live="scheduleType">
                        <option value="truce">Trêve (blocages)</option>
                        <option value="bonus">Bonus (production/stockage)</option>
                        <option value="shop_bonus">Bonus boutique (Happy Hours)</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Message (optionnel)</label>
                    <input type="text" class="admin-form-input" wire:model.live="scheduleMessage" placeholder="Ex: Trêve de Noël">
                </div>
            </div>

            <div class="admin-form-grid admin-form-grid-2">
                <div class="admin-form-group">
                    <label class="admin-form-label">Début</label>
                    <input type="datetime-local" class="admin-form-input" wire:model.live="scheduleStartsAt">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Fin (optionnel)</label>
                    <input type="datetime-local" class="admin-form-input" wire:model.live="scheduleEndsAt">
                </div>
            </div>

            <!-- Section Trêve -->
            <div class="admin-form-grid admin-form-grid-3" x-show="type === 'truce'" x-cloak>
                <div class="admin-form-group">
                    <label class="admin-form-label">Bloquer attaques terrestres</label>
                    <label class="admin-switch">
                        <input type="checkbox" wire:model.live="scheduleBlockEarth">
                        <span class="admin-switch-slider"></span>
                    </label>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Bloquer attaques spatiales</label>
                    <label class="admin-switch">
                        <input type="checkbox" wire:model.live="scheduleBlockSpatial">
                        <span class="admin-switch-slider"></span>
                    </label>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Bloquer espionnage</label>
                    <label class="admin-switch">
                        <input type="checkbox" wire:model.live="scheduleBlockSpy">
                        <span class="admin-switch-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Section Bonus -->
            <div class="admin-form-grid admin-form-grid-2" x-show="type === 'bonus'" x-cloak>
                <div class="admin-form-group">
                    <label class="admin-form-label">Bonus production (%)</label>
                    <input type="number" step="0.1" min="0" class="admin-form-input" wire:model.live="scheduleProductionBonusPercent" placeholder="Ex: 100">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Bonus stockage (%)</label>
                    <input type="number" step="0.1" min="0" class="admin-form-input" wire:model.live="scheduleStorageBonusPercent" placeholder="Ex: 100">
                </div>
            </div>

            <!-- Section Bonus Boutique -->
            <div class="admin-form-grid admin-form-grid-1" x-show="type === 'shop_bonus'" x-cloak>
                <div class="admin-form-group">
                    <label class="admin-form-label">Bonus d'or sur achats (%)</label>
                    <input type="number" step="0.1" min="0" class="admin-form-input" wire:model.live="scheduleShopBonusPercent" placeholder="Ex: 25">
                    <div class="admin-help">Ex: 25 = +25% d'or sur les achats pendant cette période.</div>
                </div>
            </div>

            <div class="admin-form-actions">
                <button type="button" class="admin-btn admin-btn-primary" wire:click="createSchedule">
                    Créer le planning
                </button>
            </div>

            <!-- Liste des plannings récents -->
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th>Détails</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $recentSchedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($s['id']); ?></td>
                                <td><?php echo e($s['type']); ?></td>
                                <td><?php echo e($s['starts_at']); ?></td>
                                <td><?php echo e($s['ends_at'] ?: '-'); ?></td>
                                <td>
                                    <?php if($s['type'] === 'truce'): ?>
                                        Terre: <?php echo e(($s['payload']['block_earth'] ?? false) ? 'oui' : 'non'); ?>,
                                        Spatial: <?php echo e(($s['payload']['block_spatial'] ?? false) ? 'oui' : 'non'); ?>,
                                        Spy: <?php echo e(($s['payload']['block_spy'] ?? false) ? 'oui' : 'non'); ?>

                                        <?php if($s['message']): ?><br><em><?php echo e($s['message']); ?></em><?php endif; ?>
                                    <?php elseif($s['type'] === 'bonus'): ?>
                                        Prod: +<?php echo e((float)($s['payload']['production_bonus_percent'] ?? 0)); ?>%,
                                        Stock: +<?php echo e((float)($s['payload']['storage_bonus_percent'] ?? 0)); ?>%
                                        <?php if($s['message']): ?><br><em><?php echo e($s['message']); ?></em><?php endif; ?>
                                    <?php elseif($s['type'] === 'shop_bonus'): ?>
                                        Boutique: Or +<?php echo e((float)($s['payload']['gold_bonus_percent'] ?? 0)); ?>%
                                        <?php if($s['message']): ?><br><em><?php echo e($s['message']); ?></em><?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($s['enabled']): ?>
                                        <span class="admin-badge admin-badge-success">Actif</span>
                                    <?php else: ?>
                                        <span class="admin-badge">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="admin-btn admin-btn-sm" wire:click="toggleSchedule(<?php echo e($s['id']); ?>)">
                                        <?php echo e($s['enabled'] ? 'Désactiver' : 'Activer'); ?>

                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if(count($recentSchedules) === 0): ?>
                            <tr>
                                <td colspan="7" class="admin-table-empty">Aucun planning</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Historique des applications -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Historique des applications</h2>
            <p class="admin-card-subtitle">Logs des dernières applications de trêves/bonus effectuées automatiquement.</p>
        </div>
        <div class="admin-card-body">
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Message</th>
                            <th>Changements</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($log['id']); ?></td>
                                <td><?php echo e($log['applied_at']); ?></td>
                                <td><?php echo e($log['message']); ?></td>
                                <td>
                                    <details>
                                        <summary>Voir</summary>
                                        <pre class="admin-code-block"><?php echo e(json_encode($log['changes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                    </details>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if(!isset($recentLogs) || count($recentLogs) === 0): ?>
                            <tr>
                                <td colspan="4" class="admin-table-empty">Aucun log récent</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/options.blade.php ENDPATH**/ ?>