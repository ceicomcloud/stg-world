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
            @foreach($bunkerResources as $index => $resource)
            <div class="bunker-resource">
                <div class="resource-header">
                    <img src="/images/resources/{{ $resource['icon'] }}" alt="{{ $resource['name'] }}" class="resource-icon">
                    <div class="resource-name">{{ $resource['name'] }}</div>
                    <div class="resource-amount">{{ number_format($resource['stored_amount']) }}</div>
                </div>
                
                <div class="storage-bar">
                    <div class="storage-fill" style="width: {{ $resource['percentage'] }}%"></div>
                </div>
                
                <div class="storage-info">
                    <div>Stocké: {{ number_format($resource['stored_amount']) }}</div>
                    <div class="storage-capacity">Capacité globale: {{ number_format($resource['max_storage']) }}</div>
                </div>
                
                <div class="resource-actions">
                    <div class="action-group">
                        <div class="quantity-control">
                            <input type="number" wire:model="storeAmounts.{{ $resource['id'] }}" wire:change="updateStoreAmount({{ $resource['id'] }}, $event.target.value)" min="0" max="{{ isset($planetResources[$resource['resource_id']]) ? min($planetResources[$resource['resource_id']]['current_amount'], $resource['available_space']) : 0 }}" class="quantity-input" placeholder="Quantité">
                            <button class="max-button" wire:click="setMaxStoreAmount({{ $resource['id'] }})" title="Quantité maximale">Max</button>
                        </div>
                        <button class="action-button store-button" wire:click="storeResource({{ $resource['id'] }})">
                            <i class="fas fa-arrow-down"></i> Stocker
                        </button>
                    </div>
                    
                    <div class="action-group">
                        <div class="quantity-control">
                            <input type="number" wire:model="retrieveAmounts.{{ $resource['id'] }}" wire:change="updateRetrieveAmount({{ $resource['id'] }}, $event.target.value)" min="0" max="{{ $resource['stored_amount'] }}" class="quantity-input" placeholder="Quantité">
                            <button class="max-button" wire:click="setMaxRetrieveAmount({{ $resource['id'] }})" title="Quantité maximale">Max</button>
                        </div>
                        <button class="action-button retrieve-button" wire:click="retrieveResource({{ $resource['id'] }})">
                            <i class="fas fa-arrow-up"></i> Récupérer
                        </button>
                    </div>
                </div>
                
                <div class="planet-resource-info">
                    <div class="planet-resource-label">Sur la planète:</div>
                    <div class="planet-resource-amount">{{ isset($planetResources[$resource['resource_id']]) ? number_format($planetResources[$resource['resource_id']]['current_amount']) : 0 }}</div>
                </div>
            </div>
            @endforeach
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
                <strong>Centre de Commandement Niveau {{ $commandCenterLevel }}</strong><br>
                Capacité de stockage actuelle (globale): <strong>{{ number_format($totalBunkerCapacity) }}</strong><br>
                Utilisé (toutes ressources): <strong>{{ number_format($usedBunkerStorage) }}</strong><br>
                Disponible (global): <strong>{{ number_format($globalAvailableStorage) }}</strong><br>
                @if($nextLevelBoost > 0)
                    Prochain niveau: <strong>+{{ number_format($nextLevelBoost) }}</strong> de capacité supplémentaire
                @endif
            </div>
        </div>
        
        <!-- Historique des transactions -->
        <div class="bunker-transactions">
            <div class="transactions-title">
                <i class="fas fa-history"></i>
                Historique des Transactions
            </div>
            
            @if(count($recentTransactions) > 0)
                <div class="transactions-list">
                    @foreach($recentTransactions as $transaction)
                        <div class="transaction-item {{ $transaction['type'] == 'store' ? 'transaction-store' : 'transaction-retrieve' }}">
                            <div class="transaction-icon">
                                <i class="fas {{ $transaction['type_icon'] }}"></i>
                            </div>
                            <div class="transaction-resource">
                                <img src="/images/resources/{{ $transaction['resource_icon'] }}" alt="{{ $transaction['resource_name'] }}" class="transaction-resource-icon">
                                <span>{{ $transaction['resource_name'] }}</span>
                            </div>
                            <div class="transaction-details">
                                <div class="transaction-type">{{ $transaction['type_formatted'] }}</div>
                                <div class="transaction-amount">{{ number_format($transaction['amount']) }}</div>
                            </div>
                            <div class="transaction-time" title="{{ $transaction['created_at_formatted'] }}">
                                {{ $transaction['created_at'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination des transactions -->
                @if($totalPages > 1)
                    <div class="transactions-pagination">
                        <div class="pagination-info">Page {{ $currentPage }} sur {{ $totalPages }}</div>
                        <div class="pagination-controls">
                            <button 
                                wire:click="changePage({{ $currentPage - 1 }})"
                                class="pagination-button"
                                {{ $currentPage <= 1 ? 'disabled' : '' }}
                            >
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            
                            @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                                <button 
                                    wire:click="changePage({{ $i }})"
                                    class="pagination-button {{ $i == $currentPage ? 'active' : '' }}"
                                >
                                    {{ $i }}
                                </button>
                            @endfor
                            
                            <button 
                                wire:click="changePage({{ $currentPage + 1 }})"
                                class="pagination-button"
                                {{ $currentPage >= $totalPages ? 'disabled' : '' }}
                            >
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                @endif
            @else
                <div class="no-transactions">
                    Aucune transaction récente.
                </div>
            @endif
        </div>
    </div>
</div>