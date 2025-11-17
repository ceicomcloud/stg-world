<div page="building">
    <div class="building-container">
        <!-- En-tête -->
        <div class="building-header">
            <!-- Navigation des types -->
            <div class="type-navigation">
                <a href="<?php echo e(route('game.construction.type', ['type' => 'building'])); ?>" class="type-nav-link <?php echo e($type === 'building' ? 'active' : ''); ?>">
                    <i class="fas fa-building"></i>
                    Bâtiments
                </a>
                <a href="<?php echo e(route('game.construction.type', ['type' => 'unit'])); ?>" class="type-nav-link <?php echo e($type === 'unit' ? 'active' : ''); ?>">
                    <i class="fas fa-users"></i>
                    Unités
                </a>
                <a href="<?php echo e(route('game.construction.type', ['type' => 'defense'])); ?>" class="type-nav-link <?php echo e($type === 'defense' ? 'active' : ''); ?>">
                    <i class="fas fa-shield-alt"></i>
                    Défenses
                </a>
                <a href="<?php echo e(route('game.construction.type', ['type' => 'ship'])); ?>" class="type-nav-link <?php echo e($type === 'ship' ? 'active' : ''); ?>">
                    <i class="fas fa-rocket"></i>
                    Vaisseaux
                </a>
                <a href="<?php echo e(route('game.construction.type', ['type' => 'equip'])); ?>" class="type-nav-link <?php echo e($type === 'equip' ? 'active' : ''); ?>">
                    <i class="fas fa-users-gear"></i>
                    Gestion d’équipe
                </a>
                <a href="<?php echo e(route('game.customization')); ?>" class="type-nav-link">
                    <i class="fas fa-paint-brush"></i>
                    Personnalisation
                </a>
            </div>

        </div>

        <!-- Construction queue removed but keep timer for completion -->
        <div style="display: none;"></div>

        <!-- Aperçu des ressources possédées -->
        <div class="resource-summary">
            <div class="resource-summary-title">
                <i class="fas fa-coins"></i>
                Ressources possédées
            </div>
            <div class="resource-summary-list">
                <?php $__currentLoopData = $planet->resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="resource-chip">
                        <?php if($pr->resource->icon): ?>
                            <img src="<?php echo e(asset('images/resources/' . $pr->resource->icon)); ?>" alt="<?php echo e($pr->resource->name); ?>" class="resource-icon" style="width:20px;height:20px;margin-right:6px;" />
                        <?php else: ?>
                            <i class="fas fa-coins" style="margin-right:6px;"></i>
                        <?php endif; ?>
                        <span class="resource-name" style="margin-right:6px;opacity:0.85;"><?php echo e(ucfirst($pr->resource->name)); ?></span>
                        <span class="resource-amount" style="font-weight:600;"><?php echo e($this->formatNumber($pr->current_amount)); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <?php if($type === 'equip'): ?>
            <!-- Gestion d'équipe -->
                <div class="equip-card">
                    <div class="equip-card-header">
                        <div class="equip-card-title">
                            <i class="fas fa-users-gear"></i>
                            Gestion des équipes d’attaque
                        </div>
                        <div class="equip-card-subtitle">Créez des équipes terrestres ou spatiales avec vos unités/vaisseaux</div>
                        <div class="equip-stats">
                            <span class="equip-stat"><i class="fas fa-hashtag"></i> Actuelles: <?php echo e($equipCountTotal); ?></span>
                            <span class="equip-stat"><i class="fas fa-bullseye"></i> Limite: <?php echo e($equipMaxLimit); ?></span>
                            <span class="equip-stat earth"><i class="fas fa-person-rifle"></i> Terrestres: <?php echo e($equipCountEarth); ?></span>
                            <span class="equip-stat spatial"><i class="fas fa-rocket"></i> Spatiales: <?php echo e($equipCountSpatial); ?></span>
                        </div>
                    </div>

                <div class="equip-content">
                    <!-- Formulaire de création/édition -->
                    <div class="equip-form">
                        <div class="equip-form-row">
                            <div class="equip-field">
                                <select class="equip-select" wire:model.live="equipCategory">
                                    <option value="earth">Terrestre</option>
                                    <option value="spatial">Spatial</option>
                                </select>
                            </div>
                            <div class="equip-field">
                                <input type="text" wire:model.live="equipLabel" placeholder="Nom de l’équipe">
                            </div>
                            <!-- Index supprimé: désormais attribué automatiquement -->
                        </div>
                        <div class="equip-form-row">
                            <div class="equip-field full">
                                <input type="text" wire:model.live="equipNotes" placeholder="Notes facultatives">
                            </div>
                        </div>

                        <div class="equip-payload">
                            <?php if($equipCategory === 'earth'): ?>
                                <div class="equip-payload-title"><i class="fas fa-person-rifle"></i> Unités terrestres</div>
                                <div class="equip-payload-grid">
                                    <?php $__currentLoopData = $planet->units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planetUnit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="payload-row">
                                            <div class="payload-label">
                                                <img src="<?php echo e(asset('images/units/' . ($planetUnit->unit->icon ?? 'unit.png'))); ?>" alt="<?php echo e($planetUnit->unit->label); ?>">
                                                <span><?php echo e($planetUnit->unit->label); ?></span>
                                                <small>Dispo: <?php echo e($planetUnit->quantity); ?></small>
                                            </div>
                                            <div class="payload-input">
                                                <input type="number"
                                                       min="0"
                                                       max="<?php echo e($planetUnit->quantity); ?>"
                                                       wire:model.live="equipPayloadUnits.<?php echo e($planetUnit->unit->id); ?>"
                                                       placeholder="0">
                                                <button type="button" class="payload-btn max" wire:click="setMaxUnit(<?php echo e($planetUnit->unit->id); ?>)">Max</button>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php else: ?>
                                <div class="equip-payload-title"><i class="fas fa-rocket"></i> Vaisseaux spatiaux</div>
                                <div class="equip-payload-grid">
                                    <?php $__currentLoopData = $planet->ships; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planetShip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="payload-row">
                                            <div class="payload-label">
                                                <img src="<?php echo e(asset('images/ships/' . ($planetShip->ship->icon ?? 'ship.png'))); ?>" alt="<?php echo e($planetShip->ship->label); ?>">
                                                <span><?php echo e($planetShip->ship->label); ?></span>
                                                <small>Dispo: <?php echo e($planetShip->quantity); ?></small>
                                            </div>
                                            <div class="payload-input">
                                                <input type="number"
                                                       min="0"
                                                       max="<?php echo e($planetShip->quantity); ?>"
                                                       wire:model.live="equipPayloadShips.<?php echo e($planetShip->ship->id); ?>"
                                                       placeholder="0">
                                                <button type="button" class="payload-btn max" wire:click="setMaxShip(<?php echo e($planetShip->ship->id); ?>)">Max</button>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="equip-actions">
                            <?php if($equipEditId): ?>
                                <button class="equip-btn primary" wire:click="saveTeam"><i class="fas fa-save"></i> Mettre à jour</button>
                                <button class="equip-btn" wire:click="startNewTeam('<?php echo e($equipCategory); ?>')"><i class="fas fa-plus"></i> Nouvelle équipe</button>
                            <?php else: ?>
                                <button class="equip-btn primary" wire:click="saveTeam"><i class="fas fa-save"></i> Créer l’équipe</button>
                                <button class="equip-btn" wire:click="startNewTeam('<?php echo e($equipCategory); ?>')"><i class="fas fa-broom"></i> Réinitialiser</button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Liste des équipes existantes -->
                    <div class="equip-list">
                        <div class="equip-list-title"><i class="fas fa-list"></i> Équipes enregistrées</div>
                        <table class="equip-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Label</th>
                                    <th>Catégorie</th>
                                    <th>Actif</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $equipTeams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($team['team_index']); ?></td>
                                        <td><?php echo e($team['label']); ?></td>
                                        <td>
                                            <span class="equip-badge <?php echo e($team['category'] === 'earth' ? 'earth' : 'spatial'); ?>">
                                                <?php echo e($team['category'] === 'earth' ? 'Terrestre' : 'Spatial'); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <button class="equip-badge <?php echo e($team['is_active'] ? 'active' : 'inactive'); ?>" wire:click="toggleTeamActive(<?php echo e($team['id']); ?>)">
                                                <?php echo e($team['is_active'] ? 'Actif' : 'Inactif'); ?>

                                            </button>
                                        </td>
                                        <td>
                                            <button class="equip-btn" wire:click="editTeam(<?php echo e($team['id']); ?>)"><i class="fas fa-pen"></i> Éditer</button>
                                            <button class="equip-btn danger" wire:click="deleteTeam(<?php echo e($team['id']); ?>)"><i class="fas fa-trash"></i> Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" style="text-align:center; opacity: 0.7;">Aucune équipe pour l’instant.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Grille de bâtiments -->
            <div class="buildings-grid">
            <?php $__currentLoopData = $buildings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $building): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="building-card">
                    <!-- En-tête de la carte -->
                    <div class="building-card-header" wire:click="openBuildingModal(<?php echo e($building['id']); ?>)">
                        <?php if($building['icon']): ?>
                            <?php
                                $iconPath = match($type) {
                                    'building' => 'buildings',
                                    'unit' => 'units',
                                    'ship' => 'ships',
                                    'defense' => 'defenses',
                                    default => 'buildings'
                                };
                            ?>
                            <img src="<?php echo e(asset('images/' . $iconPath . '/' . $building['icon'])); ?>" 
                                 alt="<?php echo e($building['label']); ?>" 
                                 class="building-image">
                        <?php else: ?>
                            <?php
                                $iconClass = match($type) {
                                    'building' => 'fas fa-building',
                                    'unit' => 'fas fa-users',
                                    'ship' => 'fas fa-rocket',
                                    'defense' => 'fas fa-shield-alt',
                                    default => 'fas fa-building'
                                };
                            ?>
                            <div class="building-image" style="background: linear-gradient(135deg, #1e293b, #334155); display: flex; align-items: center; justify-content: center;">
                                <i class="<?php echo e($iconClass); ?>" style="font-size: 3rem; color: var(--stargate-primary);"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="building-level">
                            <?php if($building['is_quantity_based']): ?>
                                <i class="fas fa-cubes"></i>
                                Quantité <?php echo e($building['quantity']); ?>

                            <?php else: ?>
                                <i class="fas fa-layer-group"></i>
                                Niveau <?php echo e($building['level']); ?>

                            <?php endif; ?>
                        </div>
                
                    </div>

                    <!-- Contenu de la carte -->
                    <div class="building-card-content">
                        <h3 class="building-name">
                            <i class="fas fa-<?php echo e($building['category'] === 'resource' ? 'coins' : ($building['category'] === 'military' ? 'shield-alt' : 'cog')); ?>"></i>
                            <?php echo e($building['label']); ?>

                        </h3>
                        
                        <?php if($building['is_quantity_based']): ?>
                            <!-- Contrôles de quantité pour unités/défenses/vaisseaux -->
                            <div class="quantity-controls">
                                <?php
                                    $currentQuantity = (int) $this->getQuantity($building['id']);
                                ?>
                                <button type="button" wire:click="setQuantity(<?php echo e($building['id']); ?>, <?php echo e(max(1, intval($currentQuantity) - 1)); ?>)" class="quantity-btn minus" <?php echo e($currentQuantity <= 1 ? 'disabled' : ''); ?>>
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" 
                                       id="quantity-<?php echo e($building['id']); ?>" 
                                       wire:model.live.debounce.150ms="quantities.<?php echo e($building['id']); ?>" 
                                       min="1" 
                                       max="999" 
                                       class="quantity-input"
                                       placeholder="Quantité">
                                <button type="button" wire:click="setQuantity(<?php echo e($building['id']); ?>, <?php echo e(intval($currentQuantity) + 1); ?>)" class="quantity-btn plus" <?php echo e($currentQuantity >= 999 ? 'disabled' : ''); ?>>
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        <?php endif; ?>

                        <!-- Informations rapides -->
                        <div class="building-info">
                            <div class="building-cost">
                                <div class="cost-label">Coût</div>
                                <div class="cost-values">
                                    <?php if(($building['is_quantity_based'] || $building['level'] < $building['max_level']) && count($building['costs']) > 0): ?>
                                        <?php $__currentLoopData = $building['costs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resourceName => $costData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $currentQuantity = (int) $this->getQuantity($building['id']);
                                                $cost = $costData['amount'];
                                                $totalCost = $building['is_quantity_based'] ? $cost * $currentQuantity : $cost;
                                                $hasEnough = ($planetResources[$resourceName] ?? 0) >= $totalCost;
                                            ?>
                                            <div class="cost-item <?php echo e($hasEnough ? '' : 'insufficient'); ?>">
                                                <?php if($costData['icon']): ?>
                                                    <img src="<?php echo e(asset('images/resources/' . $costData['icon'])); ?>" 
                                                         alt="<?php echo e($resourceName); ?>" 
                                                         class="resource-icon" 
                                                         style="width: 20px; height: 20px; margin-right: 5px;">
                                                <?php else: ?>
                                                    <i class="fas fa-coins"></i>
                                                <?php endif; ?>
                                                <span><?php echo e($this->formatNumber($totalCost)); ?></span>
                                                <?php if($building['is_quantity_based'] && $currentQuantity > 1): ?>
                                                    <small>(<?php echo e($this->formatNumber($cost)); ?> × <?php echo e($currentQuantity); ?>)</small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php elseif(!$building['is_quantity_based'] && $building['level'] >= $building['max_level']): ?>
                                        <div class="cost-item">
                                            <i class="fas fa-check"></i>
                                            <span>Max</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="building-time">
                                <div class="time-label">Temps</div>
                                <div class="time-value">
                                    <?php if($building['is_quantity_based'] || $building['level'] < $building['max_level']): ?>
                                        <i class="fas fa-clock"></i>
                                        <?php echo e($this->formatTime($building['build_time'])); ?>

                                        <?php if($building['is_quantity_based'] && $this->getQuantity($building['id']) > 1): ?>
                                            <small>(par unité)</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <i class="fas fa-check"></i>
                                        Max
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="building-action">
                            <?php if($building['is_constructing']): ?>
                            <button class="btn-upgrade" disabled>
                                <i class="fas fa-hammer"></i>
                                En cours de construction
                            </button>
                            <?php elseif(!$building['is_quantity_based'] && $building['level'] >= $building['max_level']): ?>
                                <button class="btn-upgrade" disabled>
                                    <i class="fas fa-check"></i>
                                    Niveau maximum
                                </button>
                            <?php elseif($building['has_insufficient_resources']): ?>
                                <button class="btn-upgrade" disabled>
                                    <i class="fas fa-coins"></i>
                                    Ressources insuffisantes
                                </button>
                            <?php elseif($building['has_insufficient_fields']): ?>
                                <button class="btn-upgrade" disabled>
                                    <i class="fas fa-map"></i>
                                    Plus de place
                                </button>
                            <?php elseif($building['can_upgrade']): ?>
                                <button class="btn-upgrade" 
                                        wire:click.stop="upgradeBuilding(<?php echo e($building['id']); ?>)"
                                        wire:loading.attr="disabled"
                                        wire:target="upgradeBuilding">
                                    <?php if($building['is_quantity_based']): ?>
                                        <i class="fas fa-plus"></i>
                                        Construire
                                        <?php if($this->getQuantity($building['id']) > 1): ?>
                                            (<?php echo e($this->getQuantity($building['id'])); ?>)
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <i class="fas fa-arrow-up"></i>
                                        Améliorer
                                    <?php endif; ?>
                                </button>
                            <?php else: ?>
                                <button class="btn-upgrade" disabled>
                                    <i class="fas fa-lock"></i>
                                    Prérequis manquants
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>


</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/building.blade.php ENDPATH**/ ?>