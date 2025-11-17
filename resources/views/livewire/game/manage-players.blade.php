<div page="managePlayers">
    <div class="managePlayers-container">
        <!-- En-tête de la page -->
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-user-cog"></i> Gestion du Joueur</h1>
            <p class="page-subtitle">Paramètres VIP, Boutique d'or et Historique des commandes</p>
        </div>
        <!-- Onglets alignés au visuel Commerce -->
        <div class="manage-players-tabs">
            <button wire:click="setActiveTab('home')" class="tab-button {{ $activeTab === 'home' ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                Accueil
            </button>
            <button wire:click="setActiveTab('shop')" class="tab-button {{ $activeTab === 'shop' ? 'active' : '' }}">
                <i class="fas fa-store"></i>
                Boutique
            </button>
            <button wire:click="setActiveTab('transactions')" class="tab-button {{ $activeTab === 'transactions' ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i>
                Transactions
            </button>
        </div>

        <!-- Contenu des onglets -->
        @if($activeTab === 'home')
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
                                        Équipes d’attaque par planète: {{ $maxPlanetEquipsNormal }} ➜ <strong>{{ $maxPlanetEquipsVip }}</strong> (VIP)
                                    </li>
                                    <li>
                                        Favoris/Bookmarks maximum: {{ $maxBookmarksNormal }} ➜ <strong>{{ $maxBookmarksVip }}</strong> (VIP)
                                    </li>
                                </ul>
                                <p><strong>Coût:</strong> {{ number_format($vipCostGold) }} or pour 1 mois.</p>
                                <p><strong>Votre solde:</strong> {{ number_format($goldBalance) }} or.</p>
                            </div>
                            <div class="edit-actions" style="margin-top:0.5rem; display:flex; gap:0.5rem;">
                                <button class="btn btn-primary" wire:click="activateVipOneMonth" wire:loading.attr="disabled">
                                    <i class="fas fa-crown"></i> Activer VIP 1 mois
                                </button>
                                <button class="btn {{ $vipBadgeEnabled ? 'btn-secondary' : 'btn-primary' }}" wire:click="toggleVipBadge" wire:loading.attr="disabled">
                                    <i class="fas fa-id-badge"></i> {{ $vipBadgeEnabled ? 'Désactiver' : 'Activer' }} badge VIP (classement)
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
                                    <button class="btn {{ $hidePointsBreakdown ? 'btn-secondary' : 'btn-primary' }}" wire:click="saveHidePointsBreakdown" wire:loading.attr="disabled">
                                        <i class="fas fa-user-shield"></i> {{ $hidePointsBreakdown ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($activeTab === 'shop')
            <div class="management-section">
                <div class="section-header">
                    <h3><i class="fas fa-store"></i> Boutique d'or</h3>
                </div>
                @if(!$shopEnabled)
                    <div class="alert alert-warning" style="margin-bottom:1rem;">
                        <i class="fas fa-exclamation-triangle"></i> La boutique est momentanément désactivée.
                    </div>
                @elseif($shopRewardRate > 1)
                    <div class="alert alert-success" style="margin-bottom:1rem;">
                        <i class="fas fa-clock"></i> Happy Hours: +{{ (int) round(($shopRewardRate - 1) * 100) }}% d'or sur vos achats !
                    </div>
                @endif
                <div class="shop-grid">
                    @foreach($shopPackages as $key => $pkg)
                        @php
                            $finalGold = (int) floor(($pkg['gold'] ?? 0) * ($shopRewardRate ?? 1));
                            $label = $pkg['label'] ?? strtoupper($key);
                            $isRecommended = !empty($pkg['recommended']);
                        @endphp
                        <div class="shop-card {{ $isRecommended ? 'recommended' : '' }}">
                            @if($isRecommended)
                                <div class="shop-ribbon">Meilleur choix</div>
                            @endif
                            @if(($shopRewardRate ?? 1) > 1)
                                <div class="shop-bonus-badge">+{{ (int) round(($shopRewardRate - 1) * 100) }}% bonus</div>
                            @endif
                            <div class="pack-art">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="shop-info">
                                <div>
                                    <div class="shop-name">Pack {{ $label }}</div>
                                    <div class="shop-gold">
                                        {{ number_format($pkg['gold']) }} or
                                        @if(($shopRewardRate ?? 1) > 1)
                                            <span class="text-muted" style="margin-left:0.25rem;">➜</span>
                                            <strong>{{ number_format($finalGold) }} or</strong>
                                        @endif
                                    </div>
                                </div>
                                <div class="shop-price"><i class="fas fa-euro-sign"></i> {{ number_format($pkg['eur'], 2) }} €</div>
                            </div>
                            <div class="edit-actions" style="margin-top:0.5rem;">
                                <button class="btn btn-primary" wire:click="createPaypalOrder('{{ $key }}')" wire:loading.attr="disabled">
                                    <i class="fas fa-shopping-cart"></i> Acheter
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($activeTab === 'transactions')
            <!-- Historique des transactions -->
            <div class="management-section">
                <div class="section-header">
                    <h3><i class="fas fa-file-invoice-dollar"></i> Historique des transactions</h3>
                </div>
                @php
                    $statusLabels = [
                        'pending' => 'En attente',
                        'paid' => 'Payé',
                        'failed' => 'Échoué',
                        'canceled' => 'Annulé',
                    ];
                    $providers = [
                        'paypal' => 'PayPal',
                    ];
                @endphp
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
                            @forelse($orders as $order)
                                @php
                                    $packLabel = $shopPackages[$order->package_key]['label'] ?? strtoupper($order->package_key ?? '#');
                                    $packGold = $shopPackages[$order->package_key]['gold'] ?? $order->gold_amount;
                                    $status = strtolower($order->status ?? 'pending');
                                    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                                    $providerLabel = $providers[$order->provider] ?? ucfirst($order->provider ?? '');
                                @endphp
                                <tr>
                                    <td>#{{ $order->id }}</td>
                                    <td>Pack {{ $packLabel }}</td>
                                    <td>{{ number_format($packGold) }}</td>
                                    <td>{{ number_format($order->amount_eur, 2) }}</td>
                                    <td>
                                        <span class="status-badge {{ $status }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td>{{ $providerLabel }}</td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="font-style: italic;">Aucune transaction pour le moment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>  
</div>