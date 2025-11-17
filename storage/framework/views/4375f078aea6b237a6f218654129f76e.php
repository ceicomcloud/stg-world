<div class="admin-payments">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-file-invoice-dollar"></i> Paiements</h1>
        <div class="admin-page-actions">
            <a class="admin-tab-button" href="<?php echo e(route('admin.jobs')); ?>">
                <i class="fas fa-tasks"></i> Jobs
            </a>
        </div>
    </div>

    <div class="admin-content-body">
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Commandes</h2>
                <p class="admin-card-subtitle">Liste des commandes utilisateurs (PayPal)</p>
            </div>
            <div class="admin-card-body">
                <div class="admin-filters-grid">
                    <div class="admin-search-input-wrapper">
                        <i class="fas fa-search admin-search-icon"></i>
                        <input type="text" class="admin-search-input" placeholder="Rechercher par utilisateur, ID, package, provider id" wire:model.live.debounce.300ms="search">
                        <?php if($search): ?>
                            <button class="admin-search-clear" wire:click="$set('search', '')">
                                <i class="fas fa-times"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="admin-filter">
                        <label>Status</label>
                        <select class="admin-select" wire:model.change="status">
                            <option value="">Tous</option>
                            <option value="pending">En attente</option>
                            <option value="paid">Payé</option>
                            <option value="failed">Échoué</option>
                            <option value="refunded">Remboursé</option>
                            <option value="canceled">Annulé</option>
                        </select>
                    </div>
                    <div class="admin-filter">
                        <label>Provider</label>
                        <select class="admin-select" wire:model.change="provider">
                            <option value="">Tous</option>
                            <option value="paypal">PayPal</option>
                        </select>
                    </div>
                </div>

                <?php if(count($orders) > 0): ?>
                    <div class="admin-table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Utilisateur</th>
                                    <th>Package</th>
                                    <th>Or</th>
                                    <th>Montant (€)</th>
                                    <th>Provider</th>
                                    <th>Provider ID</th>
                                    <th>Status</th>
                                    <th>Créé le</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($order->id); ?></td>
                                        <td>
                                            <?php if($order->user): ?>
                                                <?php echo e($order->user->name); ?>

                                                <div class="admin-table-subtext"><?php echo e($order->user->email); ?></div>
                                            <?php else: ?>
                                                <span class="admin-table-subtext">Utilisateur supprimé</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($order->package_key); ?></td>
                                        <td><?php echo e($order->gold_amount); ?></td>
                                        <td><?php echo e(number_format($order->amount_eur, 2)); ?></td>
                                        <td><?php echo e(ucfirst($order->provider)); ?></td>
                                        <td class="mono"><?php echo e($order->provider_order_id ?? '—'); ?></td>
                                        <td>
                                            <?php $st = $order->status; ?>
                                            <?php if($st === 'paid'): ?>
                                                <span class="admin-badge success">Payé</span>
                                            <?php elseif($st === 'pending'): ?>
                                                <span class="admin-badge primary">En attente</span>
                                            <?php elseif($st === 'failed'): ?>
                                                <span class="admin-badge danger">Échoué</span>
                                            <?php elseif($st === 'refunded'): ?>
                                                <span class="admin-badge warning">Remboursé</span>
                                            <?php elseif($st === 'canceled'): ?>
                                                <span class="admin-badge neutral">Annulé</span>
                                            <?php else: ?>
                                                <span class="admin-badge"><?php echo e($st); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($order->created_at?->format('d/m/Y H:i')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="admin-pagination-container">
                        <?php echo e($orders->links()); ?>

                    </div>
                <?php else: ?>
                    <div class="admin-empty-state">
                        <div class="admin-empty-state-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <h3>Aucune commande trouvée</h3>
                        <p>Essayez d’ajuster les filtres ou la recherche.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/admin/payments.blade.php ENDPATH**/ ?>