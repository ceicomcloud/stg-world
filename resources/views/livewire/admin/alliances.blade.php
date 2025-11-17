<div class="admin-alliances">
    <div class="admin-page-header">
        <h1>Gestion des alliances</h1>
        <div class="admin-page-actions">
            <button class="admin-tab-button {{ $activeTab === 'list' ? 'active' : '' }}" wire:click="setActiveTab('list')">
                <i class="fas fa-users"></i> Liste des alliances
            </button>
            @if($activeTab === 'detail')
                <button class="admin-tab-button active">
                    <i class="fas fa-user-shield"></i> {{ $selectedAlliance->name }}
                </button>
            @endif
        </div>
    </div>

    <div class="admin-content-body">
        <!-- Liste des alliances -->
        @if($activeTab === 'list')
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Liste des alliances</h2>
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
                                    <th wire:click="sortBy('tag')" class="admin-sortable">
                                        Tag
                                        @if($sortField === 'tag')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th>Leader</th>
                                    <th wire:click="sortBy('max_members')" class="admin-sortable">
                                        Membres
                                        @if($sortField === 'max_members')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('deuterium_bank')" class="admin-sortable">
                                        Banque
                                        @if($sortField === 'deuterium_bank')
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
                                @forelse($alliances as $alliance)
                                    <tr>
                                        <td>{{ $alliance->id }}</td>
                                        <td>{{ $alliance->name }}</td>
                                        <td>{{ $alliance->tag }}</td>
                                        <td>{{ $alliance->leader->name ?? 'Aucun' }}</td>
                                        <td>{{ $alliance->member_count }} / {{ $alliance->max_members }}</td>
                                        <td>{{ number_format($alliance->deuterium_bank) }}</td>
                                        <td>{{ $alliance->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="admin-actions">
                                                <button wire:click="selectAlliance({{ $alliance->id }})" class="admin-action-button admin-action-info" title="Voir les détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="admin-table-empty">Aucune alliance trouvée</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="admin-pagination">
                        {{ $alliances->links() }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Détails de l'alliance -->
        @if($activeTab === 'detail' && $selectedAlliance)
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Alliance: {{ $selectedAlliance->name }} [{{ $selectedAlliance->tag }}]</h2>
                    <div class="admin-card-tabs">
                        <button class="admin-tab-button {{ $allianceDetailTab === 'info' ? 'active' : '' }}" wire:click="setAllianceDetailTab('info')">
                            <i class="fas fa-info-circle"></i> Informations
                        </button>
                        <button class="admin-tab-button {{ $allianceDetailTab === 'members' ? 'active' : '' }}" wire:click="setAllianceDetailTab('members')">
                            <i class="fas fa-users"></i> Membres
                        </button>
                        <button class="admin-tab-button {{ $allianceDetailTab === 'ranks' ? 'active' : '' }}" wire:click="setAllianceDetailTab('ranks')">
                            <i class="fas fa-user-tag"></i> Rangs
                        </button>
                        <button class="admin-tab-button {{ $allianceDetailTab === 'technologies' ? 'active' : '' }}" wire:click="setAllianceDetailTab('technologies')">
                            <i class="fas fa-flask"></i> Technologies
                        </button>
                        <button class="admin-tab-button {{ $allianceDetailTab === 'bank' ? 'active' : '' }}" wire:click="setAllianceDetailTab('bank')">
                            <i class="fas fa-university"></i> Banque
                        </button>
                        <button class="admin-tab-button {{ $allianceDetailTab === 'applications' ? 'active' : '' }}" wire:click="setAllianceDetailTab('applications')">
                            <i class="fas fa-clipboard-list"></i> Candidatures
                        </button>
                        <button class="admin-tab-button {{ $allianceDetailTab === 'wars' ? 'active' : '' }}" wire:click="setAllianceDetailTab('wars')">
                            <i class="fas fa-fighter-jet"></i> Guerres
                        </button>
                    </div>
                </div>
                <div class="admin-card-body">
                    <!-- Informations générales -->
                    @if($allianceDetailTab === 'info')
                        <div class="admin-profile-header">
                            <div class="admin-profile-avatar">
                                @if($selectedAlliance->logo_url)
                                    <img src="{{ $selectedAlliance->logo_url }}" alt="{{ $selectedAlliance->name }}" class="admin-alliance-logo">
                                @else
                                    <i class="fas fa-user-shield"></i>
                                @endif
                            </div>
                            <div class="admin-profile-info">
                                <h3>{{ $selectedAlliance->name }} [{{ $selectedAlliance->tag }}]</h3>
                                <p>Leader: {{ $selectedAlliance->leader->name ?? 'Aucun' }}</p>
                                <div class="admin-profile-badges">
                                    <span class="admin-badge admin-badge-primary">{{ $selectedAlliance->member_count }} / {{ $selectedAlliance->max_members }} membres</span>
                                    @if($selectedAlliance->open_recruitment)
                                        <span class="admin-badge admin-badge-success">Recrutement ouvert</span>
                                    @else
                                        <span class="admin-badge admin-badge-danger">Recrutement fermé</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="admin-profile-details">
                            <div class="admin-profile-section">
                                <h4>Informations générales</h4>
                                <div class="admin-profile-grid">
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">ID</span>
                                        <span class="admin-profile-value">{{ $selectedAlliance->id }}</span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Nom</span>
                                        <span class="admin-profile-value">{{ $selectedAlliance->name }}</span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Tag</span>
                                        <span class="admin-profile-value">{{ $selectedAlliance->tag }}</span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Date de création</span>
                                        <span class="admin-profile-value">{{ $selectedAlliance->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Membres</span>
                                        <span class="admin-profile-value">{{ $selectedAlliance->member_count }} / {{ $selectedAlliance->max_members }}</span>
                                    </div>
                                    <div class="admin-profile-item">
                                        <span class="admin-profile-label">Banque de deuterium</span>
                                        <span class="admin-profile-value">{{ number_format($selectedAlliance->deuterium_bank) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="admin-profile-section">
                                <h4>Description externe</h4>
                                <div class="admin-profile-detail">
                                    <div class="admin-profile-text">
                                        {!! nl2br(e($selectedAlliance->external_description)) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="admin-profile-section">
                                <h4>Description interne</h4>
                                <div class="admin-profile-detail">
                                    <div class="admin-profile-text">
                                        {!! nl2br(e($selectedAlliance->internal_description)) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Membres -->
                    @if($allianceDetailTab === 'members')
                        <div class="admin-table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Rang</th>
                                        <th>Date d'adhésion</th>
                                        <th>Contribution (deuterium)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allianceMembers as $member)
                                        <tr>
                                            <td>{{ $member->user->id }}</td>
                                            <td>{{ $member->user->name }}</td>
                                            <td>
                                                @if($member->rank)
                                                    <span class="admin-badge admin-badge-primary">{{ $member->rank->name }}</span>
                                                @else
                                                    <span class="admin-badge admin-badge-secondary">Membre</span>
                                                @endif
                                                @if($member->isLeader())
                                                    <span class="admin-badge admin-badge-success">Leader</span>
                                                @endif
                                            </td>
                                            <td>{{ $member->joined_at->format('d/m/Y') }}</td>
                                            <td>{{ number_format($member->contributed_deuterium) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="admin-table-empty">Aucun membre trouvé</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Rangs -->
                    @if($allianceDetailTab === 'ranks')
                        <div class="admin-table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Niveau</th>
                                        <th>Permissions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allianceRanks as $rank)
                                        <tr>
                                            <td>{{ $rank->id }}</td>
                                            <td>{{ $rank->name }}</td>
                                            <td>
                                                <span class="admin-badge admin-badge-primary">{{ $rank->level_name }}</span>
                                            </td>
                                            <td>
                                                <div class="admin-badges-container">
                                                    @foreach($rank->getFormattedPermissions() as $permission => $label)
                                                        <span class="admin-badge admin-badge-info">{{ $label }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="admin-table-empty">Aucun rang trouvé</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Technologies -->
                    @if($allianceDetailTab === 'technologies')
                        <div class="admin-form-grid admin-form-grid-2">
                            @forelse($allianceTechnologies as $technology)
                                <div class="admin-card">
                                    <div class="admin-card-header">
                                        <h3>{{ $technology->getName() }}</h3>
                                    </div>
                                    <div class="admin-card-body">
                                        <div class="admin-tech-info">
                                            <p>{{ $technology->getDescription() }}</p>
                                            <div class="admin-tech-level">
                                                <span class="admin-tech-level-label">Niveau:</span>
                                                <span class="admin-tech-level-value">{{ $technology->level }} / {{ $technology->max_level }}</span>
                                            </div>
                                            <div class="admin-tech-bonus">
                                                <span class="admin-tech-bonus-label">Bonus actuel:</span>
                                                <span class="admin-tech-bonus-value">
                                                    @if($technology->technology_type === \App\Models\Alliance\AllianceTechnology::TYPE_MEMBERS)
                                                        +{{ $technology->getBonus() }} membres
                                                    @elseif($technology->technology_type === \App\Models\Alliance\AllianceTechnology::TYPE_BANK)
                                                        +{{ number_format($technology->getBonus()) }} deuterium
                                                    @else
                                                        {{ $technology->getBonus() }}
                                                    @endif
                                                </span>
                                            </div>
                                            @if($technology->canUpgrade())
                                                <div class="admin-tech-upgrade">
                                                    <div class="admin-tech-cost">
                                                        <span class="admin-tech-cost-label">Coût d'amélioration:</span>
                                                        <span class="admin-tech-cost-value">{{ number_format($technology->getUpgradeCost()) }} deuterium</span>
                                                    </div>
                                                    <div class="admin-tech-next-bonus">
                                                        <span class="admin-tech-next-bonus-label">Bonus au niveau suivant:</span>
                                                        <span class="admin-tech-next-bonus-value">
                                                            @if($technology->technology_type === \App\Models\Alliance\AllianceTechnology::TYPE_MEMBERS)
                                                                +{{ $technology->getNextLevelBonus() }} membres
                                                            @elseif($technology->technology_type === \App\Models\Alliance\AllianceTechnology::TYPE_BANK)
                                                                +{{ number_format($technology->getNextLevelBonus()) }} deuterium
                                                            @else
                                                                {{ $technology->getNextLevelBonus() }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <button wire:click="upgradeTechnology({{ $technology->id }})" class="admin-button admin-button-primary admin-button-sm">
                                                        <i class="fas fa-arrow-up"></i> Améliorer
                                                    </button>
                                                </div>
                                            @else
                                                <div class="admin-tech-max-level">
                                                    <span class="admin-badge admin-badge-success">Niveau maximum atteint</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="admin-alert admin-alert-info">
                                    <i class="fas fa-info-circle"></i> Aucune technologie trouvée
                                </div>
                            @endforelse
                        </div>
                    @endif

                    <!-- Banque -->
                    @if($allianceDetailTab === 'bank')
                        <div class="admin-form-grid admin-form-grid-2">
                            <div class="admin-card">
                                <div class="admin-card-header">
                                    <h3>État de la banque</h3>
                                </div>
                                <div class="admin-card-body">
                                    <div class="admin-bank-info">
                                        <div class="admin-bank-balance">
                                            <span class="admin-bank-balance-label">Solde actuel:</span>
                                            <span class="admin-bank-balance-value">{{ number_format($selectedAlliance->deuterium_bank) }} deuterium</span>
                                        </div>
                                        <div class="admin-bank-capacity">
                                            <span class="admin-bank-capacity-label">Capacité maximale:</span>
                                            <span class="admin-bank-capacity-value">{{ number_format($selectedAlliance->getMaxDeuteriumStorage()) }} deuterium</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="admin-card">
                                <div class="admin-card-header">
                                    <h3>Opérations bancaires</h3>
                                </div>
                                <div class="admin-card-body">
                                    <form wire:submit.prevent="manageBankOperation" class="admin-form">
                                        <div class="admin-form-group">
                                            <label for="bankOperation">Opération</label>
                                            <select id="bankOperation" wire:model="bankForm.operation" class="admin-select">
                                                <option value="add">Ajouter du deuterium</option>
                                                <option value="withdraw">Retirer du deuterium</option>
                                            </select>
                                        </div>
                                        
                                        <div class="admin-form-group">
                                            <label for="bankAmount">Montant</label>
                                            <input type="number" id="bankAmount" wire:model="bankForm.amount" class="admin-input" min="1">
                                            @error('bankForm.amount') <span class="admin-error">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div class="admin-form-actions">
                                            <button type="submit" class="admin-button admin-button-primary">
                                                <i class="fas fa-check"></i> Exécuter l'opération
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Candidatures -->
                    @if($allianceDetailTab === 'applications')
                        <div class="admin-table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Utilisateur</th>
                                        <th>Message</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Examiné par</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allianceApplications as $application)
                                        <tr>
                                            <td>{{ $application->id }}</td>
                                            <td>{{ $application->user->name }}</td>
                                            <td>
                                                <div class="admin-truncated-text">
                                                    {{ $application->message }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($application->isPending())
                                                    <span class="admin-badge admin-badge-warning">En attente</span>
                                                @elseif($application->isAccepted())
                                                    <span class="admin-badge admin-badge-success">Acceptée</span>
                                                @elseif($application->isRejected())
                                                    <span class="admin-badge admin-badge-danger">Rejetée</span>
                                                @endif
                                            </td>
                                            <td>{{ $application->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($application->reviewer)
                                                    {{ $application->reviewer->name }}
                                                    <br>
                                                    <small>{{ $application->reviewed_at->format('d/m/Y H:i') }}</small>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="admin-table-empty">Aucune candidature trouvée</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Guerres -->
                    @if($allianceDetailTab === 'wars')
                        <div class="admin-table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Alliance adverse</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Raison</th>
                                        <th>Déclarée par</th>
                                        <th>Date de début</th>
                                        <th>Date de fin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allianceWars as $war)
                                        <tr>
                                            <td>{{ $war->id }}</td>
                                            <td>
                                                @if($war->attacker_alliance_id === $selectedAllianceId)
                                                    {{ $war->defenderAlliance->name }} [{{ $war->defenderAlliance->tag }}]
                                                @else
                                                    {{ $war->attackerAlliance->name }} [{{ $war->attackerAlliance->tag }}]
                                                @endif
                                            </td>
                                            <td>
                                                @if($war->attacker_alliance_id === $selectedAllianceId)
                                                    <span class="admin-badge admin-badge-danger">Attaquant</span>
                                                @else
                                                    <span class="admin-badge admin-badge-warning">Défenseur</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($war->isDeclared())
                                                    <span class="admin-badge admin-badge-warning">Déclarée</span>
                                                @elseif($war->isActive())
                                                    <span class="admin-badge admin-badge-danger">Active</span>
                                                @elseif($war->isEnded())
                                                    <span class="admin-badge admin-badge-success">Terminée</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="admin-truncated-text">
                                                    {{ $war->reason }}
                                                </div>
                                            </td>
                                            <td>{{ $war->declaredBy->name }}</td>
                                            <td>{{ $war->started_at ? $war->started_at->format('d/m/Y H:i') : '-' }}</td>
                                            <td>{{ $war->ended_at ? $war->ended_at->format('d/m/Y H:i') : '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="admin-table-empty">Aucune guerre trouvée</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>