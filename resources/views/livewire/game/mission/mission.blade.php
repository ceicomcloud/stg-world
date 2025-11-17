<div page="mission">
    <div class="mission-container">
        <div class="mission-header">
            <h2 class="mission-title">
                <i class="fas fa-rocket"></i>
                Centre de Mission
            </h2>
        </div>
        
        <!-- Compteur des flottes en vol -->
        <div class="mission-status" style="margin: 8px 0 12px 0; display:flex; align-items:center; gap:8px;">
            <span class="mission-counter-badge" title="Limite basée sur le Centre de Commandement">
                Flottes en vol : {{ $fleetCurrent }} / {{ $fleetLimit }}
            </span>
        </div>
        
        <div class="mission-tabs" style="display:flex; gap:8px; margin-bottom:12px;">
            <button class="mission-btn {{ $activeTab === 'mission' ? 'mission-btn-primary' : 'mission-btn-outline' }}" wire:click="switchTab('mission')">Missions</button>
            <button class="mission-btn {{ $activeTab === 'bookmarks' ? 'mission-btn-primary' : 'mission-btn-outline' }}" wire:click="switchTab('bookmarks')">Bookmarks</button>
        </div>

        @if($activeTab === 'mission')
        <div class="mission-content">
            <div class="mission-form">
                <!-- Sélecteur de planètes et coordonnées -->
                <div class="mission-form-row">
                    <!-- Sélecteur de planètes -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">
                            <i class="fas fa-bookmark"></i>
                            Sélection rapide
                        </label>
                        <div class="mission-planet-selector">
                            <select class="mission-form-control" wire:model.live="selectedSourcePlanet" wire:change="selectSourcePlanet($event.target.value)">
                                <option value="">-- Sélectionner une planète --</option>
                                @foreach($userPlanets as $planet)
                                    <option value="{{ $planet['id'] }}">
                                        {{ $planet['name'] }} [{{ $planet['galaxy'] }}:{{ $planet['system'] }}:{{ $planet['position'] }}]
                                        @if($planet['is_current']) (Planète actuelle) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="mission-form-help">Sélectionnez une de vos planètes comme destination </p>
                    </div>
                    
                    <!-- Coordonnées de la cible -->
                    <div class="mission-form-group">
                        <label class="mission-form-label">
                            <i class="fas fa-crosshairs"></i>
                            Coordonnées de la cible
                        </label>
                        <div class="mission-coordinates-container">
                            <div class="mission-coordinates">
                                <div class="coordinate-group">
                                    <label class="coordinate-label">Galaxie</label>
                                    <input type="number" class="mission-form-control mission-coordinate-input" wire:model.live="targetGalaxy" min="1" max="9">
                                </div>
                                <span class="mission-coordinate-separator">:</span>
                                <div class="coordinate-group">
                                    <label class="coordinate-label">Système</label>
                                    <input type="number" class="mission-form-control mission-coordinate-input" wire:model.live="targetSystem" min="1" max="1000">
                                </div>
                                <span class="mission-coordinate-separator">:</span>
                                <div class="coordinate-group">
                                    <label class="coordinate-label">Position</label>
                                    <input type="number" class="mission-form-control mission-coordinate-input" wire:model.live="targetPosition" min="1" max="10">
                                </div>
                            </div>
                        </div>
                        <p class="mission-form-help">Entrez les coordonnées de la planète cible</p>
                    </div>
                </div>

                <!-- Accès rapide aux Bookmarks (sélection uniquement) -->
                <div class="mission-form-group">
                    <label class="mission-form-label">
                        <i class="fas fa-bookmark"></i>
                        Accès rapide Bookmarks
                    </label>
                    <div class="mission-planet-selector">
                        <select class="mission-form-control" wire:change="selectBookmark($event.target.value)">
                            <option value="">-- Sélectionner un bookmark --</option>
                            @foreach($bookmarks as $b)
                                <option value="{{ $b['id'] }}">{{ $b['label'] }} [{{ $b['galaxy'] }}:{{ $b['system'] }}:{{ $b['position'] }}]</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="mission-form-help">L'ajout de bookmarks se fait depuis la fenêtre PlanetInfo.</p>
                </div>

                <!-- Sélection du type de mission -->
                <div class="mission-form-group mission-type-section">
                    <label class="mission-form-label">
                        <i class="fas fa-space-shuttle"></i>
                        Type de mission
                    </label>
                    <p class="mission-form-help">Sélectionnez le type de mission que vous souhaitez lancer</p>
                    
                    <div class="mission-types-grid">
                        <!-- Attaque Spatiale -->
                        <div class="mission-type-card {{ $missionType === 'attack_spatial' ? 'selected' : '' }} {{ !$canAttackSpatial ? 'disabled' : '' }}" 
                             wire:click="{{ $canAttackSpatial ? "selectMissionType('attack_spatial')" : '' }}" style="--i: 0;">
                            <div class="mission-type-icon">
                                <i class="fas fa-fighter-jet"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Attaque Spatiale</h4>
                                <p>Envoyez vos vaisseaux de combat pour attaquer une planète ennemie.</p>
                            </div>
                            @if(!$canAttackSpatial)
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucun vaisseau de combat disponible</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Attaque Terrestre -->
                        <div class="mission-type-card {{ $missionType === 'attack_earth' ? 'selected' : '' }} {{ !$canAttackEarth ? 'disabled' : '' }}" 
                             wire:click="{{ $canAttackEarth ? "selectMissionType('attack_earth')" : '' }}" style="--i: 1;">
                            <div class="mission-type-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Attaque Terrestre</h4>
                                <p>Envoyez vos unités terrestres pour attaquer une planète ennemie.</p>
                            </div>
                            @if(!$canAttackEarth)
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucune unité terrestre disponible</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Espionnage -->
                        <div class="mission-type-card {{ $missionType === 'spy' ? 'selected' : '' }} {{ !$canSpy ? 'disabled' : '' }}" 
                             wire:click="{{ $canSpy ? "selectMissionType('spy')" : '' }}" style="--i: 2;">
                            <div class="mission-type-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Espionnage</h4>
                                <p>Envoyez des sondes pour espionner une planète et obtenir des informations.</p>
                            </div>
                            @if(!$canSpy)
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucun vaisseau d'espionnage disponible (Requis: Scout Quantique)</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Transport -->
                        <div class="mission-type-card {{ $missionType === 'transport' ? 'selected' : '' }} {{ !$canTransport ? 'disabled' : '' }}" 
                             wire:click="{{ $canTransport ? "selectMissionType('transport')" : '' }}" style="--i: 3;">
                            <div class="mission-type-icon">
                                <i class="fas fa-truck-loading"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Transport</h4>
                                <p>Transportez des ressources vers une autre planète.</p>
                            </div>
                            @if(!$canTransport)
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucun vaisseau de transport disponible (Requis: Transporteur Delta)</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Colonisation -->
                        <div class="mission-type-card {{ $missionType === 'colonize' ? 'selected' : '' }} {{ !$canColonize ? 'disabled' : '' }}" 
                             wire:click="{{ $canColonize ? "selectMissionType('colonize')" : '' }}" style="--i: 4;">
                            <div class="mission-type-icon">
                                <i class="fas fa-flag"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Colonisation</h4>
                                <p>Établissez une nouvelle colonie sur une planète inoccupée.</p>
                            </div>
                            @if(!$canColonize)
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucun vaisseau de colonisation disponible (Requis: Vaisseau de Commandement)</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Basement -->
                        <div class="mission-type-card {{ $missionType === 'basement' ? 'selected' : '' }} {{ !$canBasement ? 'disabled' : '' }}" 
                             wire:click="{{ $canBasement ? "selectMissionType('basement')" : '' }}" style="--i: 5;">
                            <div class="mission-type-icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Basement</h4>
                                <p>Transfert instantané d'unités et vaisseaux entre vos planètes.</p>
                            </div>
                            @if(!$canBasement)
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Vous devez avoir plusieurs planètes</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Extraction -->
                        <div class="mission-type-card {{ $missionType === 'extract' ? 'selected' : '' }} {{ !$canExtract ? 'disabled' : '' }}" 
                             wire:click="{{ $canExtract ? "selectMissionType('extract')" : '' }}" style="--i: 6;">
                            <div class="mission-type-icon">
                                <i class="fas fa-pickaxe"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Extraction</h4>
                                <p>Récoltez des ressources sur des planètes non colonisées.</p>
                            </div>
                            @if(!$canExtract)
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucun vaisseau d'extraction disponible (Requis: Transporteur Delta)</span>
                                </div>
                            @endif
                        </div>

                        <!-- Exploration -->
                        <div class="mission-type-card {{ $missionType === 'explore' ? 'selected' : '' }} {{ !$canExplore ? 'disabled' : '' }}" 
                             wire:click="{{ $canExplore ? "selectMissionType('explore')" : '' }}" style="--i: 7;">
                            <div class="mission-type-icon">
                                <i class="fas fa-compass"></i>
                            </div>
                            <div class="mission-type-info">
                                <h4>Exploration</h4>
                                <p>Envoyez des éclaireurs pour découvrir des récompenses.</p>
                            </div>
                            @if(!$canExplore)
                                <div class="mission-type-unavailable">
                                    <i class="fas fa-lock"></i>
                                    <span>Aucun vaisseau d'exploration disponible (Requis: Drone Stratos ou Scout Quantique)</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mission-actions">
                    <button type="button" class="mission-btn mission-btn-primary {{ !$missionType ? 'disabled' : '' }}" 
                            wire:click="continueMission" {{ !$missionType ? 'disabled' : '' }}>
                        <i class="fas fa-paper-plane"></i>
                        Continuer
                    </button>
                </div>
            </div>
        </div>
        @elseif($activeTab === 'bookmarks')
        <div class="mission-content">
            <div class="mission-form">
                <div class="mission-form-group">
                    <label class="mission-form-label">
                    <i class="fas fa-bookmark"></i>
                    Gestion des Bookmarks
                    <span class="mission-counter-badge">{{ $bookmarkCount }} / {{ $bookmarkLimit }}</span>
                    </label>
                    <p class="mission-form-help">Supprimez vos bookmarks. L'ajout se fait via PlanetInfo.</p>
                    <div class="mission-bookmarks-list">
                        @if(empty($bookmarks))
                            <div class="mission-empty-state">
                                <i class="far fa-bookmark"></i>
                                <span>Aucun bookmark pour l'instant</span>
                            </div>
                        @else
                            <div class="mission-table-wrapper">
                                <table class="mission-table">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Coordonnées</th>
                                            <th class="actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bookmarks as $b)
                                            <tr>
                                                <td class="mission-table-label">{{ $b['label'] }}</td>
                                                <td class="mission-table-coords">[{{ $b['galaxy'] }}:{{ $b['system'] }}:{{ $b['position'] }}]</td>
                                                <td class="mission-table-actions">
                                                    <button type="button" class="mission-btn mission-btn-danger"
                                                            wire:click="deleteBookmark({{ $b['id'] }})">
                                                        <i class="fas fa-trash"></i>
                                                        Supprimer
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>