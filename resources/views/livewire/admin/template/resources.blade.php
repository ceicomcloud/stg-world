<div class="admin-resoureces">
    <div class="admin-page-header">
        <h1>Gestion des ressources</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button {{ $activeTab === 'list' ? 'active' : '' }}" wire:click="setActiveTab('list')">
                <i class="fas fa-coins"></i> Liste des ressources
            </button>
            <button class="admin-tab-button {{ $activeTab === 'create' ? 'active' : '' }}" wire:click="setActiveTab('create')">
                <i class="fas fa-plus-circle"></i> Créer une ressource
            </button>
            @if($activeTab === 'edit')
                <button class="admin-tab-button active">
                    <i class="fas fa-edit"></i> {{ $selectedResource->display_name }}
                </button>
            @endif
        </div>
    </div>

    <div class="admin-content-body">
        <!-- Liste des ressources -->
        @if($activeTab === 'list')
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des ressources</h2>
                    <div class="admin-card-tools">
                        <div class="admin-search-box">
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="admin-search-input">
                            <i class="fas fa-search admin-search-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="admin-card-body">
                    <div class="admin-filters">
                        <div class="admin-filter-group">
                            <label for="filterType">Type:</label>
                            <select id="filterType" wire:model.live="filterType" class="admin-select">
                                <option value="">Tous</option>
                                @foreach($resourceTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <label for="filterActive">Statut:</label>
                            <select id="filterActive" wire:model.live="filterActive" class="admin-select">
                                <option value="">Tous</option>
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <label for="perPage">Par page:</label>
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
                                    <th wire:click="sortBy('sort_order')" class="admin-sortable">
                                        Ordre
                                        @if($sortField === 'sort_order')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('name')" class="admin-sortable">
                                        Nom
                                        @if($sortField === 'name')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('display_name')" class="admin-sortable">
                                        Nom affiché
                                        @if($sortField === 'display_name')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th>Icône</th>
                                    <th wire:click="sortBy('type')" class="admin-sortable">
                                        Type
                                        @if($sortField === 'type')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('base_production')" class="admin-sortable">
                                        Production
                                        @if($sortField === 'base_production')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('base_storage')" class="admin-sortable">
                                        Stockage
                                        @if($sortField === 'base_storage')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th>Planètes</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resources as $resource)
                                    <tr>
                                        <td>{{ $resource->sort_order }}</td>
                                        <td>{{ $resource->name }}</td>
                                        <td>{{ $resource->display_name }}</td>
                                        <td>
                                            @if($resource->icon)
                                                <i class="fas fa-{{ $resource->icon }}" style="color: {{ $resource->color }};"></i>
                                            @else
                                                <i class="fas fa-coins" style="color: {{ $resource->color }};"></i>
                                            @endif
                                        </td>
                                        <td>{{ $resourceTypes[$resource->type] ?? $resource->type }}</td>
                                        <td>{{ $resource->base_production }}</td>
                                        <td>{{ $resource->base_storage }}</td>
                                        <td>{{ $planetCounts[$resource->id] ?? 0 }}</td>
                                        <td>
                                            @if($resource->is_active)
                                                <span class="admin-badge admin-badge-success">Actif</span>
                                            @else
                                                <span class="admin-badge admin-badge-danger">Inactif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="selectResource({{ $resource->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                
                                @if($resources->count() === 0)
                                    <tr>
                                        <td colspan="10" class="admin-table-empty">Aucune ressource trouvée</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="admin-pagination">
                        {{ $resources->links() }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Créer une ressource -->
        @if($activeTab === 'create')
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Créer une nouvelle ressource</h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="createResource">
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="name">Nom (identifiant)</label>
                                <input type="text" id="name" wire:model="resourceForm.name" class="admin-input" required>
                                @error('resourceForm.name') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="display_name">Nom affiché</label>
                                <input type="text" id="display_name" wire:model="resourceForm.display_name" class="admin-input" required>
                                @error('resourceForm.display_name') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="type">Type de ressource</label>
                                <select id="type" wire:model="resourceForm.type" class="admin-select" required>
                                    <option value="">Sélectionner un type</option>
                                    @foreach($resourceTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('resourceForm.type') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="sort_order">Ordre d'affichage</label>
                                <input type="number" id="sort_order" wire:model="resourceForm.sort_order" class="admin-input" min="0" required>
                                @error('resourceForm.sort_order') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="icon">Icône (classe FontAwesome)</label>
                                <input type="text" id="icon" wire:model="resourceForm.icon" class="admin-input" placeholder="ex: coins, atom, etc.">
                                @error('resourceForm.icon') <span class="admin-error">{{ $message }}</span> @enderror
                                <div class="admin-form-help">Prévisualisation: 
                                    <i class="fas fa-{{ $resourceForm['icon'] ?: 'coins' }}" style="color: {{ $resourceForm['color'] }};"></i>
                                </div>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="color">Couleur</label>
                                <input type="color" id="color" wire:model="resourceForm.color" class="admin-input">
                                @error('resourceForm.color') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="base_production">Production de base</label>
                                <input type="number" id="base_production" wire:model="resourceForm.base_production" class="admin-input" min="0" step="0.01" required>
                                @error('resourceForm.base_production') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="base_storage">Stockage de base</label>
                                <input type="number" id="base_storage" wire:model="resourceForm.base_storage" class="admin-input" min="0" required>
                                @error('resourceForm.base_storage') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="trade_rate">Taux d'échange</label>
                                <input type="number" id="trade_rate" wire:model="resourceForm.trade_rate" class="admin-input" min="0" step="0.01" required>
                                @error('resourceForm.trade_rate') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group admin-form-checkbox-group">
                                <div class="admin-checkbox-container">
                                    <input type="checkbox" id="is_tradeable" wire:model="resourceForm.is_tradeable" class="admin-checkbox">
                                    <label for="is_tradeable">Échangeable</label>
                                </div>
                                @error('resourceForm.is_tradeable') <span class="admin-error">{{ $message }}</span> @enderror
                                
                                <div class="admin-checkbox-container">
                                    <input type="checkbox" id="is_active" wire:model="resourceForm.is_active" class="admin-checkbox">
                                    <label for="is_active">Actif</label>
                                </div>
                                @error('resourceForm.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group admin-form-group-full">
                                <label for="description">Description</label>
                                <textarea id="description" wire:model="resourceForm.description" class="admin-textarea" rows="4"></textarea>
                                @error('resourceForm.description') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-actions">
                            <button type="button" class="admin-btn admin-btn-secondary" wire:click="setActiveTab('list')">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-save"></i> Créer la ressource
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Éditer une ressource -->
        @if($activeTab === 'edit' && $selectedResource)
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Modifier la ressource: {{ $selectedResource->display_name }}</h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="updateResource">
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_name">Nom (identifiant)</label>
                                <input type="text" id="edit_name" wire:model="resourceForm.name" class="admin-input" required>
                                @error('resourceForm.name') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_display_name">Nom affiché</label>
                                <input type="text" id="edit_display_name" wire:model="resourceForm.display_name" class="admin-input" required>
                                @error('resourceForm.display_name') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_type">Type de ressource</label>
                                <select id="edit_type" wire:model="resourceForm.type" class="admin-select" required>
                                    <option value="">Sélectionner un type</option>
                                    @foreach($resourceTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('resourceForm.type') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_sort_order">Ordre d'affichage</label>
                                <input type="number" id="edit_sort_order" wire:model="resourceForm.sort_order" class="admin-input" min="0" required>
                                @error('resourceForm.sort_order') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_icon">Icône (classe FontAwesome)</label>
                                <input type="text" id="edit_icon" wire:model="resourceForm.icon" class="admin-input" placeholder="ex: coins, atom, etc.">
                                @error('resourceForm.icon') <span class="admin-error">{{ $message }}</span> @enderror
                                <div class="admin-form-help">Prévisualisation: 
                                    <i class="fas fa-{{ $resourceForm['icon'] ?: 'coins' }}" style="color: {{ $resourceForm['color'] }};"></i>
                                </div>
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_color">Couleur</label>
                                <input type="color" id="edit_color" wire:model="resourceForm.color" class="admin-input">
                                @error('resourceForm.color') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_base_production">Production de base</label>
                                <input type="number" id="edit_base_production" wire:model="resourceForm.base_production" class="admin-input" min="0" step="0.01" required>
                                @error('resourceForm.base_production') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_base_storage">Stockage de base</label>
                                <input type="number" id="edit_base_storage" wire:model="resourceForm.base_storage" class="admin-input" min="0" required>
                                @error('resourceForm.base_storage') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_trade_rate">Taux d'échange</label>
                                <input type="number" id="edit_trade_rate" wire:model="resourceForm.trade_rate" class="admin-input" min="0" step="0.01" required>
                                @error('resourceForm.trade_rate') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group admin-form-checkbox-group">
                                <div class="admin-checkbox-container">
                                    <input type="checkbox" id="edit_is_tradeable" wire:model="resourceForm.is_tradeable" class="admin-checkbox">
                                    <label for="edit_is_tradeable">Échangeable</label>
                                </div>
                                @error('resourceForm.is_tradeable') <span class="admin-error">{{ $message }}</span> @enderror
                                
                                <div class="admin-checkbox-container">
                                    <input type="checkbox" id="edit_is_active" wire:model="resourceForm.is_active" class="admin-checkbox">
                                    <label for="edit_is_active">Actif</label>
                                </div>
                                @error('resourceForm.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group admin-form-group-full">
                                <label for="edit_description">Description</label>
                                <textarea id="edit_description" wire:model="resourceForm.description" class="admin-textarea" rows="4"></textarea>
                                @error('resourceForm.description') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-actions">
                            <button type="button" class="admin-btn admin-btn-secondary" wire:click="setActiveTab('list')">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i class="fas fa-save"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>