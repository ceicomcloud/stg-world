<div class="admin-content">
    <div class="admin-page-header">
        <h1>Gestion des planètes templates</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button {{ $activeTab === 'list' ? 'active' : '' }}" wire:click="setActiveTab('list')">
                Liste des planètes
            </button>
            <button class="admin-tab-button {{ $activeTab === 'create' ? 'active' : '' }}" wire:click="setActiveTab('create')">
                Créer une planète
            </button>
            @if($selectedPlanet)
                <button class="admin-tab-button active">
                    {{ $selectedPlanet->name }}
                </button>
            @endif
        </div>
    </div>

    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ $freePlanetsCount }}</div>
                <div class="admin-stat-label">Planètes libres</div>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-user-astronaut"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ $occupiedPlanetsCount }}</div>
                <div class="admin-stat-label">Planètes occupées</div>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-flag"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ $colonizablePlanetsCount }}</div>
                <div class="admin-stat-label">Planètes colonisables</div>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-icon">
                <i class="fas fa-meteor"></i>
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ array_sum($planetTypeCounts) }}</div>
                <div class="admin-stat-label">Total des planètes</div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Liste des planètes</h2>
        </div>
        <div class="admin-card-body">            
            <!-- Onglet Liste des planètes -->
            @if($activeTab === 'list')
                <div class="admin-filters">
                    <div class="admin-filter-group">
                        <input type="text" wire:model.live="search" placeholder="Rechercher par nom ou coordonnées..." class="admin-input">
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterGalaxy" class="admin-select">
                            <option value="">Toutes les galaxies</option>
                            @foreach($galaxies as $galaxy)
                                <option value="{{ $galaxy }}">Galaxie {{ $galaxy }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterSystem" class="admin-select">
                            <option value="">Tous les systèmes</option>
                            @foreach($systems as $system)
                                <option value="{{ $system }}">Système {{ $system }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterType" class="admin-select">
                            <option value="">Tous les types</option>
                            @foreach($planetTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterSize" class="admin-select">
                            <option value="">Toutes les tailles</option>
                            @foreach($planetSizes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterOccupied" class="admin-select">
                            <option value="">Occupation</option>
                            <option value="occupied">Occupées</option>
                            <option value="free">Libres</option>
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterColonizable" class="admin-select">
                            <option value="">Colonisation</option>
                            <option value="colonizable">Colonisables</option>
                            <option value="not_colonizable">Non colonisables</option>
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="filterActive" class="admin-select">
                            <option value="">Statut</option>
                            <option value="active">Actives</option>
                            <option value="inactive">Inactives</option>
                        </select>
                    </div>
                    
                    <div class="admin-filter-group">
                        <select wire:model.live="perPage" class="admin-select">
                            <option value="15">15 par page</option>
                            <option value="30">30 par page</option>
                            <option value="50">50 par page</option>
                            <option value="100">100 par page</option>
                        </select>
                    </div>
                </div>
                
                <div class="admin-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th wire:click="sortBy('id')" class="admin-th-sortable">
                                    ID
                                    @if($sortField === 'id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('name')" class="admin-th-sortable">
                                    Nom
                                    @if($sortField === 'name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('galaxy')" class="admin-th-sortable">
                                    Coordonnées
                                    @if($sortField === 'galaxy')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('type')" class="admin-th-sortable">
                                    Type
                                    @if($sortField === 'type')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('size')" class="admin-th-sortable">
                                    Taille
                                    @if($sortField === 'size')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('fields')" class="admin-th-sortable">
                                    Champs
                                    @if($sortField === 'fields')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('is_occupied')" class="admin-th-sortable">
                                    Occupation
                                    @if($sortField === 'is_occupied')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('is_colonizable')" class="admin-th-sortable">
                                    Colonisable
                                    @if($sortField === 'is_colonizable')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('is_active')" class="admin-th-sortable">
                                    Statut
                                    @if($sortField === 'is_active')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('created_at')" class="admin-th-sortable">
                                    Création
                                    @if($sortField === 'created_at')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($planets as $planet)
                                <tr>
                                    <td>{{ $planet->id }}</td>
                                    <td>{{ $planet->name }}</td>
                                    <td>[{{ $planet->galaxy }}:{{ $planet->system }}:{{ $planet->position }}]</td>
                                    <td>
                                        <span class="admin-badge admin-badge-{{ $planet->type }}">
                                            {{ $planetTypes[$planet->type] }}
                                        </span>
                                    </td>
                                    <td>{{ $planetSizes[$planet->size] }}</td>
                                    <td>{{ $planet->fields }}</td>
                                    <td>
                                        @if($planet->is_occupied)
                                            <span class="admin-badge admin-badge-danger">Occupée</span>
                                        @else
                                            <span class="admin-badge admin-badge-success">Libre</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($planet->is_colonizable)
                                            <span class="admin-badge admin-badge-success">Oui</span>
                                        @else
                                            <span class="admin-badge admin-badge-danger">Non</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($planet->is_active)
                                            <span class="admin-badge admin-badge-success">Active</span>
                                        @else
                                            <span class="admin-badge admin-badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $planet->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="admin-actions">
                                            <button wire:click="selectPlanet({{ $planet->id }})" class="admin-btn admin-btn-primary admin-btn-sm">
                                                <i class="fas fa-edit"></i> Éditer
                                            </button>
                                            <button wire:click="deletePlanet({{ $planet->id }})" class="admin-btn admin-btn-danger admin-btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette planète ?')">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">Aucune planète trouvée</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="admin-pagination">
                    {{ $planets->links() }}
                </div>
            @endif
            
            <!-- Onglet Créer une planète -->
            @if($activeTab === 'create')
                <form wire:submit.prevent="createPlanet" class="admin-form">
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Informations générales</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="name" class="admin-form-label">Nom</label>
                                <div class="admin-input-group">
                                    <input type="text" id="name" wire:model="planetForm.name" class="admin-input" required>
                                    <button type="button" wire:click="generatePlanetName" class="admin-btn admin-btn-secondary">
                                        <i class="fas fa-magic"></i> Générer
                                    </button>
                                </div>
                                @error('planetForm.name') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Coordonnées</label>
                                <div class="admin-input-group">
                                    <input type="number" wire:model="planetForm.galaxy" class="admin-input" min="1" placeholder="Galaxie" required>
                                    <span class="admin-input-group-text">:</span>
                                    <input type="number" wire:model="planetForm.system" class="admin-input" min="1" placeholder="Système" required>
                                    <span class="admin-input-group-text">:</span>
                                    <input type="number" wire:model="planetForm.position" class="admin-input" min="1" placeholder="Position" required>
                                </div>
                                @error('planetForm.galaxy') <span class="admin-error">{{ $message }}</span> @enderror
                                @error('planetForm.system') <span class="admin-error">{{ $message }}</span> @enderror
                                @error('planetForm.position') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="type" class="admin-form-label">Type</label>
                                <select id="type" wire:model.live="planetForm.type" class="admin-select" required>
                                    @foreach($planetTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('planetForm.type') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="size" class="admin-form-label">Taille</label>
                                <select id="size" wire:model.live="planetForm.size" class="admin-select" required>
                                    @foreach($planetSizes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('planetForm.size') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="" class="admin-form-label">&nbsp;</label>
                                <button type="button" wire:click="calculatePlanetProperties" class="admin-btn admin-btn-secondary">
                                    <i class="fas fa-calculator"></i> Calculer les propriétés
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Propriétés physiques</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="diameter" class="admin-form-label">Diamètre (km)</label>
                                <input type="number" id="diameter" wire:model="planetForm.diameter" class="admin-input" min="1000" required>
                                @error('planetForm.diameter') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="fields" class="admin-form-label">Champs</label>
                                <input type="number" id="fields" wire:model="planetForm.fields" class="admin-input" min="0" required>
                                @error('planetForm.fields') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="min_temperature" class="admin-form-label">Température min (°C)</label>
                                <input type="number" id="min_temperature" wire:model="planetForm.min_temperature" class="admin-input" required>
                                @error('planetForm.min_temperature') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="max_temperature" class="admin-form-label">Température max (°C)</label>
                                <input type="number" id="max_temperature" wire:model="planetForm.max_temperature" class="admin-input" required>
                                @error('planetForm.max_temperature') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Bonus de ressources</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="metal_bonus" class="admin-form-label">Bonus de métal</label>
                                <input type="number" id="metal_bonus" wire:model="planetForm.metal_bonus" class="admin-input" min="0" step="0.01" required>
                                @error('planetForm.metal_bonus') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="crystal_bonus" class="admin-form-label">Bonus de cristal</label>
                                <input type="number" id="crystal_bonus" wire:model="planetForm.crystal_bonus" class="admin-input" min="0" step="0.01" required>
                                @error('planetForm.crystal_bonus') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="deuterium_bonus" class="admin-form-label">Bonus de deutérium</label>
                                <input type="number" id="deuterium_bonus" wire:model="planetForm.deuterium_bonus" class="admin-input" min="0" step="0.01" required>
                                @error('planetForm.deuterium_bonus') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="energy_bonus" class="admin-form-label">Bonus d'énergie</label>
                                <input type="number" id="energy_bonus" wire:model="planetForm.energy_bonus" class="admin-input" min="0" step="0.01" required>
                                @error('planetForm.energy_bonus') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Statut</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Colonisable</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="is_colonizable" wire:model="planetForm.is_colonizable" class="admin-toggle-input">
                                    <label for="is_colonizable" class="admin-toggle-label"></label>
                                </div>
                                @error('planetForm.is_colonizable') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Occupée</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="is_occupied" wire:model="planetForm.is_occupied" class="admin-toggle-input">
                                    <label for="is_occupied" class="admin-toggle-label"></label>
                                </div>
                                @error('planetForm.is_occupied') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Disponible</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="is_available" wire:model="planetForm.is_available" class="admin-toggle-input">
                                    <label for="is_available" class="admin-toggle-label"></label>
                                </div>
                                @error('planetForm.is_available') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Active</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="is_active" wire:model="planetForm.is_active" class="admin-toggle-input">
                                    <label for="is_active" class="admin-toggle-label"></label>
                                </div>
                                @error('planetForm.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-actions">
                        <button type="submit" class="admin-btn admin-btn-primary">
                            <i class="fas fa-save"></i> Créer la planète
                        </button>
                        <button type="button" wire:click="resetPlanetForm" class="admin-btn admin-btn-secondary">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </button>
                    </div>
                </form>
            @endif
            
            <!-- Onglet Édition d'une planète -->
            @if($activeTab === 'edit' && $selectedPlanet)
                <form wire:submit.prevent="updatePlanet" class="admin-form">
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Informations générales</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_name" class="admin-form-label">Nom</label>
                                <div class="admin-input-group">
                                    <input type="text" id="edit_name" wire:model="planetForm.name" class="admin-input" required>
                                    <button type="button" wire:click="generatePlanetName" class="admin-btn admin-btn-secondary">
                                        <i class="fas fa-magic"></i> Générer
                                    </button>
                                </div>
                                @error('planetForm.name') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Coordonnées</label>
                                <div class="admin-input-group">
                                    <input type="number" wire:model="planetForm.galaxy" class="admin-input" min="1" placeholder="Galaxie" required>
                                    <span class="admin-input-group-text">:</span>
                                    <input type="number" wire:model="planetForm.system" class="admin-input" min="1" placeholder="Système" required>
                                    <span class="admin-input-group-text">:</span>
                                    <input type="number" wire:model="planetForm.position" class="admin-input" min="1" placeholder="Position" required>
                                </div>
                                @error('planetForm.galaxy') <span class="admin-error">{{ $message }}</span> @enderror
                                @error('planetForm.system') <span class="admin-error">{{ $message }}</span> @enderror
                                @error('planetForm.position') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_type" class="admin-form-label">Type</label>
                                <select id="edit_type" wire:model.live="planetForm.type" class="admin-select" required>
                                    @foreach($planetTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('planetForm.type') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_size" class="admin-form-label">Taille</label>
                                <select id="edit_size" wire:model.live="planetForm.size" class="admin-select" required>
                                    @foreach($planetSizes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('planetForm.size') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="" class="admin-form-label">&nbsp;</label>
                                <button type="button" wire:click="calculatePlanetProperties" class="admin-btn admin-btn-secondary">
                                    <i class="fas fa-calculator"></i> Calculer les propriétés
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Propriétés physiques</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_diameter" class="admin-form-label">Diamètre (km)</label>
                                <input type="number" id="edit_diameter" wire:model="planetForm.diameter" class="admin-input" min="1000" required>
                                @error('planetForm.diameter') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_fields" class="admin-form-label">Champs</label>
                                <input type="number" id="edit_fields" wire:model="planetForm.fields" class="admin-input" min="0" required>
                                @error('planetForm.fields') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_min_temperature" class="admin-form-label">Température min (°C)</label>
                                <input type="number" id="edit_min_temperature" wire:model="planetForm.min_temperature" class="admin-input" required>
                                @error('planetForm.min_temperature') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_max_temperature" class="admin-form-label">Température max (°C)</label>
                                <input type="number" id="edit_max_temperature" wire:model="planetForm.max_temperature" class="admin-input" required>
                                @error('planetForm.max_temperature') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Bonus de ressources</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_metal_bonus" class="admin-form-label">Bonus de métal</label>
                                <input type="number" id="edit_metal_bonus" wire:model="planetForm.metal_bonus" class="admin-input" min="0" step="0.01" required>
                                @error('planetForm.metal_bonus') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_crystal_bonus" class="admin-form-label">Bonus de cristal</label>
                                <input type="number" id="edit_crystal_bonus" wire:model="planetForm.crystal_bonus" class="admin-input" min="0" step="0.01" required>
                                @error('planetForm.crystal_bonus') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="edit_deuterium_bonus" class="admin-form-label">Bonus de deutérium</label>
                                <input type="number" id="edit_deuterium_bonus" wire:model="planetForm.deuterium_bonus" class="admin-input" min="0" step="0.01" required>
                                @error('planetForm.deuterium_bonus') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label for="edit_energy_bonus" class="admin-form-label">Bonus d'énergie</label>
                                <input type="number" id="edit_energy_bonus" wire:model="planetForm.energy_bonus" class="admin-input" min="0" step="0.01" required>
                                @error('planetForm.energy_bonus') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-section">
                        <h3 class="admin-form-title">Statut</h3>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Colonisable</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="edit_is_colonizable" wire:model="planetForm.is_colonizable" class="admin-toggle-input">
                                    <label for="edit_is_colonizable" class="admin-toggle-label"></label>
                                </div>
                                @error('planetForm.is_colonizable') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Occupée</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="edit_is_occupied" wire:model="planetForm.is_occupied" class="admin-toggle-input">
                                    <label for="edit_is_occupied" class="admin-toggle-label"></label>
                                </div>
                                @error('planetForm.is_occupied') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Disponible</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="edit_is_available" wire:model="planetForm.is_available" class="admin-toggle-input">
                                    <label for="edit_is_available" class="admin-toggle-label"></label>
                                </div>
                                @error('planetForm.is_available') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="admin-form-group">
                                <label class="admin-form-label">Active</label>
                                <div class="admin-toggle-switch">
                                    <input type="checkbox" id="edit_is_active" wire:model="planetForm.is_active" class="admin-toggle-input">
                                    <label for="edit_is_active" class="admin-toggle-label"></label>
                                </div>
                                @error('planetForm.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="admin-form-actions">
                        <button type="submit" class="admin-btn admin-btn-primary">
                            <i class="fas fa-save"></i> Mettre à jour la planète
                        </button>
                        <button type="button" wire:click="setActiveTab('list')" class="admin-btn admin-btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>