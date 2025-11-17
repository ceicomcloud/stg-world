<div page="bunker">
    <div class="bunker-container-page">
        <div class="bunker-header">
            <div class="bunker-title">
                <i class="fas fa-shield-alt"></i>
                Bunker de Protection
            </div>
        </div>

        <div class="bunker-description">
            Le bunker vous permet de protéger vos ressources contre les attaques ennemies. Les ressources stockées dans le bunker ne peuvent pas être pillées lors d'une attaque. Améliorez votre Centre de Commandement pour augmenter la capacité de stockage du bunker.
        </div>

        <div class="bunker-container">
            <?php $__currentLoopData = $bunkerResources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bunker-resource">
                <div class="resource-header">
                    <img src="/images/resources/<?php echo e($resource['icon']); ?>" alt="<?php echo e($resource['name']); ?>" class="resource-icon">
                    <div class="resource-name"><?php echo e($resource['name']); ?></div>
                    <div class="resource-amount"><?php echo e(number_format($resource['stored_amount'])); ?></div>
                </div>
                
                <div class="storage-bar">
                    <div class="storage-fill" style="width: <?php echo e($resource['percentage']); ?>%"></div>
                </div>
                
                <div class="storage-info">
                    <div>Stocké: <?php echo e(number_format($resource['stored_amount'])); ?></div>
                    <div class="storage-capacity">Capacité globale: <?php echo e(number_format($resource['max_storage'])); ?></div>
                </div>
                
                <div class="resource-actions">
                    <div class="action-group">
                        <div class="quantity-control">
                            <input type="number" wire:model="storeAmounts.<?php echo e($resource['id']); ?>" wire:change="updateStoreAmount(<?php echo e($resource['id']); ?>, $event.target.value)" min="0" max="<?php echo e(isset($planetResources[$resource['resource_id']]) ? min($planetResources[$resource['resource_id']]['current_amount'], $resource['available_space']) : 0); ?>" class="quantity-input" placeholder="Quantité">
                            <button class="max-button" wire:click="setMaxStoreAmount(<?php echo e($resource['id']); ?>)" title="Quantité maximale">Max</button>
                        </div>
                        <button class="action-button store-button" wire:click="storeResource(<?php echo e($resource['id']); ?>)">
                            <i class="fas fa-arrow-down"></i> Stocker
                        </button>
                    </div>
                    
                    <div class="action-group">
                        <div class="quantity-control">
                            <input type="number" wire:model="retrieveAmounts.<?php echo e($resource['id']); ?>" wire:change="updateRetrieveAmount(<?php echo e($resource['id']); ?>, $event.target.value)" min="0" max="<?php echo e($resource['stored_amount']); ?>" class="quantity-input" placeholder="Quantité">
                            <button class="max-button" wire:click="setMaxRetrieveAmount(<?php echo e($resource['id']); ?>)" title="Quantité maximale">Max</button>
                        </div>
                        <button class="action-button retrieve-button" wire:click="retrieveResource(<?php echo e($resource['id']); ?>)">
                            <i class="fas fa-arrow-up"></i> Récupérer
                        </button>
                    </div>
                </div>
                
                <div class="planet-resource-info">
                    <div class="planet-resource-label">Sur la planète:</div>
                    <div class="planet-resource-amount"><?php echo e(isset($planetResources[$resource['resource_id']]) ? number_format($planetResources[$resource['resource_id']]['current_amount']) : 0); ?></div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="bunker-info">
            <div class="bunker-info-title">
                <i class="fas fa-info-circle"></i>
                Informations sur le Bunker
            </div>
            <div class="bunker-info-content">
                Le bunker est une installation souterraine qui protège vos ressources des pillages ennemis. Plus votre Centre de Commandement est développé, plus la capacité de stockage de votre bunker est importante.
            </div>
            
            <div class="bunker-upgrade-info">
                <strong>Centre de Commandement Niveau <?php echo e($commandCenterLevel); ?></strong><br>
                Capacité de stockage actuelle (globale): <strong><?php echo e(number_format($totalBunkerCapacity)); ?></strong><br>
                Utilisé (toutes ressources): <strong><?php echo e(number_format($usedBunkerStorage)); ?></strong><br>
                Disponible (global): <strong><?php echo e(number_format($globalAvailableStorage)); ?></strong><br>
                <?php if($nextLevelBoost > 0): ?>
                    Prochain niveau: <strong>+<?php echo e(number_format($nextLevelBoost)); ?></strong> de capacité supplémentaire
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Historique des transactions -->
        <div class="bunker-transactions">
            <div class="transactions-title">
                <i class="fas fa-history"></i>
                Historique des Transactions
            </div>
            
            <?php if(count($recentTransactions) > 0): ?>
                <div class="transactions-list">
                    <?php $__currentLoopData = $recentTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="transaction-item <?php echo e($transaction['type'] == 'store' ? 'transaction-store' : 'transaction-retrieve'); ?>">
                            <div class="transaction-icon">
                                <i class="fas <?php echo e($transaction['type_icon']); ?>"></i>
                            </div>
                            <div class="transaction-resource">
                                <img src="/images/resources/<?php echo e($transaction['resource_icon']); ?>" alt="<?php echo e($transaction['resource_name']); ?>" class="transaction-resource-icon">
                                <span><?php echo e($transaction['resource_name']); ?></span>
                            </div>
                            <div class="transaction-details">
                                <div class="transaction-type"><?php echo e($transaction['type_formatted']); ?></div>
                                <div class="transaction-amount"><?php echo e(number_format($transaction['amount'])); ?></div>
                            </div>
                            <div class="transaction-time" title="<?php echo e($transaction['created_at_formatted']); ?>">
                                <?php echo e($transaction['created_at']); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                
                <!-- Pagination des transactions -->
                <?php if($totalPages > 1): ?>
                    <div class="transactions-pagination">
                        <div class="pagination-info">Page <?php echo e($currentPage); ?> sur <?php echo e($totalPages); ?></div>
                        <div class="pagination-controls">
                            <button 
                                wire:click="changePage(<?php echo e($currentPage - 1); ?>)"
                                class="pagination-button"
                                <?php echo e($currentPage <= 1 ? 'disabled' : ''); ?>

                            >
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            
                            <?php for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                <button 
                                    wire:click="changePage(<?php echo e($i); ?>)"
                                    class="pagination-button <?php echo e($i == $currentPage ? 'active' : ''); ?>"
                                >
                                    <?php echo e($i); ?>

                                </button>
                            <?php endfor; ?>
                            
                            <button 
                                wire:click="changePage(<?php echo e($currentPage + 1); ?>)"
                                class="pagination-button"
                                <?php echo e($currentPage >= $totalPages ? 'disabled' : ''); ?>

                            >
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-transactions">
                    Aucune transaction récente.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/bunker.blade.php ENDPATH**/ ?>