<div class="mission-info-modal">
    <!-- En-tête avec type et statut de mission -->
    <div class="mission-info-header">
        <div class="mission-info-type-icon mission-info-type-<?php echo e($missionData['type'] ?? 'default'); ?>">
            <i class="fas fa-<?php echo e($this->getMissionTypeIcon()); ?>"></i>
        </div>
        <div class="mission-info-details">
            <h2 class="mission-info-title"><?php echo e($missionData['type_label'] ?? 'Mission'); ?></h2>
            <div class="mission-info-status mission-info-status-<?php echo e($missionData['status'] ?? 'default'); ?>">
                <i class="fas fa-<?php echo e($this->getMissionStatusIcon()); ?>"></i>
                <span><?php echo e($missionData['status_label'] ?? 'Statut inconnu'); ?></span>
                <?php if(isset($missionData['time_remaining']) && in_array($missionData['status'], ['traveling', 'returning'])): ?>
                    <span class="mission-timer" data-end-time="<?php echo e($missionData['status'] === 'traveling' && isset($missionData['arrival_time']) ? $missionData['arrival_time']->unix() : (isset($missionData['return_time']) ? $missionData['return_time']->unix() : '')); ?>">
                        <?php echo e($missionData['time_remaining']); ?>

                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Informations sur l'itinéraire -->
    <div class="mission-info-section">
        <h3 class="mission-info-section-title">
            <i class="fas fa-route"></i>
            Itinéraire
        </h3>
        <div class="mission-info-route">
            <div class="mission-info-planet">
                <i class="fas fa-globe"></i>
                <span class="mission-info-planet-name"><?php echo e($missionData['from_planet']['name'] ?? 'Planète inconnue'); ?></span>
                <span class="mission-info-planet-coords">[<?php echo e($missionData['from_planet']['coordinates']['galaxy'] ?? 'N/A'); ?>:<?php echo e($missionData['from_planet']['coordinates']['system'] ?? 'N/A'); ?>:<?php echo e($missionData['from_planet']['coordinates']['position'] ?? 'N/A'); ?>]</span>
            </div>
            <div class="mission-info-direction">
                <i class="fas fa-long-arrow-alt-right"></i>
            </div>
            <div class="mission-info-planet">
                <i class="fas fa-globe"></i>
                <?php if(isset($missionData['to_planet'])): ?>
                    <span class="mission-info-planet-name"><?php echo e($missionData['to_planet']['name'] ?? 'Planète inconnue'); ?></span>
                <?php endif; ?>
                <span class="mission-info-planet-coords">[<?php echo e($missionData['to_coordinates']['galaxy'] ?? 'N/A'); ?>:<?php echo e($missionData['to_coordinates']['system'] ?? 'N/A'); ?>:<?php echo e($missionData['to_coordinates']['position'] ?? 'N/A'); ?>]</span>
            </div>
        </div>
        <div class="mission-info-times">
            <div class="mission-info-time">
                <i class="fas fa-calendar-day"></i>
                <span class="mission-info-time-label">Départ</span>
                <span class="mission-info-time-value"><?php echo e(isset($missionData['departure_time']) ? $missionData['departure_time']->format('d/m/Y H:i:s') : 'N/A'); ?></span>
            </div>
            <div class="mission-info-time">
                <i class="fas fa-calendar-check"></i>
                <span class="mission-info-time-label">Arrivée</span>
                <span class="mission-info-time-value"><?php echo e(isset($missionData['arrival_time']) ? $missionData['arrival_time']->format('d/m/Y H:i:s') : 'N/A'); ?></span>
            </div>
            <?php if(isset($missionData['return_time'])): ?>
                <div class="mission-info-time">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="mission-info-time-label">Retour</span>
                    <span class="mission-info-time-value"><?php echo e($missionData['return_time']->format('d/m/Y H:i:s')); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Vaisseaux -->
    <?php if(isset($missionData['ships']) && count($missionData['ships']) > 0): ?>
        <div class="mission-info-section">
            <h3 class="mission-info-section-title">
                <i class="fas fa-rocket"></i>
                Vaisseaux
            </h3>
            <div class="mission-info-ships">
                <?php $__currentLoopData = $missionData['ships']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $ship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        // Support both legacy and normalized payloads
                        $quantity = null;
                        $name = null;
                        $icon = null;
                        $type = null;
                        $templateId = null;

                        if (is_array($ship)) {
                            // Legacy: detailed array structure
                            if (isset($ship['quantity'])) {
                                $quantity = is_array($ship['quantity']) ? ($ship['quantity']['quantity'] ?? null) : $ship['quantity'];
                            }
                            $name = $ship['name'] ?? null;
                            $icon = $ship['icon'] ?? null;
                            $type = $ship['type'] ?? null;
                            $templateId = $ship['template_id'] ?? ($ship['id'] ?? null);
                        } else {
                            // Normalized: map of template_id => quantity
                            $templateId = $key;
                            $quantity = $ship;
                        }

                        // Backfill from template when needed
                        if ((!$name || !$icon || !$type) && $templateId) {
                            $tpl = \App\Models\Template\TemplateBuild::find($templateId);
                            if ($tpl) {
                                $name = $name ?? ($tpl->label ?? $tpl->name);
                                $icon = $icon ?? $tpl->icon;
                                $type = $type ?? $tpl->type;
                            }
                        }

                        $isUnit = ($type === \App\Models\Template\TemplateBuild::TYPE_UNIT);
                        $imgFolder = $isUnit ? 'images/units/' : 'images/ships/';
                    ?>
                    <div class="mission-info-ship">
                        <?php if(!empty($icon)): ?>
                            <img src="<?php echo e(asset($imgFolder . $icon)); ?>" alt="<?php echo e($name ?? 'Vaisseau'); ?>" class="mission-info-ship-icon">
                        <?php else: ?>
                            <i class="fas fa-rocket mission-info-ship-icon"></i>
                        <?php endif; ?>
                        <span class="mission-info-ship-name"><?php echo e($name ?? 'Unité/Vaisseau'); ?></span>
                        <?php if(is_numeric($quantity)): ?>
                            <span class="mission-info-ship-quantity">x<?php echo e(number_format($quantity)); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Ressources -->
    <?php if(isset($missionData['resources']) && count($missionData['resources']) > 0): ?>
        <div class="mission-info-section">
            <h3 class="mission-info-section-title">
                <i class="fas fa-boxes"></i>
                Ressources
            </h3>
            <div class="mission-info-resources">
                <?php $__currentLoopData = $missionData['resources']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(isset($resource['amount']) && $resource['amount'] > 0): ?>
                        <div class="mission-info-resource">
                            <img src="<?php echo e(asset('images/resources/' . ($resource['icon'] ?? 'default.png'))); ?>" alt="<?php echo e($resource['name'] ?? 'Ressource'); ?>" class="mission-info-resource-icon">
                            <span class="mission-info-resource-name"><?php echo e($resource['name'] ?? 'Ressource'); ?></span>
                            <span class="mission-info-resource-amount"><?php echo e(number_format($resource['amount'])); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Résultat de la mission -->
    <?php if(isset($missionData['result']) && count($missionData['result']) > 0): ?>
        <div class="mission-info-section">
            <h3 class="mission-info-section-title">
                <i class="fas fa-clipboard-list"></i>
                Résultat
            </h3>
            <div class="mission-info-result">
                <?php if(isset($missionData['result']['message'])): ?>
                    <div class="mission-info-result-message">
                        <?php echo e($missionData['result']['message']); ?>

                    </div>
                <?php endif; ?>
                
                <?php if(isset($missionData['result']['resources_found'])): ?>
                    <div class="mission-info-resources">
                        <h4>Ressources trouvées</h4>
                        <?php
                        // Récupérer tous les templates de ressources pour obtenir les noms
                        $templateResources = \App\Models\Template\TemplateResource::all()->keyBy('id');
                        ?>
                        
                        <?php $__currentLoopData = $missionData['result']['resources_found']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resourceId => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($amount > 0): ?>
                                <?php
                                $resourceName = isset($templateResources[$resourceId]) ? $templateResources[$resourceId]->name : 'resource';
                                $resourceDisplayName = isset($templateResources[$resourceId]) ? ($templateResources[$resourceId]->display_name ?? ucfirst($resourceName)) : 'Ressource';
                                $resourceIcon = $resourceName . '.png';
                                ?>
                                <div class="mission-info-resource">
                                    <img src="<?php echo e(asset('images/resources/' . $resourceIcon)); ?>" alt="<?php echo e($resourceDisplayName); ?>" class="mission-info-resource-icon">
                                    <span class="mission-info-resource-name"><?php echo e($resourceDisplayName); ?></span>
                                    <span class="mission-info-resource-amount"><?php echo e(number_format($amount)); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($missionData['result']['spy_report'])): ?>
                    <div class="mission-info-spy-report">
                        <h4>Rapport d'espionnage</h4>
                        <div class="mission-info-spy-details">
                            <?php echo $missionData['result']['spy_report']; ?>

                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($missionData['result']['battle_report'])): ?>
                    <div class="mission-info-battle-report">
                        <h4>Rapport de bataille</h4>
                        <div class="mission-info-battle-details">
                            <?php echo $missionData['result']['battle_report']; ?>

                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($missionData['result']['discoveries'])): ?>
                    <div class="mission-info-discoveries">
                        <h4>Découvertes</h4>
                        <?php $__currentLoopData = $missionData['result']['discoveries']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $discovery): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="mission-info-discovery">
                                <div class="mission-info-discovery-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="mission-info-discovery-details">
                                    <div class="mission-info-discovery-title">Découverte</div>
                                    <div class="mission-info-discovery-description"><?php echo e($discovery); ?></div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/modal/mission-info.blade.php ENDPATH**/ ?>