<div x-data="{ activeTab: <?php if ((object) ('activeTab') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('activeTab'->value()); ?>')<?php echo e('activeTab'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('activeTab'); ?>')<?php endif; ?> }" page="trade" class="container mx-auto px-4 py-8">
    <div class="trade-header">
        <h1>Centre de Commerce</h1>
        <p>Échangez des ressources avec d'autres joueurs</p>
    </div>
    
    <div class="trade-tabs">
        <button @click="activeTab = 'create'" :class="{ 'active': activeTab === 'create' }" class="tab-button">
            <i class="fas fa-plus-circle"></i> Créer une offre
        </button>
        <button @click="activeTab = 'my-offers'" :class="{ 'active': activeTab === 'my-offers' }" class="tab-button">
            <i class="fas fa-list"></i> Mes offres
            <?php if(count($myOffers) > 0): ?>
                <span class="badge"><?php echo e(count($myOffers)); ?></span>
            <?php endif; ?>
        </button>
        <button @click="activeTab = 'available-offers'" :class="{ 'active': activeTab === 'available-offers' }" class="tab-button">
            <i class="fas fa-exchange-alt"></i> Offres disponibles
            <?php if(count($tradeOffers) > 0): ?>
                <span class="badge"><?php echo e(count($tradeOffers)); ?></span>
            <?php endif; ?>
        </button>
        <button @click="activeTab = 'history'" :class="{ 'active': activeTab === 'history' }" class="tab-button">
            <i class="fas fa-history"></i> Historique
        </button>
    </div>
    <?php if($planet): ?>
        <div class="trade-content">
            <!-- Section créer une offre -->
            <div class="trade-section" x-data="{ createOfferOpen: true }" x-show="$wire.activeTab === 'create'">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-plus-circle"></i>
                        Créer une offre
                    </h3>
                </div>
                <div class="create-offer-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="selectedResource">Ressource à offrir</label>
                            <select wire:model="selectedResource" id="selectedResource" class="form-select">
                                <option value="">Sélectionner une ressource</option>
                                <?php $__currentLoopData = $planetResources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resourceKey => $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($resourceKey); ?>"><?php echo e($resource['name']); ?> (<?php echo e(number_format($resource['current_amount'])); ?> disponible)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['selectedResource'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="form-group">
                            <label for="offerAmount">Quantité à offrir</label>
                            <input type="number" wire:model="offerAmount" id="offerAmount" class="form-input" min="1" placeholder="0">
                            <?php $__errorArgs = ['offerAmount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="requestedResource">Ressource demandée</label>
                            <select wire:model="requestedResource" id="requestedResource" class="form-select">
                                <option value="">Sélectionner une ressource</option>
                                <?php $__currentLoopData = $planetResources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resourceKey => $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($resourceKey); ?>"><?php echo e($resource['name']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['requestedResource'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="form-group">
                            <label for="requestedAmount">Quantité demandée</label>
                            <input type="number" wire:model="requestedAmount" id="requestedAmount" class="form-input" min="1" placeholder="0">
                            <?php $__errorArgs = ['requestedAmount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button wire:click="createOffer" class="btn btn-primary">
                            <i class="fas fa-handshake"></i>
                            Créer l'offre
                        </button>
                    </div>
                </div>
            </div>

            <!-- Section mes offres -->
            <div class="trade-section" x-show="$wire.activeTab === 'my-offers'">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-list"></i>
                        Mes offres
                    </h3>
                </div>
                
                <!-- Filtres et tri pour mes offres -->
                <div class="filters-container">
                    <div class="filter-group">
                        <label for="myOffersFilterResource">Filtrer par ressource:</label>
                        <select wire:model="myOffersFilterResource" id="myOffersFilterResource" class="form-select">
                            <option value="">Toutes les ressources</option>
                            <?php $__currentLoopData = $planetResources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resourceKey => $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($resourceKey); ?>"><?php echo e($resource['name']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="myOffersSortBy">Trier par:</label>
                        <select wire:model="myOffersSortBy" id="myOffersSortBy" class="form-select">
                            <option value="created_at">Date de création</option>
                            <option value="offered_amount">Quantité offerte</option>
                            <option value="requested_amount">Quantité demandée</option>
                            <option value="expires_at">Date d'expiration</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="myOffersSortDirection">Ordre:</label>
                        <select wire:model="myOffersSortDirection" id="myOffersSortDirection" class="form-select">
                            <option value="desc">Décroissant</option>
                            <option value="asc">Croissant</option>
                        </select>
                    </div>
                </div>
                
                <div class="my-offers-list">
                    <?php if(count($myOffers) > 0): ?>
                        <?php $__currentLoopData = $myOffers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $offer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="offer-card">
                                <div class="offer-header">
                                    <div class="offer-info">
                                        <span class="offer-planet"><?php echo e($offer['seller_planet']); ?></span>
                                        <span class="offer-time"><?php echo e($offer['time_remaining']); ?></span>
                                    </div>
                                    <?php if($offer['can_be_cancelled']): ?>
                                        <button wire:click="cancelOffer(<?php echo e($offer['id']); ?>)" class="btn btn-danger btn-sm">
                                            <i class="fas fa-times"></i>
                                            Annuler
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="offer-content">
                                    <div class="offer-resources">
                                        <div class="offered-resource">
                                            <div class="resource-icon">
                                                <img src="<?php echo e(asset('images/resources/' . $offer['offered_resource']['icon'])); ?>" alt="<?php echo e($offer['offered_resource']['name']); ?>">
                                            </div>
                                            <div class="resource-details">
                                                <span class="resource-name"><?php echo e($offer['offered_resource']['name']); ?></span>
                                                <span class="resource-amount"><?php echo e(number_format($offer['offered_resource']['amount'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="exchange-icon">
                                            <i class="fas fa-exchange-alt"></i>
                                        </div>
                                        <div class="requested-resource">
                                            <div class="resource-icon">
                                                <img src="<?php echo e(asset('images/resources/' . $offer['requested_resource']['icon'])); ?>" alt="<?php echo e($offer['requested_resource']['name']); ?>">
                                            </div>
                                            <div class="resource-details">
                                                <span class="resource-name"><?php echo e($offer['requested_resource']['name']); ?></span>
                                                <span class="resource-amount"><?php echo e(number_format($offer['requested_resource']['amount'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <!-- Pagination pour mes offres -->
                        <div class="pagination-container">
                            <div class="pagination-info">
                            Page <?php echo e($myOffersPage); ?> sur <?php echo e($this->getMyOffersMaxPage()); ?>

                        </div>
                        <div class="pagination-controls">
                            <button wire:click="previousMyOffersPage" class="btn btn-secondary btn-sm" <?php echo e($myOffersPage <= 1 ? 'disabled' : ''); ?>>
                                <i class="fas fa-chevron-left"></i>
                                Précédent
                            </button>
                            
                            <?php for($i = 1; $i <= $this->getMyOffersMaxPage(); $i++): ?>
                                <button wire:click="goToMyOffersPage(<?php echo e($i); ?>)" class="btn <?php echo e($myOffersPage == $i ? 'btn-primary' : 'btn-secondary'); ?> btn-sm">
                                    <?php echo e($i); ?>

                                </button>
                            <?php endfor; ?>
                            
                            <button wire:click="nextMyOffersPage" class="btn btn-secondary btn-sm" <?php echo e($myOffersPage >= $this->getMyOffersMaxPage() ? 'disabled' : ''); ?>>
                                Suivant
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-info-circle"></i>
                            <p>Vous n'avez pas d'offres actives.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section offres disponibles -->
            <div class="trade-section" x-show="$wire.activeTab === 'available-offers'">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-store"></i>
                        Offres disponibles
                    </h3>
                </div>
                
                <!-- Filtres et tri pour les offres disponibles -->
                <div class="filters-container">
                    <div class="filter-group">
                        <label for="availableOffersFilterResource">Ressource offerte:</label>
                        <select wire:model="availableOffersFilterResource" id="availableOffersFilterResource" class="form-select">
                            <option value="">Toutes les ressources</option>
                            <?php $__currentLoopData = $planetResources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resourceKey => $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($resourceKey); ?>"><?php echo e($resource['name']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="availableOffersFilterRequestedResource">Ressource demandée:</label>
                        <select wire:model="availableOffersFilterRequestedResource" id="availableOffersFilterRequestedResource" class="form-select">
                            <option value="">Toutes les ressources</option>
                            <?php $__currentLoopData = $planetResources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resourceKey => $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($resourceKey); ?>"><?php echo e($resource['name']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="tradeOffersSortBy">Trier par:</label>
                        <select wire:model="tradeOffersSortBy" id="tradeOffersSortBy" class="form-select">
                            <option value="created_at">Date de création</option>
                            <option value="offered_amount">Quantité offerte</option>
                            <option value="requested_amount">Quantité demandée</option>
                            <option value="exchange_ratio">Ratio d'échange</option>
                            <option value="expires_at">Date d'expiration</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="tradeOffersSortDirection">Ordre:</label>
                        <select wire:model="tradeOffersSortDirection" id="tradeOffersSortDirection" class="form-select">
                            <option value="desc">Décroissant</option>
                            <option value="asc">Croissant</option>
                        </select>
                    </div>
                </div>
                
                <div class="available-offers-list">
                    <?php if(count($tradeOffers) > 0): ?>
                        <?php $__currentLoopData = $tradeOffers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $offer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="offer-card">
                                <div class="offer-header">
                                    <div class="offer-info">
                                        <span class="offer-seller"><?php echo e($offer['seller_name']); ?></span>
                                        <span class="offer-time"><?php echo e($offer['time_remaining']); ?></span>
                                    </div>
                                </div>
                                <div class="offer-content">
                                    <div class="offer-resources">
                                        <div class="offered-resource">
                                            <div class="resource-icon">
                                                <img src="<?php echo e(asset('images/resources/' . $offer['offered_resource']['icon'])); ?>" alt="<?php echo e($offer['offered_resource']['name']); ?>">
                                            </div>
                                            <div class="resource-details">
                                                <span class="resource-name"><?php echo e($offer['offered_resource']['name']); ?></span>
                                                <span class="resource-amount"><?php echo e(number_format($offer['offered_resource']['amount'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="exchange-icon">
                                            <i class="fas fa-exchange-alt"></i>
                                        </div>
                                        <div class="requested-resource">
                                            <div class="resource-icon">
                                                <img src="<?php echo e(asset('images/resources/' . $offer['requested_resource']['icon'])); ?>" alt="<?php echo e($offer['requested_resource']['name']); ?>">
                                            </div>
                                            <div class="resource-details">
                                                <span class="resource-name"><?php echo e($offer['requested_resource']['name']); ?></span>
                                                <span class="resource-amount"><?php echo e(number_format($offer['requested_resource']['amount'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="offer-actions">
                                        <button wire:click="acceptOffer(<?php echo e($offer['id']); ?>)" class="btn btn-success">
                                            <i class="fas fa-check"></i>
                                            Accepter l'échange
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-info-circle"></i>
                            <p>Aucune offre disponible pour le moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination pour les offres disponibles -->
                <div class="pagination-container">
                    <div class="pagination-info">
                        Page <?php echo e($tradeOffersPage); ?> sur <?php echo e($this->getTradeOffersMaxPage()); ?>

                    </div>
                    <div class="pagination-controls">
                        <button wire:click="previousTradeOffersPage" class="btn btn-secondary btn-sm" <?php echo e($tradeOffersPage <= 1 ? 'disabled' : ''); ?>>
                            <i class="fas fa-chevron-left"></i>
                            Précédent
                        </button>
                        
                        <?php for($i = 1; $i <= $this->getTradeOffersMaxPage(); $i++): ?>
                            <button wire:click="goToTradeOffersPage(<?php echo e($i); ?>)" class="btn <?php echo e($tradeOffersPage == $i ? 'btn-primary' : 'btn-secondary'); ?> btn-sm">
                                <?php echo e($i); ?>

                            </button>
                        <?php endfor; ?>
                        
                        <button wire:click="nextTradeOffersPage" class="btn btn-secondary btn-sm" <?php echo e($tradeOffersPage >= $this->getTradeOffersMaxPage() ? 'disabled' : ''); ?>>
                            Suivant
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Section historique des transactions -->
            <div class="trade-section" x-show="$wire.activeTab === 'history'" x-cloak>
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-history"></i>
                        Historique des transactions
                    </h3>
                </div>
                
                <!-- Filtres pour l'historique -->
                <div class="filters-container">
                    <div class="filter-group">
                        <label for="historyFilterType">Type:</label>
                        <select wire:model="historyFilterType" id="historyFilterType" class="form-select">
                            <option value="all">Toutes les transactions</option>
                            <option value="sent">Offres envoyées</option>
                            <option value="received">Offres reçues</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="historyFilterStatus">Statut:</label>
                        <select wire:model="historyFilterStatus" id="historyFilterStatus" class="form-select">
                            <option value="all">Tous les statuts</option>
                            <option value="completed">Complétées</option>
                            <option value="cancelled">Annulées</option>
                            <option value="expired">Expirées</option>
                        </select>
                    </div>
                </div>
                
                <div class="trade-history-list">
                    <?php if(count($tradeHistory) > 0): ?>
                        <?php $__currentLoopData = $tradeHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="history-card <?php echo e($trade['status']); ?>">
                                <div class="history-header">
                                    <div class="history-info">
                                        <span class="history-date"><?php echo e($trade['created_at']); ?></span>
                                        <span class="history-status"><?php echo e($trade['status_label']); ?></span>
                                    </div>
                                    <div class="history-participants">
                                        <span class="history-seller"><?php echo e($trade['seller_name']); ?></span>
                                        <i class="fas fa-arrow-right"></i>
                                        <span class="history-buyer"><?php echo e($trade['buyer_name']); ?></span>
                                    </div>
                                </div>
                                <div class="history-content">
                                    <div class="history-resources">
                                        <div class="offered-resource">
                                            <div class="resource-icon">
                                                <img src="<?php echo e(asset('images/resources/' . $trade['offered_resource']['icon'])); ?>" alt="<?php echo e($trade['offered_resource']['name']); ?>">
                                            </div>
                                            <div class="resource-details">
                                                <span class="resource-name"><?php echo e($trade['offered_resource']['name']); ?></span>
                                                <span class="resource-amount"><?php echo e(number_format($trade['offered_resource']['amount'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="exchange-icon">
                                            <i class="fas fa-exchange-alt"></i>
                                        </div>
                                        <div class="requested-resource">
                                            <div class="resource-icon">
                                                <img src="<?php echo e(asset('images/resources/' . $trade['requested_resource']['icon'])); ?>" alt="<?php echo e($trade['requested_resource']['name']); ?>">
                                            </div>
                                            <div class="resource-details">
                                                <span class="resource-name"><?php echo e($trade['requested_resource']['name']); ?></span>
                                                <span class="resource-amount"><?php echo e(number_format($trade['requested_resource']['amount'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        
                        <!-- Pagination pour l'historique -->
                        <div class="pagination-container">
                            <div class="pagination-info">
                                Page <?php echo e($historyPage); ?> sur <?php echo e($this->getHistoryMaxPage()); ?>

                            </div>
                            <div class="pagination-controls">
                                <button wire:click="previousHistoryPage" class="btn btn-secondary btn-sm" <?php echo e($historyPage <= 1 ? 'disabled' : ''); ?>>
                                    <i class="fas fa-chevron-left"></i>
                                    Précédent
                                </button>
                                
                                <?php for($i = 1; $i <= $this->getHistoryMaxPage(); $i++): ?>
                                    <button wire:click="goToHistoryPage(<?php echo e($i); ?>)" class="btn <?php echo e($historyPage == $i ? 'btn-primary' : 'btn-secondary'); ?> btn-sm">
                                        <?php echo e($i); ?>

                                    </button>
                                <?php endfor; ?>
                                
                                <button wire:click="nextHistoryPage" class="btn btn-secondary btn-sm" <?php echo e($historyPage >= $this->getHistoryMaxPage() ? 'disabled' : ''); ?>>
                                    Suivant
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-info-circle"></i>
                            <p>Aucune transaction dans l'historique.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="no-planet-selected">
            <i class="fas fa-globe"></i>
            <p>Veuillez sélectionner une planète pour accéder au commerce</p>
        </div>
    <?php endif; ?>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/trade.blade.php ENDPATH**/ ?>