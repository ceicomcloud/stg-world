<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-rocket"></i>
                Centre de Mission
            </h2>
        </div>
        
        <!-- Compteur des flottes en vol -->
        <div class="mission-status" style="margin: 8px 0 12px 0; display:flex; align-items:center; gap:8px;">
            <span class="mission-counter-badge" title="Limite basée sur le Centre de Commandement">
                Flottes en vol : <?php echo e($fleetCurrent); ?> / <?php echo e($fleetLimit); ?>

            </span>
        </div>
        
        <div class="mission-tabs" style="display:flex; gap:8px; margin-bottom:12px;">
            <button class="mission-btn <?php echo e($activeTab === 'mission' ? 'mission-btn-primary' : 'mission-btn-outline'); ?>" wire:click="switchTab('mission')">Missions</button>
            <button class="mission-btn <?php echo e($activeTab === 'bookmarks' ? 'mission-btn-primary' : 'mission-btn-outline'); ?>" wire:click="switchTab('bookmarks')">Bookmarks</button>
        </div>

        <?php if($activeTab === 'mission'): ?>
        <div class="mission-content">
            <div class="mission-form">
                <!-- Sélecteur de planètes et coordonnées -->
                <div class="mission-form-row">
                    <!-- Sélecteur de planètes -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">
                            <i class="fas fa-bookmark"></i>
                            Sélection rapide
                        </label>
                        <div class="mission-planet-selector">
                            <select class="mission-form-control" wire:model.live="selectedSourcePlanet" wire:change="selectSourcePlanet($event.target.value)">
                                <option value="">-- Sélectionner une planète --</option>
                                <?php $__currentLoopData = $userPlanets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($planet['id']); ?>">
                                        <?php echo e($planet['name']); ?> [<?php echo e($planet['galaxy']); ?>:<?php echo e($planet['system']); ?>:<?php echo e($planet['position']); ?>]
                                        <?php if($planet['is_current']): ?> (Planète actuelle) <?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <p class="mission-form-help">Sélectionnez une de vos planètes comme destination </p>
                    </div>
                    
                    <!-- Coordonnées de la cible -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">
                            <i class="fas fa-crosshairs"></i>
                            Coordonnées de la cible
                        </label>
                        <div class="mission-coordinates-container">
                            <div class="mission-coordinates">
                                <div class="coordinate-group">
                                    <label class="coordinate-label">Galaxie</label>
                                    <input type="number" class="mission-form-control mission-coordinate-input" wire:model.live="targetGalaxy" min="1" max="9">
                                </div>
                                <span class="mission-coordinate-separator">:</span>
                                <div class="coordinate-group">
                                    <label class="coordinate-label">Système</label>
                                    <input type="number" class="mission-form-control mission-coordinate-input" wire:model.live="targetSystem" min="1" max="1000">
                                </div>
                                <span class="mission-coordinate-separator">:</span>
                                <div class="coordinate-group">
                                    <label class="coordinate-label">Position</label>
                                    <input type="number" class="mission-form-control mission-coordinate-input" wire:model.live="targetPosition" min="1" max="10">
                                </div>
                            </div>
                        </div>
                        <p class="mission-form-help">Entrez les coordonnées de la planète cible</p>
                    </div>
                </div>

                <!-- Accès rapide aux Bookmarks (sélection uniquement) -->
                <div class="mission-form-group">
                    <label class="mission-form-label">
                        <i class="fas fa-bookmark"></i>
                        Accès rapide Bookmarks
                    </label>
                    <div class="mission-planet-selector">
                        <select class="mission-form-control" wire:change="selectBookmark($event.target.value)">
                            <option value="">-- Sélectionner un bookmark --</option>
                            <?php $__currentLoopData = $bookmarks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($b['id']); ?>"><?php echo e($b['label']); ?> [<?php echo e($b['galaxy']); ?>:<?php echo e($b['system']); ?>:<?php echo e($b['position']); ?>]</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <p class="mission-form-help">L'ajout de bookmarks se fait depuis la fenêtre PlanetInfo.</p>
                </div>

                <!-- Sélection du type de mission -->
                <div class="mission-form-group mission-type-section">
                    <label class="mission-form-label">
                        <i class="fas fa-space-shuttle"></i>
                        Type de mission
                    </label>
                    <p class="mission-form-help">Sélectionnez le type de mission que vous souhaitez lancer</p>
                    
                    <div class="mission-types-grid">
                        <!-- Attaque Spatiale -->
                        <div class="mission-type-card <?php echo e($missionType === 'attack_spatial' ? 'selected' : ''); ?> <?php echo e(!$canAttackSpatial ? 'disabled' : ''); ?>" 
                             wire:click="<?php echo e($canAttackSpatial ? "selectMissionType('attack_spatial')" : ''); ?>" style="--i: 0;">
                            <div class="mission-type-icon">
                                <i class="fas fa-fighter-jet"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Attaque Spatiale</h4>
                                <p>Envoyez vos vaisseaux de combat pour attaquer une planète ennemie.</p>
                            </div>
                            <?php if(!$canAttackSpatial): ?>
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucun vaisseau de combat disponible</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Attaque Terrestre -->
                        <div class="mission-type-card <?php echo e($missionType === 'attack_earth' ? 'selected' : ''); ?> <?php echo e(!$canAttackEarth ? 'disabled' : ''); ?>" 
                             wire:click="<?php echo e($canAttackEarth ? "selectMissionType('attack_earth')" : ''); ?>" style="--i: 1;">
                            <div class="mission-type-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Attaque Terrestre</h4>
                                <p>Envoyez vos unités terrestres pour attaquer une planète ennemie.</p>
                            </div>
                            <?php if(!$canAttackEarth): ?>
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucune unité terrestre disponible</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Espionnage -->
                        <div class="mission-type-card <?php echo e($missionType === 'spy' ? 'selected' : ''); ?> <?php echo e(!$canSpy ? 'disabled' : ''); ?>" 
                             wire:click="<?php echo e($canSpy ? "selectMissionType('spy')" : ''); ?>" style="--i: 2;">
                            <div class="mission-type-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Espionnage</h4>
                                <p>Envoyez des sondes pour espionner une planète et obtenir des informations.</p>
                            </div>
                            <?php if(!$canSpy): ?>
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucun vaisseau d'espionnage disponible (Requis: Scout Quantique)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Transport -->
                        <div class="mission-type-card <?php echo e($missionType === 'transport' ? 'selected' : ''); ?> <?php echo e(!$canTransport ? 'disabled' : ''); ?>" 
                             wire:click="<?php echo e($canTransport ? "selectMissionType('transport')" : ''); ?>" style="--i: 3;">
                            <div class="mission-type-icon">
                                <i class="fas fa-truck-loading"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Transport</h4>
                                <p>Transportez des ressources vers une autre planète.</p>
                            </div>
                            <?php if(!$canTransport): ?>
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucun vaisseau de transport disponible (Requis: Transporteur Delta)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Colonisation -->
                        <div class="mission-type-card <?php echo e($missionType === 'colonize' ? 'selected' : ''); ?> <?php echo e(!$canColonize ? 'disabled' : ''); ?>" 
                             wire:click="<?php echo e($canColonize ? "selectMissionType('colonize')" : ''); ?>" style="--i: 4;">
                            <div class="mission-type-icon">
                                <i class="fas fa-flag"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Colonisation</h4>
                                <p>Établissez une nouvelle colonie sur une planète inoccupée.</p>
                            </div>
                            <?php if(!$canColonize): ?>
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucun vaisseau de colonisation disponible (Requis: Vaisseau de Commandement)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Basement -->
                        <div class="mission-type-card <?php echo e($missionType === 'basement' ? 'selected' : ''); ?> <?php echo e(!$canBasement ? 'disabled' : ''); ?>" 
                             wire:click="<?php echo e($canBasement ? "selectMissionType('basement')" : ''); ?>" style="--i: 5;">
                            <div class="mission-type-icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Basement</h4>
                                <p>Transfert instantané d'unités et vaisseaux entre vos planètes.</p>
                            </div>
                            <?php if(!$canBasement): ?>
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Vous devez avoir plusieurs planètes</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Extraction -->
                        <div class="mission-type-card <?php echo e($missionType === 'extract' ? 'selected' : ''); ?> <?php echo e(!$canExtract ? 'disabled' : ''); ?>" 
                             wire:click="<?php echo e($canExtract ? "selectMissionType('extract')" : ''); ?>" style="--i: 6;">
                            <div class="mission-type-icon">
                                <i class="fas fa-pickaxe"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Extraction</h4>
                                <p>Récoltez des ressources sur des planètes non colonisées.</p>
                            </div>
                            <?php if(!$canExtract): ?>
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucun vaisseau d'extraction disponible (Requis: Transporteur Delta)</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Exploration -->
                        <div class="mission-type-card <?php echo e($missionType === 'explore' ? 'selected' : ''); ?> <?php echo e(!$canExplore ? 'disabled' : ''); ?>" 
                             wire:click="<?php echo e($canExplore ? "selectMissionType('explore')" : ''); ?>" style="--i: 7;">
                            <div class="mission-type-icon">
                                <i class="fas fa-compass"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Exploration</h4>
                                <p>Envoyez des éclaireurs pour découvrir des récompenses.</p>
                            </div>
                            <?php if(!$canExplore): ?>
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucun vaisseau d'exploration disponible (Requis: Drone Stratos ou Scout Quantique)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mission-actions">
                    <button type="button" class="mission-btn mission-btn-primary <?php echo e(!$missionType ? 'disabled' : ''); ?>" 
                            wire:click="continueMission" <?php echo e(!$missionType ? 'disabled' : ''); ?>>
                        <i class="fas fa-paper-plane"></i>
                        Continuer
                    </button>
                </div>
            </div>
        </div>
        <?php elseif($activeTab === 'bookmarks'): ?>
        <div class="mission-content">
            <div class="mission-form">
                <div class="mission-form-group">
                    <label class="mission-form-label">
                    <i class="fas fa-bookmark"></i>
                    Gestion des Bookmarks
                    <span class="mission-counter-badge"><?php echo e($bookmarkCount); ?> / <?php echo e($bookmarkLimit); ?></span>
                    </label>
                    <p class="mission-form-help">Supprimez vos bookmarks. L'ajout se fait via PlanetInfo.</p>
                    <div class="mission-bookmarks-list">
                        <?php if(empty($bookmarks)): ?>
                            <div class="mission-empty-state">
                                <i class="far fa-bookmark"></i>
                                <span>Aucun bookmark pour l'instant</span>
                            </div>
                        <?php else: ?>
                            <div class="mission-table-wrapper">
                                <table class="mission-table">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Coordonnées</th>
                                            <th class="actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $bookmarks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td class="mission-table-label"><?php echo e($b['label']); ?></td>
                                                <td class="mission-table-coords">[<?php echo e($b['galaxy']); ?>:<?php echo e($b['system']); ?>:<?php echo e($b['position']); ?>]</td>
                                                <td class="mission-table-actions">
                                                    <button type="button" class="mission-btn mission-btn-danger"
                                                            wire:click="deleteBookmark(<?php echo e($b['id']); ?>)">
                                                        <i class="fas fa-trash"></i>
                                                        Supprimer
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/mission/mission.blade.php ENDPATH**/ ?>