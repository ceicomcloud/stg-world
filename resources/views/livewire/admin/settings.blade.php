<div class="admin-settings">
    <div class="admin-page-header">
        <h1>Paramètres du serveur</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button {{ $activeTab === 'list' ? 'active' : '' }}" wire:click="setActiveTab('list')">
                Liste des paramètres
            </button>
            <button class="admin-tab-button {{ $activeTab === 'create' ? 'active' : '' }}" wire:click="setActiveTab('create')">
                Créer un paramètre
            </button>
            @if($selectedConfig)
                <button class="admin-tab-button {{ $activeTab === 'edit' ? 'active' : '' }}" wire:click="setActiveTab('edit')">
                Modifier le paramètre
                </button>
            @endif
        </div>
    </div>

    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value">{{ $configStats['total'] }}</div>
                <div class="admin-stat-label">Paramètres au total</div>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value">{{ $configStats['active'] }}</div>
                <div class="admin-stat-label">Paramètres actifs</div>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value">{{ $configStats['inactive'] }}</div>
                <div class="admin-stat-label">Paramètres inactifs</div>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-card-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="admin-stat-card-content">
                <div class="admin-stat-value">{{ count($configCategories) }}</div>
                <div class="admin-stat-label">Catégories</div>
            </div>
        </div>
    </div>

    <!-- Liste des paramètres -->
    @if($activeTab === 'list')
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Liste des paramètres</h2>
                <div class="admin-card-tools">
                    <div class="admin-search">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="admin-input">
                    </div>
                </div>
            </div>
            <div class="admin-card-body">
                <div class="admin-filters">
                    <div class="admin-filter">
                        <label for="filterCategory" class="admin-filter-label">Catégorie</label>
                        <select id="filterCategory" wire:model.live="filterCategory" class="admin-select">
                            <option value="">Toutes les catégories</option>
                            @foreach($configCategories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-filter">
                        <label for="filterType" class="admin-filter-label">Type</label>
                        <select id="filterType" wire:model.live="filterType" class="admin-select">
                            <option value="">Tous les types</option>
                            @foreach($configTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-filter">
                        <label for="filterActive" class="admin-filter-label">Statut</label>
                        <select id="filterActive" wire:model.live="filterActive" class="admin-select">
                            <option value="">Tous les statuts</option>
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                    <div class="admin-filter">
                        <label for="perPage" class="admin-filter-label">Par page</label>
                        <select id="perPage" wire:model.live="perPage" class="admin-select">
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <div class="admin-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th wire:click="sortBy('key')" class="admin-table-sortable">
                                    Clé
                                    @if($sortField === 'key')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th>Valeur</th>
                                <th wire:click="sortBy('type')" class="admin-table-sortable">
                                    Type
                                    @if($sortField === 'type')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('category')" class="admin-table-sortable">
                                    Catégorie
                                    @if($sortField === 'category')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('is_active')" class="admin-table-sortable">
                                    Statut
                                    @if($sortField === 'is_active')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($configs as $config)
                                <tr>
                                    <td>{{ $config->key }}</td>
                                    <td>
                                        @if($config->type === 'boolean')
                                            {{ $config->value ? 'Vrai' : 'Faux' }}
                                        @elseif($config->type === 'json')
                                            <span class="admin-badge admin-badge-info">JSON</span>
                                        @else
                                            {{ Str::limit($config->value, 50) }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="admin-badge">
                                            {{ $configTypes[$config->type] ?? $config->type }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-primary">
                                            {{ $configCategories[$config->category] ?? $config->category }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($config->is_active)
                                            <span class="admin-badge admin-badge-success">Actif</span>
                                        @else
                                            <span class="admin-badge admin-badge-danger">Inactif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <button class="admin-btn admin-btn-primary admin-btn-sm" wire:click="selectConfig({{ $config->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="admin-table-empty">Aucun paramètre trouvé</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    {{ $configs->links() }}
                </div>
            </div>
        </div>
    @endif

    <!-- Formulaire de création de paramètre -->
    @if($activeTab === 'create')
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Créer un nouveau paramètre</h2>
            </div>
            <div class="admin-card-body">
                <form wire:submit.prevent="createConfig" class="admin-form">
                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="key" class="admin-form-label">Clé</label>
                            <input type="text" id="key" wire:model="configForm.key" class="admin-form-input" placeholder="server_name">
                            @error('configForm.key') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-form-group">
                            <label for="type" class="admin-form-label">Type</label>
                            <select id="type" wire:model="configForm.type" class="admin-form-select">
                                @foreach($configTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('configForm.type') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label for="value" class="admin-form-label">Valeur</label>
                        @if($configForm['type'] === 'boolean')
                            <select id="value" wire:model="configForm.value" class="admin-form-select">
                                <option value="1">Vrai</option>
                                <option value="0">Faux</option>
                            </select>
                        @elseif($configForm['type'] === 'json')
                            <textarea id="value" wire:model="configForm.value" class="admin-form-textarea" placeholder='{"key": "value"}'></textarea>
                        @else
                            <input type="text" id="value" wire:model="configForm.value" class="admin-form-input" placeholder="Valeur du paramètre">
                        @endif
                        @error('configForm.value') <span class="admin-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="admin-form-group">
                        <label for="description" class="admin-form-label">Description</label>
                        <textarea id="description" wire:model="configForm.description" class="admin-form-textarea" placeholder="Description du paramètre"></textarea>
                        @error('configForm.description') <span class="admin-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="category" class="admin-form-label">Catégorie</label>
                            <select id="category" wire:model="configForm.category" class="admin-form-select">
                                @foreach($configCategories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('configForm.category') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-form-group">
                            <label for="is_active" class="admin-form-label">Statut</label>
                            <select id="is_active" wire:model="configForm.is_active" class="admin-form-select">
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                            @error('configForm.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="admin-form-actions">
                        <button type="button" class="admin-btn admin-btn-outline" wire:click="resetConfigForm">Réinitialiser</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Créer le paramètre</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Formulaire d'édition de paramètre -->
    @if($activeTab === 'edit' && $selectedConfig)
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Modifier le paramètre</h2>
            </div>
            <div class="admin-card-body">
                <form wire:submit.prevent="updateConfig" class="admin-form">
                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="edit_key" class="admin-form-label">Clé</label>
                            <input type="text" id="edit_key" wire:model="configForm.key" class="admin-form-input" placeholder="server_name">
                            @error('configForm.key') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-form-group">
                            <label for="edit_type" class="admin-form-label">Type</label>
                            <select id="edit_type" wire:model="configForm.type" class="admin-form-select">
                                @foreach($configTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('configForm.type') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label for="edit_value" class="admin-form-label">Valeur</label>
                        @if($configForm['type'] === 'boolean')
                            <select id="edit_value" wire:model="configForm.value" class="admin-form-select">
                                <option value="1">Vrai</option>
                                <option value="0">Faux</option>
                            </select>
                        @elseif($configForm['type'] === 'json')
                            <textarea id="edit_value" wire:model="configForm.value" class="admin-form-textarea" placeholder='{"key": "value"}'></textarea>
                        @else
                            <input type="text" id="edit_value" wire:model="configForm.value" class="admin-form-input" placeholder="Valeur du paramètre">
                        @endif
                        @error('configForm.value') <span class="admin-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="admin-form-group">
                        <label for="edit_description" class="admin-form-label">Description</label>
                        <textarea id="edit_description" wire:model="configForm.description" class="admin-form-textarea" placeholder="Description du paramètre"></textarea>
                        @error('configForm.description') <span class="admin-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="admin-form-grid admin-form-grid-2">
                        <div class="admin-form-group">
                            <label for="edit_category" class="admin-form-label">Catégorie</label>
                            <select id="edit_category" wire:model="configForm.category" class="admin-form-select">
                                @foreach($configCategories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('configForm.category') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-form-group">
                            <label for="edit_is_active" class="admin-form-label">Statut</label>
                            <select id="edit_is_active" wire:model="configForm.is_active" class="admin-form-select">
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                            @error('configForm.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="admin-form-actions">
                        <button type="button" class="admin-btn admin-btn-danger" wire:click="deleteConfig" wire:confirm="Êtes-vous sûr de vouloir supprimer ce paramètre ?">Supprimer</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>