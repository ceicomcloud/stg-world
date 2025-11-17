<div page="managePlayers">
    <div class="managePlayers-container">
        <!-- En-tête de la page -->
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-user-cog"></i> Gestion du Joueur</h1>
            <p class="page-subtitle">Paramètres VIP, Boutique d'or et Historique des commandes</p>
        </div>
        <!-- Onglets alignés au visuel Commerce -->
        <div class="manage-players-tabs">
            <button wire:click="setActiveTab('home')" class="tab-button <?php echo e($activeTab === 'home' ? 'active' : ''); ?>">
                <i class="fas fa-home"></i>
                Accueil
            </button>
            <button wire:click="setActiveTab('shop')" class="tab-button <?php echo e($activeTab === 'shop' ? 'active' : ''); ?>">
                <i class="fas fa-store"></i>
                Boutique
            </button>
            <button wire:click="setActiveTab('transactions')" class="tab-button <?php echo e($activeTab === 'transactions' ? 'active' : ''); ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                Transactions
            </button>
        </div>

        <!-- Contenu des onglets -->
        <?php if($activeTab === 'home'): ?>
            <div class="management-section mb-2">
                <div class="section-header">
                    <h3><i class="fas fa-crown"></i> Statut VIP</h3>
                </div>
                <div class="items-list">
                    <div class="item-card vip-card">
                        <div class="item-info" style="width: 100%">
                            <div class="planet-description">
                                <p><strong>Avantages VIP:</strong></p>
                                <ul>
                                    <li>Jusqu’à 3 bâtiments en construction simultanée (au lieu de 1).</li>
                                    <li>Regroupement des ressources de l’empire vers une planète cible (Empire).</li>
                                    <li>Badge VIP affichable dans le classement et le profil joueur.</li>
                                    <li>Capacité de stockage des ressources +10% sur toutes les planètes.</li>
                                    <li>Cadre doré autour du nom dans le chat et le classement.</li>
                                    <li>
                                        Équipes d’attaque par planète: <?php echo e($maxPlanetEquipsNormal); ?> ➜ <strong><?php echo e($maxPlanetEquipsVip); ?></strong> (VIP)
                                    </li>
                                    <li>
                                        Favoris/Bookmarks maximum: <?php echo e($maxBookmarksNormal); ?> ➜ <strong><?php echo e($maxBookmarksVip); ?></strong> (VIP)
                                    </li>
                                </ul>
                                <p><strong>Coût:</strong> <?php echo e(number_format($vipCostGold)); ?> or pour 1 mois.</p>
                                <p><strong>Votre solde:</strong> <?php echo e(number_format($goldBalance)); ?> or.</p>
                            </div>
                            <div class="edit-actions" style="margin-top:0.5rem; display:flex; gap:0.5rem;">
                                <button class="btn btn-primary" wire:click="activateVipOneMonth" wire:loading.attr="disabled">
                                    <i class="fas fa-crown"></i> Activer VIP 1 mois
                                </button>
                                <button class="btn <?php echo e($vipBadgeEnabled ? 'btn-secondary' : 'btn-primary'); ?>" wire:click="toggleVipBadge" wire:loading.attr="disabled">
                                    <i class="fas fa-id-badge"></i> <?php echo e($vipBadgeEnabled ? 'Désactiver' : 'Activer'); ?> badge VIP (classement)
                                </button>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>

            <div class="management-section">
                <div class="section-header">
                    <h3><i class="fas fa-user-shield"></i> Confidentialité</h3>
                </div>
                <div class="items-list">
                    <div class="item-card">
                        <div class="item-info" style="width: 100%">
                            <div class="badge-toggle">
                                <div>
                                    <p><strong>Masquer le détail de mes points</strong></p>
                                    <p class="text-muted">Si activé, les autres joueurs verront uniquement vos points totaux dans le classement et le profil (RankingInfo).</p>
                                </div>
                                <div class="edit-actions" style="display:flex; align-items:center;">
                                    <button class="btn <?php echo e($hidePointsBreakdown ? 'btn-secondary' : 'btn-primary'); ?>" wire:click="saveHidePointsBreakdown" wire:loading.attr="disabled">
                                        <i class="fas fa-user-shield"></i> <?php echo e($hidePointsBreakdown ? 'Désactiver' : 'Activer'); ?>

                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif($activeTab === 'shop'): ?>
            <div class="management-section">
                <div class="section-header">
                    <h3><i class="fas fa-store"></i> Boutique d'or</h3>
                </div>
                <?php if(!$shopEnabled): ?>
                    <div class="alert alert-warning" style="margin-bottom:1rem;">
                        <i class="fas fa-exclamation-triangle"></i> La boutique est momentanément désactivée.
                    </div>
                <?php elseif($shopRewardRate > 1): ?>
                    <div class="alert alert-success" style="margin-bottom:1rem;">
                        <i class="fas fa-clock"></i> Happy Hours: +<?php echo e((int) round(($shopRewardRate - 1) * 100)); ?>% d'or sur vos achats !
                    </div>
                <?php endif; ?>
                <div class="shop-grid">
                    <?php $__currentLoopData = $shopPackages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $finalGold = (int) floor(($pkg['gold'] ?? 0) * ($shopRewardRate ?? 1));
                            $label = $pkg['label'] ?? strtoupper($key);
                            $isRecommended = !empty($pkg['recommended']);
                        ?>
                        <div class="shop-card <?php echo e($isRecommended ? 'recommended' : ''); ?>">
                            <?php if($isRecommended): ?>
                                <div class="shop-ribbon">Meilleur choix</div>
                            <?php endif; ?>
                            <?php if(($shopRewardRate ?? 1) > 1): ?>
                                <div class="shop-bonus-badge">+<?php echo e((int) round(($shopRewardRate - 1) * 100)); ?>% bonus</div>
                            <?php endif; ?>
                            <div class="pack-art">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="shop-info">
                                <div>
                                    <div class="shop-name">Pack <?php echo e($label); ?></div>
                                    <div class="shop-gold">
                                        <?php echo e(number_format($pkg['gold'])); ?> or
                                        <?php if(($shopRewardRate ?? 1) > 1): ?>
                                            <span class="text-muted" style="margin-left:0.25rem;">➜</span>
                                            <strong><?php echo e(number_format($finalGold)); ?> or</strong>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="shop-price"><i class="fas fa-euro-sign"></i> <?php echo e(number_format($pkg['eur'], 2)); ?> €</div>
                            </div>
                            <div class="edit-actions" style="margin-top:0.5rem;">
                                <button class="btn btn-primary" wire:click="createPaypalOrder('<?php echo e($key); ?>')" wire:loading.attr="disabled">
                                    <i class="fas fa-shopping-cart"></i> Acheter
                                </button>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php elseif($activeTab === 'transactions'): ?>
            <!-- Historique des transactions -->
            <div class="management-section">
                <div class="section-header">
                    <h3><i class="fas fa-file-invoice-dollar"></i> Historique des transactions</h3>
                </div>
                <?php
                    $statusLabels = [
                        'pending' => 'En attente',
                        'paid' => 'Payé',
                        'failed' => 'Échoué',
                        'canceled' => 'Annulé',
                    ];
                    $providers = [
                        'paypal' => 'PayPal',
                    ];
                ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Pack</th>
                                <th>Or</th>
                                <th>Prix (€)</th>
                                <th>Statut</th>
                                <th>Fournisseur</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $packLabel = $shopPackages[$order->package_key]['label'] ?? strtoupper($order->package_key ?? '#');
                                    $packGold = $shopPackages[$order->package_key]['gold'] ?? $order->gold_amount;
                                    $status = strtolower($order->status ?? 'pending');
                                    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                                    $providerLabel = $providers[$order->provider] ?? ucfirst($order->provider ?? '');
                                ?>
                                <tr>
                                    <td>#<?php echo e($order->id); ?></td>
                                    <td>Pack <?php echo e($packLabel); ?></td>
                                    <td><?php echo e(number_format($packGold)); ?></td>
                                    <td><?php echo e(number_format($order->amount_eur, 2)); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo e($status); ?>"><?php echo e($statusLabel); ?></span>
                                    </td>
                                    <td><?php echo e($providerLabel); ?></td>
                                    <td><?php echo e($order->created_at->format('d/m/Y H:i')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" style="font-style: italic;">Aucune transaction pour le moment.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>  
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/manage-players.blade.php ENDPATH**/ ?>