<div class="admin-server-events">
    <div class="admin-page-header">
        <h1>Événements serveur</h1>
        <div class="admin-page-actions">
            <?php if($activeEvent): ?>
                <button class="admin-action-button admin-action-danger" wire:click="stopEvent">
                    <i class="fas fa-stop-circle"></i> Arrêter et distribuer les récompenses
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-content-body">
        <div class="admin-grid-2">
            <!-- Événement actif -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Événement actif</h2>
                    <p class="admin-card-subtitle">Détails de l'événement actuellement en cours.</p>
                </div>
                <div class="admin-card-body">
                    <?php if($activeEvent): ?>
                        <div class="admin-profile-details">
                            <div class="admin-profile-section">
                                <h4>Informations</h4>
                                <div class="admin-profile-grid">
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Type</span>
                                        <span class="admin-profile-value"><?php echo e(ucfirst($activeEvent['type'])); ?></span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Récompense</span>
                                        <span class="admin-profile-value"><?php echo e($activeEvent['reward_type'] === 'gold' ? 'Or' : 'Ressources'); ?></span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Base</span>
                                        <span class="admin-profile-value"><?php echo e(number_format($activeEvent['base_reward'])); ?></span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Multiplicateur</span>
                                        <span class="admin-profile-value"><?php echo e(number_format($activeEvent['points_multiplier'], 2)); ?></span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Début</span>
                                        <span class="admin-profile-value"><?php echo e($activeEvent['start_at']); ?></span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Fin</span>
                                        <span class="admin-profile-value"><?php echo e($activeEvent['end_at'] ?? '—'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="admin-profile-section">
                                <h4>Top joueurs</h4>
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Joueur</th>
                                                <th>Alliance</th>
                                                <th>Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $leaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td><?php echo e($row['rank']); ?></td>
                                                    <td><?php echo e($row['name']); ?></td>
                                                    <td><?php echo e($row['alliance'] ?? '—'); ?></td>
                                                    <td><?php echo e(number_format($row['score'])); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="4" class="admin-table-empty">Aucun participant pour le moment</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>Aucun événement actif pour le moment.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Créer / démarrer un événement -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Créer un événement</h2>
                    <p class="admin-card-subtitle">Configurer et démarrer un nouvel événement.</p>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label for="type">Type d'événement</label>
                            <select id="type" class="admin-select" wire:model.live="type">
                                <option value="attaque">Attaque (points)</option>
                                <option value="exploration">Exploration (comptes)</option>
                                <option value="extraction">Extraction (comptes)</option>
                                <option value="pillage">Pillage (ressources total)</option>
                                <option value="construction">Construction (ressources dépensées)</option>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="reward_type">Type de récompense</label>
                            <select id="reward_type" class="admin-select" wire:model.live="reward_type">
                                <option value="resource">Ressources</option>
                                <option value="gold">Or</option>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="base_reward">Récompense de base</label>
                            <input id="base_reward" type="number" class="admin-input" wire:model.live="base_reward" min="0" step="1">
                        </div>
                        <div class="admin-form-group">
                            <label for="points_multiplier">Multiplicateur par point</label>
                            <input id="points_multiplier" type="number" class="admin-input" wire:model.live="points_multiplier" min="0" step="0.01">
                        </div>
                        <div class="admin-form-group">
                            <label for="end_at">Fin (optionnel)</label>
                            <input id="end_at" type="text" placeholder="YYYY-MM-DD HH:MM" class="admin-input" wire:model.live="end_at">
                            <small class="admin-hint">Format: 2025-11-30 23:59</small>
                        </div>
                    </div>
                    <div class="admin-card-actions">
                        <button class="admin-action-button admin-action-primary" wire:click="startEvent">
                            <i class="fas fa-play-circle"></i> Démarrer l'événement
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/server-events.blade.php ENDPATH**/ ?>