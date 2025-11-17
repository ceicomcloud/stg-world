<div class="admin-payments">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-file-invoice-dollar"></i> Paiements</h1>
        <div class="admin-page-actions">
            <a class="admin-tab-button" href="{{ route('admin.jobs') }}">
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
                        @if($search)
                            <button class="admin-search-clear" wire:click="$set('search', '')">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
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

                @if(count($orders) > 0)
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
                                @foreach($orders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>
                                            @if($order->user)
                                                {{ $order->user->name }}
                                                <div class="admin-table-subtext">{{ $order->user->email }}</div>
                                            @else
                                                <span class="admin-table-subtext">Utilisateur supprimé</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->package_key }}</td>
                                        <td>{{ $order->gold_amount }}</td>
                                        <td>{{ number_format($order->amount_eur, 2) }}</td>
                                        <td>{{ ucfirst($order->provider) }}</td>
                                        <td class="mono">{{ $order->provider_order_id ?? '—' }}</td>
                                        <td>
                                            @php $st = $order->status; @endphp
                                            @if($st === 'paid')
                                                <span class="admin-badge success">Payé</span>
                                            @elseif($st === 'pending')
                                                <span class="admin-badge primary">En attente</span>
                                            @elseif($st === 'failed')
                                                <span class="admin-badge danger">Échoué</span>
                                            @elseif($st === 'refunded')
                                                <span class="admin-badge warning">Remboursé</span>
                                            @elseif($st === 'canceled')
                                                <span class="admin-badge neutral">Annulé</span>
                                            @else
                                                <span class="admin-badge">{{ $st }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="admin-pagination-container">
                        {{ $orders->links() }}
                    </div>
                @else
                    <div class="admin-empty-state">
                        <div class="admin-empty-state-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <h3>Aucune commande trouvée</h3>
                        <p>Essayez d’ajuster les filtres ou la recherche.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>