<div class="admin-buildings">
    <div class="admin-page-header">
        <h1>Gestion des bâtiments</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button {{ $activeTab === 'list' ? 'active' : '' }}" wire:click="setActiveTab('list')">
                <i class="fas fa-building"></i> Liste des bâtiments
            </button>
            <button class="admin-tab-button {{ $activeTab === 'create' ? 'active' : '' }}" wire:click="setActiveTab('create')">
                <i class="fas fa-plus-circle"></i> Créer un bâtiment
            </button>
            @if($selectedBuild)
                <button class="admin-tab-button {{ $activeTab === 'edit' ? 'active' : '' }}" wire:click="setActiveTab('edit')">
                    <i class="fas fa-edit"></i> {{ $selectedBuild->name }}
                </button>
            @endif
        </div>
    </div>

    <div class="admin-content-body">

        <!-- Liste des bâtiments -->
        @if($activeTab === 'list')
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des bâtiments</h2>
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
                                @foreach($buildTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-filter-group">
                            <label for="filterCategory">Catégorie:</label>
                            <select id="filterCategory" wire:model.live="filterCategory" class="admin-select">
                                <option value="">Toutes</option>
                                @foreach($buildCategories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
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
                                    <th wire:click="sortBy('label')" class="admin-sortable">
                                        Label
                                        @if($sortField === 'label')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('type')" class="admin-sortable">
                                        Type
                                        @if($sortField === 'type')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('category')" class="admin-sortable">
                                        Catégorie
                                        @if($sortField === 'category')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('max_level')" class="admin-sortable">
                                        Niveau Max
                                        @if($sortField === 'max_level')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th>Utilisation</th>
                                    <th wire:click="sortBy('is_active')" class="admin-sortable">
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
                                @foreach($builds as $build)
                                    <tr>
                                        <td>{{ $build->id }}</td>
                                        <td>{{ $build->name }}</td>
                                        <td>{{ $build->label }}</td>
                                        <td>{{ $buildTypes[$build->type] ?? $build->type }}</td>
                                        <td>{{ $buildCategories[$build->category] ?? $build->category }}</td>
                                        <td>{{ $build->max_level }}</td>
                                        <td>{{ $planetCounts[$build->id] ?? 0 }}</td>
                                        <td>
                                            @if($build->is_active)
                                                <span class="admin-badge admin-badge-success">Actif</span>
                                            @else
                                                <span class="admin-badge admin-badge-danger">Inactif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="admin-btn admin-btn-sm admin-btn-primary" wire:click="selectBuild({{ $build->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                
                                @if($builds->count() === 0)
                                    <tr>
                                        <td colspan="9" class="admin-table-empty">Aucun bâtiment trouvé</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="admin-pagination">
                        {{ $builds->links() }}
                    </div>
                </div>
            </div>
        @endif
                
        <!-- Créer un bâtiment -->
        @if($activeTab === 'create')
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Créer un bâtiment</h2>
                </div>
                <div class="admin-card-body">
                    <form wire:submit.prevent="createBuild">
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="name">Nom <span class="admin-required">*</span></label>
                                <input type="text" id="name" wire:model="buildForm.name" class="admin-input">
                                @error('buildForm.name') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="admin-form-group">
                                <label for="label">Label <span class="admin-required">*</span></label>
                                <input type="text" id="label" wire:model="buildForm.label" class="admin-input">
                                @error('buildForm.label') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="type">Type <span class="admin-required">*</span></label>
                                <select id="type" wire:model="buildForm.type" class="admin-select">
                                    <option value="">Sélectionner un type</option>
                                    @foreach($buildTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('buildForm.type') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="admin-form-group">
                                <label for="category">Catégorie <span class="admin-required">*</span></label>
                                <select id="category" wire:model="buildForm.category" class="admin-select">
                                    <option value="">Sélectionner une catégorie</option>
                                    @foreach($buildCategories as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('buildForm.category') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-row">
                            <div class="admin-form-group">
                                <label for="icon">Icône</label>
                                <input type="text" id="icon" wire:model="buildForm.icon" class="admin-input">
                                @error('buildForm.icon') <span class="admin-error">{{ $message }}</span> @enderror
                                @if($buildForm['icon'])
                                    <div class="admin-icon-preview">
                                        <i class="{{ $buildForm['icon'] }} fa-2x"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="admin-form-group">
                                <label for="max_level">Niveau Max <span class="admin-required">*</span></label>
                                <input type="number" id="max_level" wire:model="buildForm.max_level" min="1" class="admin-input">
                                @error('buildForm.max_level') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="admin-form-group">
                                <label for="base_build_time">Temps de construction de base <span class="admin-required">*</span></label>
                                <input type="number" id="base_build_time" wire:model="buildForm.base_build_time" min="0" class="admin-input">
                                @error('buildForm.base_build_time') <span class="admin-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="admin-form-group">
                            <div class="admin-switch">
                                <input type="checkbox" id="is_active" wire:model="buildForm.is_active">
                                <label for="is_active">Actif</label>
                            </div>
                            @error('buildForm.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="description">Description</label>
                            <textarea id="description" wire:model="buildForm.description" rows="3" class="admin-textarea"></textarea>
                            @error('buildForm.description') <span class="admin-error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="admin-form-actions">
                            <button type="button" class="admin-btn admin-btn-secondary" wire:click="setActiveTab('list')">Annuler</button>
                            <button type="submit" class="admin-btn admin-btn-primary">Créer</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
                
                <!-- Éditer un bâtiment -->
                <div class="tab-pane fade {{ $activeTab === 'edit' && $selectedBuild ? 'show active' : '' }}">
                    @if($selectedBuild)
                        <div x-data="{ activeTab: 'details' }">
                            <div class="admin-tabs">
                                <ul class="admin-tabs-nav" role="tablist">
                                    <li class="admin-tabs-item" role="presentation">
                                        <button class="admin-tabs-link" :class="{ 'active': activeTab === 'details' }" @click="activeTab = 'details'" type="button">
                                            Détails
                                        </button>
                                    </li>
                                    <li class="admin-tabs-item" role="presentation">
                                        <button class="admin-tabs-link" :class="{ 'active': activeTab === 'costs' }" @click="activeTab = 'costs'" type="button">
                                            Coûts
                                        </button>
                                    </li>
                                    <li class="admin-tabs-item" role="presentation">
                                        <button class="admin-tabs-link" :class="{ 'active': activeTab === 'requirements' }" @click="activeTab = 'requirements'" type="button">
                                            Prérequis
                                        </button>
                                    </li>
                                    <li class="admin-tabs-item" role="presentation">
                                        <button class="admin-tabs-link" :class="{ 'active': activeTab === 'advantages' }" @click="activeTab = 'advantages'" type="button">
                                            Avantages
                                        </button>
                                    </li>
                                    <li class="admin-tabs-item" role="presentation">
                                        <button class="admin-tabs-link" :class="{ 'active': activeTab === 'disadvantages' }" @click="activeTab = 'disadvantages'" type="button">
                                            Désavantages
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="tab-content">
                            <!-- Détails du bâtiment -->
                            <div x-show="activeTab === 'details'">
                                <div class="admin-card">
                                    <div class="admin-card-header">
                                        <h2>Détails du bâtiment</h2>
                                    </div>
                                    <div class="admin-card-body">
                                        <form wire:submit.prevent="updateBuild">
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="edit_name">Nom <span class="admin-required">*</span></label>
                                                    <input type="text" id="edit_name" wire:model="buildForm.name" class="admin-input">
                                                    @error('buildForm.name') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="edit_label">Label <span class="admin-required">*</span></label>
                                                    <input type="text" id="edit_label" wire:model="buildForm.label" class="admin-input">
                                                    @error('buildForm.label') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="edit_type">Type <span class="admin-required">*</span></label>
                                                    <select id="edit_type" wire:model="buildForm.type" class="admin-select">
                                                        <option value="">Sélectionner un type</option>
                                                        @foreach($buildTypes as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('buildForm.type') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="edit_category">Catégorie <span class="admin-required">*</span></label>
                                                    <select id="edit_category" wire:model="buildForm.category" class="admin-select">
                                                        <option value="">Sélectionner une catégorie</option>
                                                        @foreach($buildCategories as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('buildForm.category') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="edit_icon">Icône</label>
                                                    <input type="text" id="edit_icon" wire:model="buildForm.icon" class="admin-input">
                                                    @error('buildForm.icon') <span class="admin-error">{{ $message }}</span> @enderror
                                                    @if($buildForm['icon'])
                                                        <div class="admin-icon-preview">
                                                            <i class="{{ $buildForm['icon'] }} fa-2x"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="edit_max_level">Niveau Max <span class="admin-required">*</span></label>
                                                    <input type="number" id="edit_max_level" wire:model="buildForm.max_level" min="1" class="admin-input">
                                                    @error('buildForm.max_level') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="edit_base_build_time">Temps de construction de base <span class="admin-required">*</span></label>
                                                    <input type="number" id="edit_base_build_time" wire:model="buildForm.base_build_time" min="0" class="admin-input">
                                                    @error('buildForm.base_build_time') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="admin-form-group">
                                                <div class="admin-switch">
                                                    <input type="checkbox" id="edit_is_active" wire:model="buildForm.is_active">
                                                    <label for="edit_is_active">Actif</label>
                                                </div>
                                                @error('buildForm.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="admin-form-group">
                                                <label for="edit_description">Description</label>
                                                <textarea id="edit_description" wire:model="buildForm.description" rows="3" class="admin-textarea"></textarea>
                                                @error('buildForm.description') <span class="admin-error">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="admin-form-actions">
                                                <button type="button" class="admin-btn admin-btn-secondary" wire:click="setActiveTab('list')">Retour à la liste</button>
                                                <button type="submit" class="admin-btn admin-btn-primary">Mettre à jour</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Coûts du bâtiment -->
                            <div x-show="activeTab === 'costs'">
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="admin-card">
                                            <div class="admin-card-header">
                                                <h2>Ajouter un coût</h2>
                                            </div>
                                            <div class="admin-card-body">
                                                <form wire:submit.prevent="addCost">
                                                    <div class="admin-form-row">
                                                        <div class="admin-form-group">
                                                            <label for="cost_resource_id">Ressource <span class="admin-required">*</span></label>
                                                            <select class="admin-select" id="cost_resource_id" wire:model="newCost.resource_id">
                                                                <option value="">Sélectionner une ressource</option>
                                                                @foreach($resources as $resource)
                                                                    <option value="{{ $resource->id }}">{{ $resource->label }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('newCost.resource_id') <span class="admin-error">{{ $message }}</span> @enderror
                                                        </div>
                                                        <div class="admin-form-group">
                                                            <label for="cost_base_cost">Coût de base <span class="admin-required">*</span></label>
                                                            <input type="number" class="admin-input" id="cost_base_cost" wire:model="newCost.base_cost" min="0">
                                                            @error('newCost.base_cost') <span class="admin-error">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    <div class="admin-form-row">
                                                        <div class="admin-form-group">
                                                            <label for="cost_multiplier">Multiplicateur <span class="admin-required">*</span></label>
                                                            <input type="number" class="admin-input" id="cost_multiplier" wire:model="newCost.cost_multiplier" min="1" step="0.1">
                                                            @error('newCost.cost_multiplier') <span class="admin-error">{{ $message }}</span> @enderror
                                                        </div>
                                                        <div class="admin-form-group">
                                                            <label for="cost_level">Niveau <span class="admin-required">*</span></label>
                                                            <input type="number" class="admin-input" id="cost_level" wire:model="newCost.level" min="1">
                                                            @error('newCost.level') <span class="admin-error">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    <div class="admin-form-actions">
                                                        <button type="submit" class="admin-btn admin-btn-primary">Ajouter</button>
                                                    </div>
                                                </form>
                                            
                                                <br />
                                
                                                <div class="admin-table-container">
                                                    <table class="admin-table">
                                                        <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Ressource</th>
                                                                <th>Coût de base</th>
                                                                <th>Multiplicateur</th>
                                                                <th>Niveau</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($costs as $cost)
                                                                <tr>
                                                                    <td>{{ $cost['id'] }}</td>
                                                                    <td>
                                                                        @php
                                                                            $resource = $resources->firstWhere('id', $cost['resource_id']);
                                                                        @endphp
                                                                        {{ $resource ? $resource->label : 'Ressource inconnue' }}
                                                                    </td>
                                                                    <td>{{ $cost['base_cost'] }}</td>
                                                                    <td>{{ $cost['cost_multiplier'] }}</td>
                                                                    <td>{{ $cost['level'] }}</td>
                                                                    <td>
                                                                        <button class="admin-btn admin-btn-danger admin-btn-sm" wire:click="deleteCost({{ $cost['id'] }})" wire:confirm="Êtes-vous sûr de vouloir supprimer ce coût ?">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="6" class="admin-table-empty">Aucun coût défini</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Prérequis du bâtiment -->
                            <div x-show="activeTab === 'requirements'">
                                <div class="admin-card">
                                    <div class="admin-card-header">
                                        <h2>Ajouter un prérequis</h2>
                                    </div>
                                    <div class="admin-card-body">
                                        <form wire:submit.prevent="addRequirement">
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="required_build_id">Bâtiment requis <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="required_build_id" wire:model="newRequirement.required_build_id">
                                                        <option value="">Sélectionner un bâtiment</option>
                                                        @foreach($availableBuilds as $build)
                                                            <option value="{{ $build->id }}">{{ $build->label }} ({{ $buildTypes[$build->type] ?? $build->type }})</option>
                                                        @endforeach
                                                    </select>
                                                    @error('newRequirement.required_build_id') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="required_level">Niveau requis <span class="admin-required">*</span></label>
                                                    <input type="number" class="admin-input" id="required_level" wire:model="newRequirement.required_level" min="1">
                                                    @error('newRequirement.required_level') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="admin-form-row">
                                                <div class="admin-form-group admin-form-checkbox-group">
                                                    <div class="admin-checkbox-container">
                                                        <input type="checkbox" id="requirement_is_active" wire:model="newRequirement.is_active" class="admin-checkbox">
                                                        <label for="requirement_is_active">Actif</label>
                                                    </div>
                                                    @error('newRequirement.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="admin-form-actions">
                                                <button type="submit" class="admin-btn admin-btn-primary">Ajouter</button>
                                            </div>
                                        </form>

                                        <br />
                                
                                        <div class="admin-table-container">
                                            <table class="admin-table">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Bâtiment requis</th>
                                                        <th>Niveau requis</th>
                                                        <th>Statut</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($requirements as $requirement)
                                                        <tr>
                                                            <td>{{ $requirement['id'] }}</td>
                                                            <td>
                                                                @php
                                                                    $requiredBuild = $availableBuilds->firstWhere('id', $requirement['required_build_id']);
                                                                @endphp
                                                                {{ $requiredBuild ? $requiredBuild->label : 'Bâtiment inconnu' }}
                                                                @if($requiredBuild)
                                                                    ({{ $buildTypes[$requiredBuild->type] ?? $requiredBuild->type }})
                                                                @endif
                                                            </td>
                                                            <td>{{ $requirement['required_level'] }}</td>
                                                            <td>
                                                                <span class="admin-badge {{ $requirement['is_active'] ? 'admin-badge-success' : 'admin-badge-danger' }}">
                                                                    {{ $requirement['is_active'] ? 'Actif' : 'Inactif' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button class="admin-btn admin-btn-danger admin-btn-sm" wire:click="deleteRequirement({{ $requirement['id'] }})" wire:confirm="Êtes-vous sûr de vouloir supprimer ce prérequis ?">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="admin-table-empty">Aucun prérequis défini</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Avantages du bâtiment -->
                            <div x-show="activeTab === 'advantages'">
                                <div class="admin-card">
                                    <div class="admin-card-header">
                                        <h2>Ajouter un avantage</h2>
                                    </div>
                                    <div class="admin-card-body">
                                        <form wire:submit.prevent="addAdvantage">
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="advantage_type">Type d'avantage <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="advantage_type" wire:model="newAdvantage.advantage_type">
                                                        <option value="">Sélectionner un type</option>
                                                        @foreach($advantageTypes as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('newAdvantage.advantage_type') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="advantage_target_type">Type de cible <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="advantage_target_type" wire:model="newAdvantage.target_type">
                                                        <option value="">Sélectionner un type de cible</option>
                                                        @foreach($targetTypes as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('newAdvantage.target_type') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="advantage_resource_id">Ressource (optionnel)</label>
                                                    <select class="admin-select" id="advantage_resource_id" wire:model="newAdvantage.resource_id">
                                                        <option value="">Aucune ressource spécifique</option>
                                                        @foreach($resources as $resource)
                                                            <option value="{{ $resource->id }}">{{ $resource->label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('newAdvantage.resource_id') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="advantage_calculation_type">Type de calcul <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="advantage_calculation_type" wire:model="newAdvantage.calculation_type">
                                                        <option value="">Sélectionner un type de calcul</option>
                                                        @foreach($calculationTypes as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('newAdvantage.calculation_type') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="advantage_base_value">Valeur de base <span class="admin-required">*</span></label>
                                                    <input type="number" class="admin-input" id="advantage_base_value" wire:model="newAdvantage.base_value" min="0" step="0.01">
                                                    @error('newAdvantage.base_value') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="advantage_value_per_level">Valeur par niveau <span class="admin-required">*</span></label>
                                                    <input type="number" class="admin-input" id="advantage_value_per_level" wire:model="newAdvantage.value_per_level" min="0" step="0.01">
                                                    @error('newAdvantage.value_per_level') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="admin-form-row">
                                                <div class="admin-form-group admin-form-checkbox-group">
                                                    <div class="admin-checkbox-container">
                                                        <input type="checkbox" id="advantage_is_percentage" wire:model="newAdvantage.is_percentage" class="admin-checkbox">
                                                        <label for="advantage_is_percentage">Est un pourcentage</label>
                                                    </div>
                                                    @error('newAdvantage.is_percentage') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="admin-form-group admin-form-checkbox-group">
                                                    <div class="admin-checkbox-container">
                                                        <input type="checkbox" id="advantage_is_active" wire:model="newAdvantage.is_active" class="admin-checkbox">
                                                        <label for="advantage_is_active">Actif</label>
                                                    </div>
                                                    @error('newAdvantage.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="admin-form-actions">
                                                <button type="submit" class="admin-btn admin-btn-primary">Ajouter</button>
                                            </div>
                                        </form>

                                        <br />
                                        <div class="admin-table-container">
                                            <table class="admin-table">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Type</th>
                                                        <th>Cible</th>
                                                        <th>Ressource</th>
                                                        <th>Valeur de base</th>
                                                        <th>Valeur par niveau</th>
                                                        <th>Type de calcul</th>
                                                        <th>Pourcentage</th>
                                                        <th>Statut</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($advantages as $advantage)
                                                        <tr>
                                                            <td>{{ $advantage['id'] }}</td>
                                                            <td>{{ $advantageTypes[$advantage['advantage_type']] ?? $advantage['advantage_type'] }}</td>
                                                            <td>{{ $targetTypes[$advantage['target_type']] ?? $advantage['target_type'] }}</td>
                                                            <td>
                                                                @if($advantage['resource_id'])
                                                                    @php
                                                                        $resource = $resources->firstWhere('id', $advantage['resource_id']);
                                                                    @endphp
                                                                    {{ $resource ? $resource->label : 'Ressource inconnue' }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>{{ $advantage['base_value'] }}{{ $advantage['is_percentage'] ? '%' : '' }}</td>
                                                            <td>{{ $advantage['value_per_level'] }}{{ $advantage['is_percentage'] ? '%' : '' }}</td>
                                                            <td>{{ $calculationTypes[$advantage['calculation_type']] ?? $advantage['calculation_type'] }}</td>
                                                            <td>
                                                                <span class="admin-badge {{ $advantage['is_percentage'] ? 'admin-badge-info' : 'admin-badge-secondary' }}">
                                                                    {{ $advantage['is_percentage'] ? 'Oui' : 'Non' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="admin-badge {{ $advantage['is_active'] ? 'admin-badge-success' : 'admin-badge-danger' }}">
                                                                    {{ $advantage['is_active'] ? 'Actif' : 'Inactif' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button class="admin-btn admin-btn-danger admin-btn-sm" wire:click="deleteAdvantage({{ $advantage['id'] }})" wire:confirm="Êtes-vous sûr de vouloir supprimer cet avantage ?">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="10" class="admin-table-empty">Aucun avantage défini</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Désavantages du bâtiment -->
                            <div x-show="activeTab === 'disadvantages'">
                                <div class="admin-card">
                                    <div class="admin-card-header">
                                        <h2>Ajouter un désavantage</h2>
                                    </div>
                                    <div class="admin-card-body">
                                        <form wire:submit.prevent="addDisadvantage">
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="disadvantage_type">Type de désavantage <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="disadvantage_type" wire:model="newDisadvantage.disadvantage_type">
                                                        <option value="">Sélectionner un type</option>
                                                        @foreach($disadvantageTypes as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('newDisadvantage.disadvantage_type') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="disadvantage_target_type">Type de cible <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="disadvantage_target_type" wire:model="newDisadvantage.target_type">
                                                        <option value="">Sélectionner un type de cible</option>
                                                        @foreach($targetTypes as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                                @error('newDisadvantage.target_type') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="disadvantage_resource_id">Ressource (optionnel)</label>
                                                    <select class="admin-select" id="disadvantage_resource_id" wire:model="newDisadvantage.resource_id">
                                                        <option value="">Aucune ressource spécifique</option>
                                                        @foreach($resources as $resource)
                                                            <option value="{{ $resource->id }}">{{ $resource->label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('newDisadvantage.resource_id') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="disadvantage_calculation_type">Type de calcul <span class="admin-required">*</span></label>
                                                    <select class="admin-select" id="disadvantage_calculation_type" wire:model="newDisadvantage.calculation_type">
                                                        <option value="">Sélectionner un type de calcul</option>
                                                        @foreach($calculationTypes as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('newDisadvantage.calculation_type') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="admin-form-row">
                                                <div class="admin-form-group">
                                                    <label for="disadvantage_base_value">Valeur de base <span class="admin-required">*</span></label>
                                                    <input type="number" class="admin-input" id="disadvantage_base_value" wire:model="newDisadvantage.base_value" min="0" step="0.01">
                                                    @error('newDisadvantage.base_value') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="admin-form-group">
                                                    <label for="disadvantage_value_per_level">Valeur par niveau <span class="admin-required">*</span></label>
                                                    <input type="number" class="admin-input" id="disadvantage_value_per_level" wire:model="newDisadvantage.value_per_level" min="0" step="0.01">
                                                    @error('newDisadvantage.value_per_level') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="admin-form-row">
                                                <div class="admin-form-group admin-form-checkbox-group">
                                                    <div class="admin-checkbox-container">
                                                        <input type="checkbox" id="disadvantage_is_percentage" wire:model="newDisadvantage.is_percentage" class="admin-checkbox">
                                                        <label for="disadvantage_is_percentage">Est un pourcentage</label>
                                                    </div>
                                                    @error('newDisadvantage.is_percentage') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="admin-form-group admin-form-checkbox-group">
                                                    <div class="admin-checkbox-container">
                                                        <input type="checkbox" id="disadvantage_is_active" wire:model="newDisadvantage.is_active" class="admin-checkbox">
                                                        <label for="disadvantage_is_active">Actif</label>
                                                    </div>
                                                    @error('newDisadvantage.is_active') <span class="admin-error">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="admin-form-actions">
                                                <button type="submit" class="admin-btn admin-btn-primary">Ajouter</button>
                                            </div>
                                        </form>

                                        <br />
                                
                                        <div class="admin-table-container">
                                            <table class="admin-table">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Type</th>
                                                        <th>Cible</th>
                                                        <th>Ressource</th>
                                                        <th>Valeur de base</th>
                                                        <th>Valeur par niveau</th>
                                                        <th>Type de calcul</th>
                                                        <th>Pourcentage</th>
                                                        <th>Statut</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($disadvantages as $disadvantage)
                                                        <tr>
                                                            <td>{{ $disadvantage['id'] }}</td>
                                                            <td>{{ $disadvantageTypes[$disadvantage['disadvantage_type']] ?? $disadvantage['disadvantage_type'] }}</td>
                                                            <td>{{ $targetTypes[$disadvantage['target_type']] ?? $disadvantage['target_type'] }}</td>
                                                            <td>
                                                                @if($disadvantage['resource_id'])
                                                                    @php
                                                                        $resource = $resources->firstWhere('id', $disadvantage['resource_id']);
                                                                    @endphp
                                                                    {{ $resource ? $resource->label : 'Ressource inconnue' }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>{{ $disadvantage['base_value'] }}{{ $disadvantage['is_percentage'] ? '%' : '' }}</td>
                                                            <td>{{ $disadvantage['value_per_level'] }}{{ $disadvantage['is_percentage'] ? '%' : '' }}</td>
                                                            <td>{{ $calculationTypes[$disadvantage['calculation_type']] ?? $disadvantage['calculation_type'] }}</td>
                                                            <td>
                                                                <span class="admin-badge {{ $disadvantage['is_percentage'] ? 'admin-badge-info' : 'admin-badge-secondary' }}">
                                                                    {{ $disadvantage['is_percentage'] ? 'Oui' : 'Non' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="admin-badge {{ $disadvantage['is_active'] ? 'admin-badge-success' : 'admin-badge-danger' }}">
                                                                    {{ $disadvantage['is_active'] ? 'Actif' : 'Inactif' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button class="admin-btn admin-btn-sm admin-btn-danger" wire:click="deleteDisadvantage({{ $disadvantage['id'] }})" wire:confirm="Êtes-vous sûr de vouloir supprimer ce désavantage ?">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="10" class="admin-table-empty">Aucun désavantage défini</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>