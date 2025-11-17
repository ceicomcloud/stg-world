<div class="admin-jobs">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-tasks"></i> Gestion des Jobs</h1>
        <div class="admin-page-actions">
            <!-- Onglets principaux -->
            <button class="admin-tab-button <?php echo e($activeTab === 'available' ? 'active' : ''); ?>" wire:click="setActiveTab('available')">
                <i class="fas fa-list"></i> Jobs Disponibles
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'running' ? 'active' : ''); ?>" wire:click="setActiveTab('running')">
                <i class="fas fa-play"></i> Jobs en Cours
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'failed' ? 'active' : ''); ?>" wire:click="setActiveTab('failed')">
                <i class="fas fa-exclamation-triangle"></i> Jobs Échoués
            </button>
            <button class="admin-tab-button <?php echo e($activeTab === 'batches' ? 'active' : ''); ?>" wire:click="setActiveTab('batches')">
                <i class="fas fa-layer-group"></i> Lots de Jobs
            </button>
        </div>
    </div>

    <div class="admin-content-body">
        <!-- Statut des Ticks -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Statut des Ticks</h2>
                <p class="admin-card-subtitle">Dernières exécutions et métriques</p>
            </div>
            <div class="admin-card-body">
                <div class="admin-grid-3">
                    <div class="admin-stat">
                        <h3 class="admin-stat-title"><i class="fas fa-stream"></i> Files (queues:tick)</h3>
                        <p><strong>Dernière exécution:</strong> <?php echo e($tickMetrics['queues']['last_run_at'] ?? '—'); ?></p>
                        <p><strong>Durée:</strong> <?php echo e($tickMetrics['queues']['duration_ms']); ?> ms</p>
                        <p><strong>Éléments traités:</strong> <?php echo e($tickMetrics['queues']['processed_count']); ?></p>
                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="runQueuesTick" wire:loading.attr="disabled" wire:target="runQueuesTick">
                            <i class="fas fa-play"></i>
                            <span wire:loading.remove wire:target="runQueuesTick">Exécuter</span>
                            <span wire:loading wire:target="runQueuesTick">Exécution...</span>
                        </button>
                    </div>
                    <div class="admin-stat">
                        <h3 class="admin-stat-title"><i class="fas fa-rocket"></i> Missions (missions:tick)</h3>
                        <p><strong>Dernière exécution:</strong> <?php echo e($tickMetrics['missions']['last_run_at'] ?? '—'); ?></p>
                        <p><strong>Durée:</strong> <?php echo e($tickMetrics['missions']['duration_ms']); ?> ms</p>
                        <p><strong>Missions traitées:</strong> <?php echo e($tickMetrics['missions']['processed_count']); ?></p>
                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="runMissionsTick" wire:loading.attr="disabled" wire:target="runMissionsTick">
                            <i class="fas fa-play"></i>
                            <span wire:loading.remove wire:target="runMissionsTick">Exécuter</span>
                            <span wire:loading wire:target="runMissionsTick">Exécution...</span>
                        </button>
                    </div>
                    <div class="admin-stat">
                        <h3 class="admin-stat-title"><i class="fas fa-industry"></i> Production (production:tick)</h3>
                        <p><strong>Dernière exécution:</strong> <?php echo e($tickMetrics['production']['last_run_at'] ?? '—'); ?></p>
                        <p><strong>Durée:</strong> <?php echo e($tickMetrics['production']['duration_ms']); ?> ms</p>
                        <p><strong>Utilisateurs traités:</strong> <?php echo e($tickMetrics['production']['processed_count']); ?></p>
                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="runProductionTick" wire:loading.attr="disabled" wire:target="runProductionTick">
                            <i class="fas fa-play"></i>
                            <span wire:loading.remove wire:target="runProductionTick">Exécuter</span>
                            <span wire:loading wire:target="runProductionTick">Exécution...</span>
                        </button>
                    </div>
                </div>
                <div class="admin-grid-3 mt-3">
                    <div class="admin-stat">
                        <h3 class="admin-stat-title"><i class="fas fa-robot"></i> Bot (bot:tick)</h3>
                        <p><strong>Dernière exécution:</strong> <?php echo e($tickMetrics['bot']['last_run_at'] ?? '—'); ?></p>
                        <p><strong>Durée:</strong> <?php echo e($tickMetrics['bot']['duration_ms']); ?> ms</p>
                        <p><strong>Planètes traitées:</strong> <?php echo e($tickMetrics['bot']['processed_count']); ?></p>
                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="runBotTick" wire:loading.attr="disabled" wire:target="runBotTick">
                            <i class="fas fa-play"></i>
                            <span wire:loading.remove wire:target="runBotTick">Exécuter</span>
                            <span wire:loading wire:target="runBotTick">Exécution...</span>
                        </button>
                    </div>
                    <div class="admin-stat">
                        <h3 class="admin-stat-title"><i class="fas fa-award"></i> Badges (badges:tick)</h3>
                        <p><strong>Dernière exécution:</strong> <?php echo e($tickMetrics['badges']['last_run_at'] ?? '—'); ?></p>
                        <p><strong>Durée:</strong> <?php echo e($tickMetrics['badges']['duration_ms']); ?> ms</p>
                        <p><strong>Badges attribués:</strong> <?php echo e($tickMetrics['badges']['processed_count']); ?></p>
                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="runBadgesTick" wire:loading.attr="disabled" wire:target="runBadgesTick">
                            <i class="fas fa-play"></i>
                            <span wire:loading.remove wire:target="runBadgesTick">Exécuter</span>
                            <span wire:loading wire:target="runBadgesTick">Exécution...</span>
                        </button>
                    </div>
                    <div class="admin-stat">
                        <h3 class="admin-stat-title"><i class="fas fa-chart-line"></i> Ranking (ranking:tick)</h3>
                        <p><strong>Dernière exécution:</strong> <?php echo e($tickMetrics['ranking']['last_run_at'] ?? '—'); ?></p>
                        <p><strong>Durée:</strong> <?php echo e($tickMetrics['ranking']['duration_ms']); ?> ms</p>
                        <p><strong>Utilisateurs traités:</strong> <?php echo e($tickMetrics['ranking']['processed_count']); ?></p>
                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="runRankingTick" wire:loading.attr="disabled" wire:target="runRankingTick">
                            <i class="fas fa-play"></i>
                            <span wire:loading.remove wire:target="runRankingTick">Exécuter</span>
                            <span wire:loading wire:target="runRankingTick">Exécution...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des exécutions BotTick -->
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2 class="admin-card-title"><i class="fas fa-robot"></i> Historique BotTick</h2>
                <p class="admin-card-subtitle">Détails des exécutions quotidiennes du bot</p>
            </div>
            <div class="admin-card-body">
                <?php if(isset($botRuns) && $botRuns->count() > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Statut</th>
                                <th>Début</th>
                                <th>Fin</th>
                                <th>Planètes traitées</th>
                                <th>Ressources générées</th>
                                <th>Ressources dépensées</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $botRuns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $run): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($run->id); ?></td>
                                    <td>
                                        <span class="admin-badge <?php echo e($run->status === 'completed' ? 'success' : ($run->status === 'failed' ? 'danger' : 'warning')); ?>">
                                            <?php echo e(ucfirst($run->status)); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e(optional($run->started_at)->format('d/m/Y H:i:s')); ?></td>
                                    <td><?php echo e(optional($run->finished_at)->format('d/m/Y H:i:s')); ?></td>
                                    <td><?php echo e(number_format($run->planets_processed)); ?></td>
                                    <td>
                                        <?php $gen = json_decode($run->resources_generated_json ?? '{}', true) ?? []; ?>
                                        <?php if(count($gen) > 0): ?>
                                            <?php $__currentLoopData = $gen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div><strong><?php echo e($name); ?></strong>: <?php echo e((int) $amount); ?></div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php else: ?>
                                            <span class="admin-text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $spent = json_decode($run->resources_spent_json ?? '{}', true) ?? []; ?>
                                        <?php if(count($spent) > 0): ?>
                                            <?php $__currentLoopData = $spent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div><strong><?php echo e($name); ?></strong>: <?php echo e((int) $amount); ?></div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php else: ?>
                                            <span class="admin-text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($run->details_path): ?>
                                            <a class="admin-link" href="<?php echo e(asset('storage/'.$run->details_path)); ?>" target="_blank">JSON</a>
                                        <?php else: ?>
                                            <span class="admin-text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                    <div class="admin-pagination-container">
                        <?php echo e($botRuns->links()); ?>

                    </div>
                <?php else: ?>
                    <div class="admin-empty-state">
                        <div class="admin-empty-state-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3>Aucune exécution enregistrée</h3>
                        <p>Les détails s’afficheront après le prochain bot:tick.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Barre de recherche -->
        <div class="admin-search-bar">
            <div class="admin-search-input-wrapper">
                <i class="fas fa-search admin-search-icon"></i>
                <input type="text" class="admin-search-input" placeholder="Rechercher..." wire:model.live.debounce.300ms="search">
                <?php if($search): ?>
                    <button class="admin-search-clear" wire:click="$set('search', '')">
                        <i class="fas fa-times"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contenu des onglets -->
        <div class="admin-tab-content">
            <!-- Onglet Jobs Disponibles -->
            <?php if($activeTab === 'available'): ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Jobs Disponibles</h2>
                        <p class="admin-card-subtitle">Liste des jobs que vous pouvez lancer manuellement</p>
                    </div>
                    <div class="admin-card-body">
                        <?php if(count($this->availableJobs) > 0): ?>
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $this->availableJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($job['name']); ?></td>
                                                <td><?php echo e($job['description']); ?></td>
                                                <td>
                                                    <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="selectJob('<?php echo e($job['name']); ?>')">
                                                        <i class="fas fa-play"></i> Lancer
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="admin-empty-state">
                                <div class="admin-empty-state-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h3>Aucun job trouvé</h3>
                                <p>Aucun job ne correspond à votre recherche.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Formulaire de lancement de job -->
                <?php if($selectedJob): ?>
                    <div class="admin-card mt-4">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title">Lancer <?php echo e($selectedJob); ?></h2>
                            <p class="admin-card-subtitle">Configurez les paramètres du job</p>
                        </div>
                        <div class="admin-card-body">
                            <form wire:submit.prevent="dispatchJob">
                                <?php $__currentLoopData = $this->availableJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($job['name'] === $selectedJob && count($job['params']) > 0): ?>
                                        <?php $__currentLoopData = $job['params']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paramName => $paramDescription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="admin-form-group">
                                                <label class="admin-form-label"><?php echo e($paramDescription); ?></label>
                                                <?php if($paramName === 'checkAllUsers'): ?>
                                                    <div class="admin-toggle-switch">
                                                        <input type="checkbox" id="<?php echo e($paramName); ?>" wire:model="jobParams.<?php echo e($paramName); ?>">
                                                        <label for="<?php echo e($paramName); ?>"></label>
                                                    </div>
                                                <?php elseif(str_contains($paramName, 'userId')): ?>
                                                    <input type="number" class="admin-form-control" wire:model="jobParams.<?php echo e($paramName); ?>" placeholder="Laisser vide pour tous les utilisateurs">
                                                <?php else: ?>
                                                    <input type="text" class="admin-form-control" wire:model="jobParams.<?php echo e($paramName); ?>">
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <div class="admin-form-actions">
                                    <button type="button" class="admin-btn admin-btn-secondary" wire:click="$set('selectedJob', null)">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                    <button type="submit" class="admin-btn admin-btn-primary">
                                        <i class="fas fa-play"></i> Lancer le Job
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Onglet Jobs en Cours -->
            <?php if($activeTab === 'running'): ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Jobs en Cours</h2>
                        <p class="admin-card-subtitle">Liste des jobs actuellement en file d'attente ou en cours d'exécution</p>
                    </div>
                    <div class="admin-card-body">
                        <?php if(count($runningJobs) > 0): ?>
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Job</th>
                                            <th>Queue</th>
                                            <th>Tentatives</th>
                                            <th>Créé le</th>
                                            <th>Disponible le</th>
                                            <th>Réservé le</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $runningJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($job->id); ?></td>
                                                <td><?php echo e($job->job_name); ?></td>
                                                <td><?php echo e($job->queue); ?></td>
                                                <td><?php echo e($job->attempts); ?></td>
                                                <td><?php echo e($job->created_at); ?></td>
                                                <td><?php echo e($job->available_at); ?></td>
                                                <td><?php echo e($job->reserved_at ?? 'Non réservé'); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="admin-pagination-container">
                                <?php echo e($runningJobs->links()); ?>

                            </div>
                        <?php else: ?>
                            <div class="admin-empty-state">
                                <div class="admin-empty-state-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3>Aucun job en cours</h3>
                                <p>Il n'y a actuellement aucun job en file d'attente ou en cours d'exécution.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Onglet Jobs Échoués -->
            <?php if($activeTab === 'failed'): ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Jobs Échoués</h2>
                        <p class="admin-card-subtitle">Liste des jobs qui ont échoué lors de leur exécution</p>
                        <div class="admin-card-actions">
                            <button class="admin-btn admin-btn-danger" wire:click="flushFailedJobs" wire:confirm="Êtes-vous sûr de vouloir supprimer tous les jobs échoués ?">
                                <i class="fas fa-trash"></i> Vider tous les jobs échoués
                            </button>
                        </div>
                    </div>
                    <div class="admin-card-body">
                        <?php if(count($failedJobs) > 0): ?>
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>UUID</th>
                                            <th>Connexion</th>
                                            <th>Queue</th>
                                            <th>Échoué le</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $failedJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($job->id); ?></td>
                                                <td><?php echo e($job->uuid); ?></td>
                                                <td><?php echo e($job->connection); ?></td>
                                                <td><?php echo e($job->queue); ?></td>
                                                <td><?php echo e($job->failed_at); ?></td>
                                                <td>
                                                    <div class="admin-btn-group">
                                                        <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="retryFailedJob(<?php echo e($job->id); ?>)">
                                                            <i class="fas fa-redo"></i> Réessayer
                                                        </button>
                                                        <button class="admin-btn admin-btn-sm admin-btn-danger" wire:click="deleteFailedJob(<?php echo e($job->id); ?>)" wire:confirm="Êtes-vous sûr de vouloir supprimer ce job ?">
                                                            <i class="fas fa-trash"></i> Supprimer
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="admin-pagination-container">
                                <?php echo e($failedJobs->links()); ?>

                            </div>
                        <?php else: ?>
                            <div class="admin-empty-state">
                                <div class="admin-empty-state-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3>Aucun job échoué</h3>
                                <p>Tous les jobs se sont exécutés avec succès.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Onglet Lots de Jobs -->
            <?php if($activeTab === 'batches'): ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">Lots de Jobs</h2>
                        <p class="admin-card-subtitle">Liste des lots de jobs (batches) et leur état</p>
                    </div>
                    <div class="admin-card-body">
                        <?php if(count($jobBatches) > 0): ?>
                            <div class="admin-table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom</th>
                                            <th>Total</th>
                                            <th>En attente</th>
                                            <th>Échoués</th>
                                            <th>Créé le</th>
                                            <th>Terminé le</th>
                                            <th>État</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $jobBatches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($batch->id); ?></td>
                                                <td><?php echo e($batch->name); ?></td>
                                                <td><?php echo e($batch->total_jobs); ?></td>
                                                <td><?php echo e($batch->pending_jobs); ?></td>
                                                <td><?php echo e($batch->failed_jobs); ?></td>
                                                <td><?php echo e($batch->created_at); ?></td>
                                                <td><?php echo e($batch->finished_at ?? 'En cours'); ?></td>
                                                <td>
                                                    <?php if($batch->cancelled_at): ?>
                                                        <span class="admin-badge danger">Annulé</span>
                                                    <?php elseif($batch->finished_at): ?>
                                                        <?php if($batch->failed_jobs > 0): ?>
                                                            <span class="admin-badge warning">Terminé avec erreurs</span>
                                                        <?php else: ?>
                                                            <span class="admin-badge success">Terminé</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="admin-badge primary">En cours</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="admin-pagination-container">
                                <?php echo e($jobBatches->links()); ?>

                            </div>
                        <?php else: ?>
                            <div class="admin-empty-state">
                                <div class="admin-empty-state-icon">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <h3>Aucun lot de jobs</h3>
                                <p>Aucun lot de jobs n'a été créé.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/jobs.blade.php ENDPATH**/ ?>