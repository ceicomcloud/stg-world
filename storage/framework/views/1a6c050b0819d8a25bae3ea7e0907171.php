<div>
    <?php if(!empty($planetData)): ?>
        <div class="planet-info-container">
            <!-- En-tête de la planète -->
            <div class="planet-modal-header">
                <div class="planet-visual-large">
                <div class="planet-sphere planet-type-<?php echo e($planetData['template']->type ?? 'planet'); ?>" 
                    <?php
                        $planetImage = '';
                        if ($planetData['image']) {
                            $planetImage = "background-image: url('" . asset('images/planets/' . $planetData['image']) . "')";
                        } else {
                            $randomNumber = rand(1, 10);
                            $planetImage = "background-image: url('" . asset('images/planets/planet-' . $randomNumber . '.png') . "')";
                        }
                    ?>
                    style="<?php echo e($planetImage); ?>">
                        <div class="planet-surface"></div>
                        <div class="planet-atmosphere"></div>
                        <?php if($planetData['is_main_planet']): ?>
                            <div class="main-planet-indicator">
                                <i class="fas fa-crown"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="planet-basic-info">
                    <h2 class="planet-name"><?php echo e($planetData['name'] ?? 'Planète Inconnue'); ?></h2>
                    <div class="planet-coordinates"><?php echo e($planetData['coordinates']); ?></div>
                    <?php if($planetData['is_protected']): ?>
                        <div class="planet-protection active">
                            <i class="fas fa-shield-alt"></i>
                            <span>Protection planétaire active jusqu'au <?php echo e(\Carbon\Carbon::parse($planetData['shield_protection_end'])->format('d/m/Y à H:i')); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if($planetData['is_vacation_mode']): ?>
                        <div class="planet-vacation active">
                            <i class="fas fa-umbrella-beach"></i>
                            <span>Joueur en mode vacances
                            <?php if($planetData['vacation_mode_until']): ?>
                                jusqu'au <?php echo e(\Carbon\Carbon::parse($planetData['vacation_mode_until'])->format('d/m/Y à H:i')); ?>

                            <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div class="planet-owner">
                        <?php if($planetData['is_bot']): ?>
                            <i class="fas fa-robot"></i>
                            <span class="owner-name bot">Planète PNJ</span>
                        <?php elseif($planetData['user_id']): ?>
                            <i class="fas fa-user"></i>
                            <span class="owner-name <?php echo e($planetData['is_own_planet'] ? 'own' : 'enemy'); ?>">
                                <?php echo e($planetData['user_name']); ?>

                            </span>
                            <?php if($planetData['is_main_planet']): ?>
                                <span class="main-planet-badge">
                                    <i class="fas fa-crown"></i>
                                    Planète Principale
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <i class="fas fa-globe"></i>
                            <span class="free-planet">Planète Libre</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Informations détaillées -->
            <div class="planet-details">
                <div class="detail-section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Caractéristiques
                    </h3>
                    <div class="details-grid">
                        <div class="detail-item">
                            <i class="fas fa-globe"></i>
                            <span class="detail-label">Type:</span>
                            <span class="detail-value"><?php echo e(ucfirst($planetData['type'] ?? 'Planète')); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span class="detail-label">Taille:</span>
                            <span class="detail-value"><?php echo e($planetData['size'] ?? 'Moyenne'); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-thermometer-half"></i>
                            <span class="detail-label">Température:</span>
                            <span class="detail-value"><?php echo e($planetData['temperature'] ?? 'N/A'); ?>°C</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-map"></i>
                            <span class="detail-label">Champs:</span>
                            <span class="detail-value"><?php echo e($planetData['used_fields'] ?? 0); ?>/<?php echo e($planetData['max_fields'] ?? 0); ?></span>
                        </div>
                    </div>
                </div>

                <?php if($planetData['template']): ?>
                    <!-- Bonus de ressources -->
                    <div class="detail-section">
                        <h3 class="section-title">
                            <i class="fas fa-chart-bar"></i>
                            Bonus de Ressources
                        </h3>
                        <div class="bonus-grid">
                            <div class="bonus-item">
                                <img src="/images/resources/metal.png" alt="Métal" class="resource-icon">
                                <span class="bonus-label">Métal:</span>
                                <span class="bonus-value"><?php echo e(number_format(($planetData['template']['metal_bonus'] - 1) * 100, 0)); ?>%</span>
                            </div>
                            <div class="bonus-item">
                                <img src="/images/resources/crystal.png" alt="Cristal" class="resource-icon">
                                <span class="bonus-label">Cristal:</span>
                                <span class="bonus-value"><?php echo e(number_format(($planetData['template']['crystal_bonus'] - 1) * 100, 0)); ?>%</span>
                            </div>
                            <div class="bonus-item">
                                <img src="/images/resources/deuterium.png" alt="Deutérium" class="resource-icon">
                                <span class="bonus-label">Deutérium:</span>
                                <span class="bonus-value"><?php echo e(number_format(($planetData['template']['deuterium_bonus'] - 1) * 100, 0)); ?>%</span>
                            </div>
                            <div class="bonus-item">
                                <i class="fas fa-bolt"></i>
                                <span class="bonus-label">Énergie:</span>
                                <span class="bonus-value"><?php echo e(number_format(($planetData['template']['energy_bonus'] - 1) * 100, 0)); ?>%</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($planetData['created_at']): ?>
                    <!-- Informations temporelles -->
                    <div class="detail-section">
                        <h3 class="section-title">
                            <i class="fas fa-clock"></i>
                            Informations Temporelles
                        </h3>
                        <div class="time-info">
                            <div class="time-item">
                                <i class="fas fa-calendar-plus"></i>
                                <span class="time-label">Colonisée le:</span>
                                <span class="time-value"><?php echo e($planetData['created_at']->format('d/m/Y à H:i')); ?></span>
                            </div>
                            <?php if($planetData['last_update']): ?>
                                <div class="time-item">
                                    <i class="fas fa-sync-alt"></i>
                                    <span class="time-label">Dernière activité:</span>
                                    <span class="time-value"><?php echo e($planetData['last_update']->diffForHumans()); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Actions disponibles -->
            <div class="planet-actions">
                <?php if($planetData['is_own_planet']): ?>
                    <!-- Actions pour ses propres planètes -->
                    <button class="action-btn primary" wire:click="visitPlanet">
                        <i class="fas fa-rocket"></i>
                        Aller sur cette planète
                    </button>
                    <?php if($planetId != auth()->user()->actual_planet_id): ?>
                        <button class="action-btn info" wire:click="transportToPlanet">
                            <i class="fas fa-truck"></i>
                            Transporter des ressources
                        </button>
                    <?php endif; ?>
                <?php elseif($planetData['user_id']): ?>
                    <?php if($planetData['is_allied']): ?>
                        <!-- Actions pour les planètes alliées -->
                        <button class="action-btn info" wire:click="transportToAllyPlanet">
                            <i class="fas fa-truck"></i>
                            Transporter des ressources
                        </button>
                    <?php elseif($planetData['is_protected']): ?>
                        <div class="action-info warning">
                            <i class="fas fa-shield-alt"></i>
                            Cette planète est protégée par un bouclier planétaire et ne peut pas être attaquée ou espionnée.
                        </div>
                    <?php elseif($planetData['is_vacation_mode']): ?>
                        <div class="action-info warning">
                            <i class="fas fa-umbrella-beach"></i>
                            Ce joueur est en mode vacances et ne peut pas être attaqué ou espionné.
                        </div>
                    <?php else: ?>
                        <button class="action-btn danger" wire:click="attackSpatialPlanet">
                            <i class="fas fa-sword"></i>
                            Attaquer Spatial
                        </button>
                        <button class="action-btn danger" wire:click="attackEarthPlanet">
                            <i class="fas fa-sword"></i>
                            Attaquer Terrestre
                        </button>
                        <button class="action-btn warning" wire:click="spyPlanet">
                            <i class="fas fa-eye"></i>
                            Espionner
                        </button>
                    <?php endif; ?>
                <?php elseif($planetData['is_bot']): ?>
                    <!-- Actions pour les planètes bot -->
                    <div class="action-info warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Cette planète est contrôlée par un PNJ et ne peut pas être colonisée.
                    </div>
                <?php else: ?>
                    <!-- Actions pour les planètes libres -->
                    <button class="action-btn success" wire:click="colonizePlanet">
                        <i class="fas fa-flag"></i>
                        Coloniser
                    </button>
                <?php endif; ?>

                <!-- Ajout de bookmark depuis PlanetInfo -->
                <button class="action-btn secondary" style="margin-top:8px;" wire:click.stop="openAddBookmark">
                    <i class="fas fa-bookmark"></i>
                    Ajouter bookmark
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Planète introuvable</h3>
            <p>Les informations de cette planète ne peuvent pas être chargées.</p>
        </div>
    <?php endif; ?>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/modal/planet-info.blade.php ENDPATH**/ ?>