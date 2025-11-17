<div class="admin-factions">
    <div class="admin-page-header">
        <h1>Gestion des factions</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button {{ $activeTab === 'list' ? 'active' : '' }}" wire:click="setActiveTab('list')">
                <i class="fas fa-flag"></i> Liste des factions
            </button>
            <button class="admin-tab-button {{ $activeTab === 'create' ? 'active' : '' }}" wire:click="setActiveTab('create')">
                <i class="fas fa-plus"></i> Créer une faction
            </button>
            @if($activeTab === 'edit')
                <button class="admin-tab-button active">
                    <i class="fas fa-edit"></i> {{ $selectedFaction->name }}
                </button>
            @endif
        </div>
    </div>

    <div class="admin-content-body">
        <!-- Liste des factions -->
        @if($activeTab === 'list')
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des factions</h2>
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
                            <label for="filterActive">Statut:</label>
                            <select id="filterActive" wire:model.live="filterActive" class="admin-select">
                                <option value="">Tous</option>
                                <option value="active">Actives</option>
                                <option value="inactive">Inactives</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="admin-table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th wire:click="sortBy('sort_order')" class="admin-sortable-column">
                                        Ordre
                                        @if($sortField === 'sort_order')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('name')" class="admin-sortable-column">
                                        Nom
                                        @if($sortField === 'name')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th>Icône</th>
                                    <th>Couleur</th>
                                    <th>Bonus principaux</th>
                                    <th>Utilisateurs</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($factions as $faction)
                                    <tr>
                                        <td>{{ $faction->sort_order }}</td>
                                        <td>{{ $faction->name }}</td>
                                        <td>
                                            @if($faction->icon)
                                                <i class="{{ $faction->icon }}" style="color: {{ $faction->color_code }};"></i>
                                            @else
                                                <i class="fas fa-flag" style="color: {{ $faction->color_code }};"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="admin-color-preview" style="background-color: {{ $faction->color_code }};"></div>
                                            {{ $faction->color_code }}
                                        </td>
                                        <td>
                                            <div class="admin-badges-container">
                                                @if($faction->getBonusResourceProduction() != 0)
                                                    <span class="admin-badge {{ $faction->getBonusResourceProduction() > 0 ? 'success' : 'danger' }}">
                                                        Production: {{ $faction->getBonusResourceProduction() > 0 ? '+' : '' }}{{ $faction->getBonusResourceProduction() }}%
                                                    </span>
                                                @endif
                                                
                                                @if($faction->getBonusAttackPower() != 0)
                                                    <span class="admin-badge {{ $faction->getBonusAttackPower() > 0 ? 'success' : 'danger' }}">
                                                        Attaque: {{ $faction->getBonusAttackPower() > 0 ? '+' : '' }}{{ $faction->getBonusAttackPower() }}%
                                                    </span>
                                                @endif
                                                
                                                @if($faction->getBonusDefensePower() != 0)
                                                    <span class="admin-badge {{ $faction->getBonusDefensePower() > 0 ? 'success' : 'danger' }}">
                                                        Défense: {{ $faction->getBonusDefensePower() > 0 ? '+' : '' }}{{ $faction->getBonusDefensePower() }}%
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="admin-badge info">
                                                {{ $userCounts[$faction->id] ?? 0 }} utilisateur(s)
                                            </span>
                                        </td>
                                        <td>
                                            @if($faction->is_active)
                                                <span class="admin-badge success">Active</span>
                                            @else
                                                <span class="admin-badge danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="admin-actions">
                                                <button class="admin-action-button admin-action-info" wire:click="selectFaction({{ $faction->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="admin-table-empty">Aucune faction trouvée</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="admin-pagination">
                        {{ $factions->links() }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Créer une faction -->
        @if($activeTab === 'create')
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Créer une nouvelle faction</h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="createFaction">
                        <div class="admin-form-grid-2">
                            <div class="admin-form-group">
                                <label for="name">Nom de la faction</label>
                                <input type="text" id="name" wire:model="factionForm.name" wire:change="generateSlug" class="admin-input" required>
                                @error('factionForm.name') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="slug">Slug</label>
                                <input type="text" id="slug" wire:model="factionForm.slug" class="admin-input" required>
                                @error('factionForm.slug') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-grid-2">
                            <div class="admin-form-group">
                                <label for="icon">Icône (classe FontAwesome)</label>
                                <input type="text" id="icon" wire:model="factionForm.icon" class="admin-input" placeholder="fas fa-flag">
                                @error('factionForm.icon') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="color_code">Couleur</label>
                                <input type="color" id="color_code" wire:model="factionForm.color_code" class="admin-input">
                                @error('factionForm.color_code') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-grid-2">
                            <div class="admin-form-group">
                                <label for="banner">Bannière (URL)</label>
                                <input type="text" id="banner" wire:model="factionForm.banner" class="admin-input" placeholder="URL de l'image">
                                @error('factionForm.banner') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="sort_order">Ordre d'affichage</label>
                                <input type="number" id="sort_order" wire:model="factionForm.sort_order" class="admin-input" min="0">
                                @error('factionForm.sort_order') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="description">Description</label>
                            <textarea id="description" wire:model="factionForm.description" class="admin-textarea" rows="4"></textarea>
                            @error('factionForm.description') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="admin-form-group">
                            <label>Bonus de faction</label>
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="resource_production">Production de ressources (%)</label>
                                    <input type="number" id="resource_production" wire:model="factionForm.bonuses.resource_production" class="admin-input" step="1">
                                    @error('factionForm.bonuses.resource_production') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="building_cost">Coût des bâtiments (%)</label>
                                    <input type="number" id="building_cost" wire:model="factionForm.bonuses.building_cost" class="admin-input" step="1">
                                    @error('factionForm.bonuses.building_cost') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="technology_cost">Coût des technologies (%)</label>
                                    <input type="number" id="technology_cost" wire:model="factionForm.bonuses.technology_cost" class="admin-input" step="1">
                                    @error('factionForm.bonuses.technology_cost') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="ship_speed">Vitesse des vaisseaux (%)</label>
                                    <input type="number" id="ship_speed" wire:model="factionForm.bonuses.ship_speed" class="admin-input" step="1">
                                    @error('factionForm.bonuses.ship_speed') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="attack_power">Puissance d'attaque (%)</label>
                                    <input type="number" id="attack_power" wire:model="factionForm.bonuses.attack_power" class="admin-input" step="1">
                                    @error('factionForm.bonuses.attack_power') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="defense_power">Puissance de défense (%)</label>
                                    <input type="number" id="defense_power" wire:model="factionForm.bonuses.defense_power" class="admin-input" step="1">
                                    @error('factionForm.bonuses.defense_power') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="ship_capacity">Capacité des vaisseaux (%)</label>
                                    <input type="number" id="ship_capacity" wire:model="factionForm.bonuses.ship_capacity" class="admin-input" step="1">
                                    @error('factionForm.bonuses.ship_capacity') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="building_speed">Vitesse de construction (%)</label>
                                    <input type="number" id="building_speed" wire:model="factionForm.bonuses.building_speed" class="admin-input" step="1">
                                    @error('factionForm.bonuses.building_speed') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-checkbox-container">
                                <input type="checkbox" wire:model="factionForm.is_active">
                                <span class="admin-checkbox-label">Faction active</span>
                            </label>
                            @error('factionForm.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="admin-form-actions">
                            <button type="button" class="admin-button admin-button-secondary" wire:click="setActiveTab('list')">Annuler</button>
                            <button type="submit" class="admin-button admin-button-primary">Créer la faction</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Éditer une faction -->
        @if($activeTab === 'edit' && $selectedFaction)
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Modifier la faction: {{ $selectedFaction->name }}</h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="updateFaction">
                        <div class="admin-form-grid-2">
                            <div class="admin-form-group">
                                <label for="edit_name">Nom de la faction</label>
                                <input type="text" id="edit_name" wire:model="factionForm.name" wire:change="generateSlug" class="admin-input" required>
                                @error('factionForm.name') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_slug">Slug</label>
                                <input type="text" id="edit_slug" wire:model="factionForm.slug" class="admin-input" required>
                                @error('factionForm.slug') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-grid-2">
                            <div class="admin-form-group">
                                <label for="edit_icon">Icône (classe FontAwesome)</label>
                                <input type="text" id="edit_icon" wire:model="factionForm.icon" class="admin-input" placeholder="fas fa-flag">
                                @error('factionForm.icon') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_color_code">Couleur</label>
                                <input type="color" id="edit_color_code" wire:model="factionForm.color_code" class="admin-input">
                                @error('factionForm.color_code') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-grid-2">
                            <div class="admin-form-group">
                                <label for="edit_banner">Bannière (URL)</label>
                                <input type="text" id="edit_banner" wire:model="factionForm.banner" class="admin-input" placeholder="URL de l'image">
                                @error('factionForm.banner') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_sort_order">Ordre d'affichage</label>
                                <input type="number" id="edit_sort_order" wire:model="factionForm.sort_order" class="admin-input" min="0">
                                @error('factionForm.sort_order') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="edit_description">Description</label>
                            <textarea id="edit_description" wire:model="factionForm.description" class="admin-textarea" rows="4"></textarea>
                            @error('factionForm.description') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="admin-form-group">
                            <label>Bonus de faction</label>
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="edit_resource_production">Production de ressources (%)</label>
                                    <input type="number" id="edit_resource_production" wire:model="factionForm.bonuses.resource_production" class="admin-input" step="1">
                                    @error('factionForm.bonuses.resource_production') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="edit_building_cost">Coût des bâtiments (%)</label>
                                    <input type="number" id="edit_building_cost" wire:model="factionForm.bonuses.building_cost" class="admin-input" step="1">
                                    @error('factionForm.bonuses.building_cost') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="edit_technology_cost">Coût des technologies (%)</label>
                                    <input type="number" id="edit_technology_cost" wire:model="factionForm.bonuses.technology_cost" class="admin-input" step="1">
                                    @error('factionForm.bonuses.technology_cost') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="edit_ship_speed">Vitesse des vaisseaux (%)</label>
                                    <input type="number" id="edit_ship_speed" wire:model="factionForm.bonuses.ship_speed" class="admin-input" step="1">
                                    @error('factionForm.bonuses.ship_speed') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="edit_attack_power">Puissance d'attaque (%)</label>
                                    <input type="number" id="edit_attack_power" wire:model="factionForm.bonuses.attack_power" class="admin-input" step="1">
                                    @error('factionForm.bonuses.attack_power') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="edit_defense_power">Puissance de défense (%)</label>
                                    <input type="number" id="edit_defense_power" wire:model="factionForm.bonuses.defense_power" class="admin-input" step="1">
                                    @error('factionForm.bonuses.defense_power') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="admin-form-grid-2">
                                <div class="admin-form-group">
                                    <label for="edit_ship_capacity">Capacité des vaisseaux (%)</label>
                                    <input type="number" id="edit_ship_capacity" wire:model="factionForm.bonuses.ship_capacity" class="admin-input" step="1">
                                    @error('factionForm.bonuses.ship_capacity') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="edit_building_speed">Vitesse de construction (%)</label>
                                    <input type="number" id="edit_building_speed" wire:model="factionForm.bonuses.building_speed" class="admin-input" step="1">
                                    @error('factionForm.bonuses.building_speed') <span class="admin-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="admin-form-group">
                            <label class="admin-checkbox-container">
                                <input type="checkbox" wire:model="factionForm.is_active">
                                <span class="admin-checkbox-label">Faction active</span>
                            </label>
                            @error('factionForm.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="admin-form-actions">
                            <button type="button" class="admin-button admin-button-secondary" wire:click="setActiveTab('list')">Annuler</button>
                            <button type="submit" class="admin-button admin-button-primary">Mettre à jour la faction</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>