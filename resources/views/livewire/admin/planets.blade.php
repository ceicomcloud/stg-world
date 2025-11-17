<div class="admin-planets">
    <div class="admin-page-header">
        <h1>Gestion des planètes</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button {{ $activeTab === 'list' ? 'active' : '' }}" wire:click="setActiveTab('list')">
                <i class="fas fa-globe"></i> Liste des planètes
            </button>
            <button class="admin-tab-button {{ $activeTab === 'assign' ? 'active' : '' }}" wire:click="setActiveTab('assign')">
                <i class="fas fa-user-plus"></i> Affecter une planète
            </button>
            @if($activeTab === 'detail')
                <button class="admin-tab-button active">
                    <i class="fas fa-info-circle"></i> {{ $selectedPlanet->name }}
                </button>
            @endif
        </div>
    </div>
    
    <div class="admin-content-body">
        <!-- Liste des planètes -->
        @if($activeTab === 'list')
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des planètes</h2>
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
                            <label for="filterOccupied">Occupation:</label>
                            <select id="filterOccupied" wire:model.live="filterOccupied" class="admin-select">
                                <option value="">Toutes</option>
                                <option value="occupied">Occupées</option>
                                <option value="free">Libres</option>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <label for="filterActive">Statut:</label>
                            <select id="filterActive" wire:model.live="filterActive" class="admin-select">
                                <option value="">Tous</option>
                                <option value="active">Actives</option>
                                <option value="inactive">Inactives</option>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <label for="filterType">Type:</label>
                            <select id="filterType" wire:model.live="filterType" class="admin-select">
                                <option value="">Tous</option>
                                <option value="planet">Planète</option>
                                <option value="moon">Lune</option>
                                <option value="asteroid">Astéroïde</option>
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <label for="perPage">Par page:</label>
                            <select id="perPage" wire:model.live="perPage" class="admin-select">
                                <option value="15">15</option>
                                <option value="30">30</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="admin-table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th wire:click="sortBy('id')" class="admin-sortable">
                                        ID
                                        @if($sortField === 'id')
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
                                    <th>Coordonnées</th>
                                    <th>Type</th>
                                    <th>Taille</th>
                                    <th>Utilisateur</th>
                                    <th wire:click="sortBy('is_main_planet')" class="admin-sortable">
                                        Statut
                                        @if($sortField === 'is_main_planet')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('is_active')" class="admin-sortable">
                                        Activité
                                        @if($sortField === 'is_active')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('created_at')" class="admin-sortable">
                                        Création
                                        @if($sortField === 'created_at')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
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
                                        <td class="admin-planet-coordinates">
                                            @if($planet->templatePlanet)
                                                {{ $planet->templatePlanet->galaxy }}:{{ $planet->templatePlanet->system }}:{{ $planet->templatePlanet->position }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($planet->templatePlanet)
                                                {{ ucfirst($planet->templatePlanet->type) }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($planet->templatePlanet)
                                                {{ $planet->templatePlanet->size }} ({{ $planet->used_fields }}/{{ $planet->templatePlanet->fields }})
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($planet->user)
                                                {{ $planet->user->name }}
                                            @else
                                                <span class="admin-badge admin-badge-warning">Non assignée</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($planet->is_main_planet)
                                                <span class="admin-badge admin-badge-primary">Principale</span>
                                            @else
                                                <span class="admin-badge admin-badge-secondary">Colonie</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($planet->is_active)
                                                <span class="admin-badge admin-badge-success">Active</span>
                                            @else
                                                <span class="admin-badge admin-badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $planet->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="admin-actions">
                                                <button wire:click="selectPlanet({{ $planet->id }})" class="admin-action-button admin-action-info" title="Voir les détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="admin-table-empty">Aucune planète trouvée</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="admin-pagination">
                        {{ $planets->links() }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Formulaire d'affectation d'une planète -->
        @if($activeTab === 'assign')
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Affecter une planète à un utilisateur</h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="assignPlanetToUser" class="admin-form">
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="user_id">Utilisateur</label>
                                <select wire:model="assignPlanet.user_id" id="user_id" class="admin-select">
                                    <option value="">Sélectionner un utilisateur</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('assignPlanet.user_id') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="admin-form-group">
                                <label for="template_planet_id">Planète disponible</label>
                                <select wire:model="assignPlanet.template_planet_id" id="template_planet_id" class="admin-select">
                                    <option value="">Sélectionner une planète</option>
                                    @foreach($availableTemplatePlanets as $templatePlanet)
                                        <option value="{{ $templatePlanet->id }}">
                                            {{ $templatePlanet->galaxy }}:{{ $templatePlanet->system }}:{{ $templatePlanet->position }} - 
                                            {{ ucfirst($templatePlanet->type) }} - 
                                            {{ $templatePlanet->size }} champs
                                        </option>
                                    @endforeach
                                </select>
                                @error('assignPlanet.template_planet_id') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="name">Nom de la planète</label>
                                <input type="text" wire:model="assignPlanet.name" id="name" class="admin-input">
                                @error('assignPlanet.name') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="is_main_planet">Planète principale</label>
                                <div class="admin-toggle-container">
                                    <label class="admin-toggle">
                                        <input type="checkbox" wire:model="assignPlanet.is_main_planet" id="is_main_planet">
                                        <span class="admin-toggle-slider"></span>
                                    </label>
                                    <span class="admin-toggle-label">{{ $assignPlanet['is_main_planet'] ? 'Oui' : 'Non' }}</span>
                                </div>
                                @error('assignPlanet.is_main_planet') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="admin-form-group">
                                <label for="is_active">Planète active</label>
                                <div class="admin-toggle-container">
                                    <label class="admin-toggle">
                                        <input type="checkbox" wire:model="assignPlanet.is_active" id="is_active">
                                        <span class="admin-toggle-slider"></span>
                                    </label>
                                    <span class="admin-toggle-label">{{ $assignPlanet['is_active'] ? 'Oui' : 'Non' }}</span>
                                </div>
                                @error('assignPlanet.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="button" wire:click="resetAssignPlanet" class="admin-button admin-button-secondary">
                                <i class="fas fa-times"></i> Réinitialiser
                            </button>
                            <button type="submit" class="admin-button admin-button-primary">
                                <i class="fas fa-save"></i> Affecter la planète
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Détail d'une planète -->
        @if($activeTab === 'detail' && $selectedPlanet)
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Détail de la planète #{{ $selectedPlanet->id }}</h2>
                    <div class="admin-card-actions">
                        <button class="admin-button admin-button-secondary" wire:click="$set('activeTab', 'list')">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </button>
                    </div>
                </div>
                
                <div class="admin-card-body">
                    <div class="admin-detail-info">
                        <div class="admin-detail-info-grid">
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">ID:</span>
                                <span class="admin-detail-value">{{ $selectedPlanet->id }}</span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Coordonnées:</span>
                                <span class="admin-detail-value admin-planet-coordinates">
                                    @if($selectedPlanet->templatePlanet)
                                        {{ $selectedPlanet->templatePlanet->galaxy }}:{{ $selectedPlanet->templatePlanet->system }}:{{ $selectedPlanet->templatePlanet->position }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Type:</span>
                                <span class="admin-detail-value">
                                    @if($selectedPlanet->templatePlanet)
                                        {{ ucfirst($selectedPlanet->templatePlanet->type) }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Taille:</span>
                                <span class="admin-detail-value">
                                    @if($selectedPlanet->templatePlanet)
                                        {{ $selectedPlanet->templatePlanet->size }} ({{ $selectedPlanet->used_fields }}/{{ $selectedPlanet->templatePlanet->fields }} champs)
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Propriétaire:</span>
                                <span class="admin-detail-value">
                                    @if($selectedPlanet->user)
                                        {{ $selectedPlanet->user->name }}
                                    @else
                                        <span class="admin-badge admin-badge-secondary">Non assignée</span>
                                    @endif
                                </span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Statut:</span>
                                <span class="admin-detail-value">
                                    @if($selectedPlanet->is_main_planet)
                                        <span class="admin-badge admin-badge-primary">Principale</span>
                                    @else
                                        <span class="admin-badge admin-badge-info">Colonie</span>
                                    @endif
                                </span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Activité:</span>
                                <span class="admin-detail-value">
                                    @if($selectedPlanet->is_active)
                                        <span class="admin-badge admin-badge-success">Active</span>
                                    @else
                                        <span class="admin-badge admin-badge-danger">Inactive</span>
                                    @endif
                                </span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Créée le:</span>
                                <span class="admin-detail-value">{{ $selectedPlanet->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="admin-detail-info-item">
                                <span class="admin-detail-label">Mise à jour:</span>
                                <span class="admin-detail-value">{{ $selectedPlanet->last_update ? $selectedPlanet->last_update->format('d/m/Y H:i:s') : 'Aucune' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="admin-tabs">
                <button class="admin-tab {{ $planetDetailTab === 'info' ? 'active' : '' }}" wire:click="setPlanetDetailTab('info')">
                    <i class="fas fa-info-circle"></i> Informations
                </button>
                <button class="admin-tab {{ $planetDetailTab === 'resources' ? 'active' : '' }}" wire:click="setPlanetDetailTab('resources')">
                    <i class="fas fa-cube"></i> Ressources
                </button>
                <button class="admin-tab {{ $planetDetailTab === 'buildings' ? 'active' : '' }}" wire:click="setPlanetDetailTab('buildings')">
                    <i class="fas fa-building"></i> Bâtiments
                </button>
                <button class="admin-tab {{ $planetDetailTab === 'units' ? 'active' : '' }}" wire:click="setPlanetDetailTab('units')">
                    <i class="fas fa-user-astronaut"></i> Unités
                </button>
                <button class="admin-tab {{ $planetDetailTab === 'ships' ? 'active' : '' }}" wire:click="setPlanetDetailTab('ships')">
                    <i class="fas fa-rocket"></i> Vaisseaux
                </button>
                <button class="admin-tab {{ $planetDetailTab === 'defenses' ? 'active' : '' }}" wire:click="setPlanetDetailTab('defenses')">
                    <i class="fas fa-shield-alt"></i> Défenses
                </button>
                <button class="admin-tab {{ $planetDetailTab === 'bunkers' ? 'active' : '' }}" wire:click="setPlanetDetailTab('bunkers')">
                    <i class="fas fa-warehouse"></i> Bunker
                </button>
                <button class="admin-tab {{ $planetDetailTab === 'missions' ? 'active' : '' }}" wire:click="setPlanetDetailTab('missions')">
                    <i class="fas fa-space-shuttle"></i> Missions
                </button>
            </div>

                <div class="admin-detail-content">
                    <!-- Onglet Informations -->
                    @if($planetDetailTab === 'info')
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Caractéristiques</h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="admin-detail-grid">
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Diamètre:</span>
                                        <span class="admin-detail-value">{{ number_format($selectedPlanet->templatePlanet->diameter, 0, ',', ' ') }} km</span>
                                    </div>
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Température:</span>
                                        <span class="admin-detail-value">{{ $selectedPlanet->templatePlanet->min_temperature }}°C à {{ $selectedPlanet->templatePlanet->max_temperature }}°C</span>
                                    </div>
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Champs:</span>
                                        <span class="admin-detail-value">{{ $selectedPlanet->used_fields }}/{{ $selectedPlanet->templatePlanet->fields }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Bonus de production</h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="admin-detail-grid">
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Métal:</span>
                                        <span class="admin-detail-value">{{ $selectedPlanet->templatePlanet->metal_bonus * 100 }}%</span>
                                    </div>
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Cristal:</span>
                                        <span class="admin-detail-value">{{ $selectedPlanet->templatePlanet->crystal_bonus * 100 }}%</span>
                                    </div>
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Deutérium:</span>
                                        <span class="admin-detail-value">{{ $selectedPlanet->templatePlanet->deuterium_bonus * 100 }}%</span>
                                    </div>
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Énergie:</span>
                                        <span class="admin-detail-value">{{ $selectedPlanet->templatePlanet->energy_bonus * 100 }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Bouclier planétaire</h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="admin-detail-grid">
                                    <div class="admin-detail-item">
                                        <span class="admin-detail-label">Statut:</span>
                                        <span class="admin-detail-value">
                                            @if($selectedPlanet->shield_active)
                                                <span class="admin-badge admin-badge-success">Actif</span>
                                                (Expire: {{ $selectedPlanet->shield_end_time->format('d/m/Y H:i') }})
                                            @else
                                                <span class="admin-badge admin-badge-danger">Inactif</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Onglet Ressources -->
                    @if($planetDetailTab === 'resources')
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Ressources de la planète</h3>
                            </div>
                            <div class="admin-card-body">
                                <!-- Formulaire d'ajout/retrait de ressources -->
                                <div class="admin-form-grid admin-form-grid-2">
                                    <div class="admin-form-group">
                                        <label for="resourceForm.resource_id" class="admin-form-label">Ressource</label>
                                        <select wire:model="resourceForm.resource_id" id="resourceForm.resource_id" class="admin-form-select">
                                            <option value="">Sélectionner une ressource</option>
                                            @foreach($planetResources as $resource)
                                                <option value="{{ $resource->resource_id }}">{{ $resource->resource->display_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('resourceForm.resource_id') <span class="admin-form-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="admin-form-group">
                                        <label for="resourceForm.amount" class="admin-form-label">Quantité</label>
                                        <input type="number" wire:model="resourceForm.amount" id="resourceForm.amount" class="admin-form-input" min="1">
                                        @error('resourceForm.amount') <span class="admin-form-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="admin-form-actions">
                                    <button type="button" wire:click="addResources" class="admin-button admin-button-success">Ajouter</button>
                                    <button type="button" wire:click="removeResources" class="admin-button admin-button-danger">Retirer</button>
                                </div>

                                <hr />
                                
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Ressource</th>
                                                <th>Quantité</th>
                                                <th>Production</th>
                                                <th>Dernière mise à jour</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($planetResources as $planetResource)
                                                <tr>
                                                    <td>{{ $planetResource->id }}</td>
                                                    <td>
                                                        @if($planetResource->resource)
                                                            <div class="admin-resource-name">
                                                                <div class="admin-resource-icon {{ strtolower($planetResource->resource->name) }}"></div>
                                                                {{ $planetResource->resource->display_name }}
                                                            </div>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($planetResource->current_amount, 0, ',', ' ') }}</td>
                                                    <td>
                                                        @php
                                                            $productionClass = 'neutral';
                                                            if ($planetResource->production_rate > 0) {
                                                                $productionClass = 'positive';
                                                            } elseif ($planetResource->production_rate < 0) {
                                                                $productionClass = 'negative';
                                                            }
                                                        @endphp
                                                        <span class="admin-production-indicator admin-production-{{ $productionClass }}">
                                                            {{ number_format($planetResource->production_rate, 2, ',', ' ') }} / h
                                                        </span>
                                                    </td>
                                                    <td>{{ $planetResource->last_update ? $planetResource->last_update->format('d/m/Y H:i:s') : 'Aucune' }}</td>
                                                    <td>
                                                        @if($planetResource->is_active)
                                                            <span class="admin-badge admin-badge-success">Active</span>
                                                        @else
                                                            <span class="admin-badge admin-badge-danger">Inactive</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="admin-table-empty">Aucune ressource trouvée</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Onglet Bâtiments -->
                    @if($planetDetailTab === 'buildings')
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Bâtiments de la planète</h3>
                            </div>
                            <div class="admin-card-body">
                                <!-- Formulaire d'ajout/retrait de niveaux de bâtiments -->
                                <div class="admin-form-grid admin-form-grid-2">
                                    <div class="admin-form-group">
                                        <label for="buildingForm.building_id" class="admin-form-label">Bâtiment</label>
                                        <select wire:model="buildingForm.building_id" id="buildingForm.building_id" class="admin-form-select">
                                            <option value="">Sélectionner un bâtiment</option>
                                            @foreach($availableBuildings as $buildTpl)
                                                <option value="{{ $buildTpl->id }}">{{ $buildTpl->label ?? $buildTpl->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('buildingForm.building_id') <span class="admin-form-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="admin-form-group">
                                        <label for="buildingForm.levels" class="admin-form-label">Niveaux</label>
                                        <input type="number" wire:model="buildingForm.levels" id="buildingForm.levels" class="admin-form-input" min="1">
                                        @error('buildingForm.levels') <span class="admin-form-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="admin-form-actions">
                                    <button type="button" wire:click="addBuildingLevels" class="admin-button admin-button-success">Ajouter niveaux</button>
                                    <button type="button" wire:click="removeBuildingLevels" class="admin-button admin-button-danger">Retirer niveaux</button>
                                </div>

                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Bâtiment</th>
                                                <th>Niveau</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($planetBuildings as $building)
                                                <tr>
                                                    <td>{{ $building->id }}</td>
                                                    <td>
                                                        @if($building->building)
                                                            <div class="admin-building-name">
                                                                <div class="admin-building-icon {{ strtolower(str_replace(' ', '-', $building->building->name)) }}"></div>
                                                                {{ $building->building->label }}
                                                            </div>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>{{ $building->level }}</td>
                                                    <td>
                                                        @if($building->is_active)
                                                            <span class="admin-badge admin-badge-success">Actif</span>
                                                        @else
                                                            <span class="admin-badge admin-badge-danger">Inactif</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="admin-table-empty">Aucun bâtiment trouvé</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Onglet Unités -->
                    @if($planetDetailTab === 'units')
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Unités de la planète</h3>
                            </div>
                            <div class="admin-card-body">
                                <!-- Formulaire d'ajout/retrait d'unités -->
                                <div class="admin-form-grid admin-form-grid-2">
                                    <div class="admin-form-group">
                                        <label for="unitForm.unit_id" class="admin-form-label">Unité</label>
                                        <select wire:model="unitForm.unit_id" id="unitForm.unit_id" class="admin-form-select">
                                            <option value="">Sélectionner une unité</option>
                                            @foreach($availableUnits as $unitTpl)
                                                <option value="{{ $unitTpl->id }}">{{ $unitTpl->label ?? $unitTpl->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('unitForm.unit_id') <span class="admin-form-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="admin-form-group">
                                        <label for="unitForm.quantity" class="admin-form-label">Quantité</label>
                                        <input type="number" wire:model="unitForm.quantity" id="unitForm.quantity" class="admin-form-input" min="1">
                                        @error('unitForm.quantity') <span class="admin-form-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="admin-form-actions">
                                    <button type="button" wire:click="addUnits" class="admin-button admin-button-success">Ajouter</button>
                                    <button type="button" wire:click="removeUnits" class="admin-button admin-button-danger">Retirer</button>
                                </div>

                                <hr />
                                
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Unité</th>
                                                <th>Quantité</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($planetUnits as $unit)
                                                <tr>
                                                    <td>{{ $unit->id }}</td>
                                                    <td>
                                                        @if($unit->unit)
                                                            <div class="admin-unit-name">
                                                                <div class="admin-unit-icon {{ strtolower(str_replace(' ', '-', $unit->unit->name)) }}"></div>
                                                                {{ $unit->unit->label }}
                                                            </div>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($unit->quantity, 0, ',', ' ') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="admin-table-empty">Aucune unité trouvée</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Onglet Vaisseaux -->
                    @if($planetDetailTab === 'ships')
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Vaisseaux de la planète</h3>
                            </div>
                            <div class="admin-card-body">
                                <!-- Formulaire d'ajout/retrait de vaisseaux -->
                                <div class="admin-form-grid admin-form-grid-2">
                                    <div class="admin-form-group">
                                        <label for="shipForm.ship_id" class="admin-form-label">Vaisseau</label>
                                        <select wire:model="shipForm.ship_id" id="shipForm.ship_id" class="admin-form-select">
                                            <option value="">Sélectionner un vaisseau</option>
                                            @foreach($availableShips as $shipTpl)
                                                <option value="{{ $shipTpl->id }}">{{ $shipTpl->label ?? $shipTpl->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('shipForm.ship_id') <span class="admin-form-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="admin-form-group">
                                        <label for="shipForm.quantity" class="admin-form-label">Quantité</label>
                                        <input type="number" wire:model="shipForm.quantity" id="shipForm.quantity" class="admin-form-input" min="1">
                                        @error('shipForm.quantity') <span class="admin-form-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="admin-form-actions">
                                    <button type="button" wire:click="addShips" class="admin-button admin-button-success">Ajouter</button>
                                    <button type="button" wire:click="removeShips" class="admin-button admin-button-danger">Retirer</button>
                                </div>

                                <hr />
                                
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Vaisseau</th>
                                                <th>Quantité</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($planetShips as $ship)
                                                <tr>
                                                    <td>{{ $ship->id }}</td>
                                                    <td>
                                                        @if($ship->ship)
                                                            <div class="admin-ship-name">
                                                                <div class="admin-ship-icon {{ strtolower(str_replace(' ', '-', $ship->ship->name)) }}"></div>
                                                                {{ $ship->ship->label }}
                                                            </div>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($ship->quantity, 0, ',', ' ') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="admin-table-empty">Aucun vaisseau trouvé</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Onglet Défenses -->
                    @if($planetDetailTab === 'defenses')
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Défenses de la planète</h3>
                            </div>
                            <div class="admin-card-body">
                                <!-- Formulaire d'ajout/retrait de défenses -->
                                <div class="admin-form-grid admin-form-grid-2">
                                    <div class="admin-form-group">
                                        <label for="defenseForm.defense_id" class="admin-form-label">Défense</label>
                                        <select wire:model="defenseForm.defense_id" id="defenseForm.defense_id" class="admin-form-select">
                                            <option value="">Sélectionner une défense</option>
                                            @foreach($availableDefenses as $defTpl)
                                                <option value="{{ $defTpl->id }}">{{ $defTpl->label ?? $defTpl->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('defenseForm.defense_id') <span class="admin-form-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="admin-form-group">
                                        <label for="defenseForm.quantity" class="admin-form-label">Quantité</label>
                                        <input type="number" wire:model="defenseForm.quantity" id="defenseForm.quantity" class="admin-form-input" min="1">
                                        @error('defenseForm.quantity') <span class="admin-form-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="admin-form-actions">
                                    <button type="button" wire:click="addDefenses" class="admin-button admin-button-success">Ajouter</button>
                                    <button type="button" wire:click="removeDefenses" class="admin-button admin-button-danger">Retirer</button>
                                </div>

                                <hr />
                                
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Défense</th>
                                                <th>Quantité</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($planetDefenses as $defense)
                                                <tr>
                                                    <td>{{ $defense->id }}</td>
                                                    <td>
                                                        @if($defense->defense)
                                                            <div class="admin-defense-name">
                                                                <div class="admin-defense-icon {{ strtolower(str_replace(' ', '-', $defense->defense->name)) }}"></div>
                                                                {{ $defense->defense->name }}
                                                            </div>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($defense->quantity, 0, ',', ' ') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="admin-table-empty">Aucune défense trouvée</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Onglet Bunker -->
                    @if($planetDetailTab === 'bunkers')
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Ressources dans le bunker</h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Ressource</th>
                                                <th>Quantité</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($planetBunkers as $planetBunker)
                                                <tr>
                                                    <td>{{ $planetBunker->id }}</td>
                                                    <td>
                                                        @if($planetBunker->resource)
                                                            <div class="admin-resource-name">
                                                                <div class="admin-resource-icon {{ strtolower(str_replace(' ', '-', $planetBunker->resource->name)) }}"></div>
                                                                {{ $planetBunker->resource->name }}
                                                            </div>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($planetBunker->amount, 0, ',', ' ') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="admin-table-empty">Aucune ressource dans le bunker</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Onglet Missions -->
                    @if($planetDetailTab === 'missions')
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3>Missions liées à la planète</h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="admin-table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Type</th>
                                                <th>Origine</th>
                                                <th>Destination</th>
                                                <th>Utilisateur</th>
                                                <th>Départ</th>
                                                <th>Arrivée</th>
                                                <th>Retour</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($planetMissions as $mission)
                                                <tr>
                                                    <td>{{ $mission->id }}</td>
                                                    <td>{{ $mission->mission_type }}</td>
                                                    <td>
                                                        @if($mission->fromPlanet)
                                                            <div class="admin-planet-coordinates">{{ $mission->fromPlanet->name }}</div>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($mission->toPlanet)
                                                            <div class="admin-planet-coordinates">{{ $mission->toPlanet->name }}</div>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($mission->user)
                                                            <div class="admin-user-name">{{ $mission->user->name }}</div>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($mission->departure_time)
                                                            {{ $mission->departure_time->format('d/m/Y H:i:s') }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($mission->arrival_time)
                                                            {{ $mission->arrival_time->format('d/m/Y H:i:s') }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($mission->return_time)
                                                            {{ $mission->return_time->format('d/m/Y H:i:s') }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($mission->status === 'completed')
                                                            <span class="admin-badge admin-badge-success">Terminée</span>
                                                        @elseif($mission->status === 'returning')
                                                            <span class="admin-badge admin-badge-info">Retour</span>
                                                        @else
                                                            <span class="admin-badge admin-badge-warning">En cours</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="admin-table-empty">Aucune mission trouvée</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="admin-pagination">
                                    {{ $planetMissions->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>