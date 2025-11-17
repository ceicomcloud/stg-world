<div page="alliance">
    <div class="alliance-container">
        <!-- Navigation Tabs -->
        <div class="alliance-tabs">
            @if($alliance)
                <button class="alliance-tab {{ $currentTab === 'overview' ? 'active' : '' }}" 
                        wire:click="switchTab('overview')">
                    📊 Vue d'ensemble
                </button>
                <button class="alliance-tab {{ $currentTab === 'members' ? 'active' : '' }}" 
                        wire:click="switchTab('members')">
                    👥 Membres
                </button>
                <button class="alliance-tab {{ $currentTab === 'bank' ? 'active' : '' }}" 
                        wire:click="switchTab('bank')">
                    🏦 Banque
                </button>
                @if($userAllianceMember && $userAllianceMember->hasPermission('manage_ranks'))
                    <button class="alliance-tab {{ $currentTab === 'ranks' ? 'active' : '' }}" 
                            wire:click="switchTab('ranks')">
                        🎖️ Rangs
                    </button>
                @endif
                @if($userAllianceMember && $userAllianceMember->hasPermission('manage_members'))
                    <button class="alliance-tab {{ $currentTab === 'member-management' ? 'active' : '' }}" 
                            wire:click="switchTab('member-management')">
                        ⚙️ Gestion Membres
                    </button>
                @endif
                @if($userAllianceMember && $userAllianceMember->hasPermission('manage_applications'))
                    <button class="alliance-tab {{ $currentTab === 'applications' ? 'active' : '' }}" 
                            wire:click="switchTab('applications')">
                        📝 Candidatures
                    </button>
                @endif
                <button class="alliance-tab {{ $currentTab === 'wars' ? 'active' : '' }}" 
                        wire:click="switchTab('wars')">
                    ⚔️ Guerres
                </button>
                @if($userAllianceMember && $userAllianceMember->hasPermission('manage_alliance'))
                    <button class="alliance-tab {{ $currentTab === 'technologies' ? 'active' : '' }}" 
                            wire:click="switchTab('technologies')">
                        🔬 Technologies
                    </button>
                @endif
            @else
                <button class="alliance-tab {{ $currentTab === 'search' ? 'active' : '' }}" 
                        wire:click="switchTab('search')">
                    🔍 Rechercher
                </button>
                <button class="alliance-tab {{ $currentTab === 'create' ? 'active' : '' }}" 
                        wire:click="switchTab('create')">
                    ➕ Créer
                </button>
            @endif
        </div>

        <div class="alliance-content">
            @if($alliance)
                <!-- Alliance Overview -->
                @if($currentTab === 'overview')
                    <div class="alliance-overview">
                        <div class="alliance-info-simple">
                            <h3>🛡️ Informations de l'Alliance</h3>
                            
                            @if(!$editMode)
                                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                                    @if($alliance->logo_url)
                                        <img src="{{ $alliance->logo_url }}" alt="Logo" class="alliance-logo">
                                    @endif
                                    <div>
                                        <h2 style="color: var(--stargate-primary); margin: 0;">{{ $alliance->name }} [{{ $alliance->tag }}]</h2>
                                        <p style="color: var(--stargate-text-secondary); margin: 5px 0;">Leader: {{ $alliance->leader->name }}</p>
                                    </div>
                                </div>
                                
                                @if($alliance->external_description)
                                    <div style="margin-bottom: 15px;">
                                        <strong>Description:</strong>
                                        <p style="margin-top: 5px;">{!! $alliance->external_description !!}</p>
                                    </div>
                                @endif
                                
                                @if($userAllianceMember && $userAllianceMember->hasPermission('view_internal_description') && $alliance->internal_description)
                                    <div style="margin-bottom: 15px;">
                                        <strong>Description interne:</strong>
                                        <p style="margin-top: 5px; font-style: italic;">{!! $alliance->internal_description !!}</p>
                                    </div>
                                @endif
                                
                                @if($userAllianceMember && $userAllianceMember->hasPermission('edit_alliance_info'))
                                    <button class="btn btn-secondary" wire:click="toggleEditMode">
                                        ✏️ Modifier
                                    </button>
                                @endif
                            @else
                                <!-- Edit Mode -->
                                <div class="alliance-form">
                                    <div class="form-group">
                                        <label>Nom de l'alliance</label>
                                        <input type="text" class="form-input" wire:model="editName">
                                        @error('editName') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Tag</label>
                                        <input type="text" class="form-input" wire:model="editTag" maxlength="10">
                                        @error('editTag') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Description externe</label>
                                        <x-input.tinymce wire:model.live="editExternalDescription" placeholder="Description visible par tous"></x-input.tinymce>
                                        @error('editExternalDescription') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Description interne</label>
                                        <x-input.tinymce wire:model.live="editInternalDescription" placeholder="Description visible uniquement par les membres"></x-input.tinymce>
                                        @error('editInternalDescription') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Nombre maximum de membres</label>
                                        <input type="number" class="form-input" wire:model="editMaxMembers" min="1" max="100">
                                        @error('editMaxMembers') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Logo de l'alliance</label>
                                        <input type="file" class="form-input" wire:model="logo" accept="image/*">
                                        @error('logo') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="form-checkbox">
                                            <input type="checkbox" wire:model="editOpenRecruitment" id="openRecruitment">
                                            <label for="openRecruitment">Recrutement ouvert</label>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; gap: 10px;">
                                        <button class="btn btn-primary" wire:click="saveAllianceInfo">
                                            💾 Sauvegarder
                                        </button>
                                        <button class="btn btn-secondary" wire:click="toggleEditMode">
                                            ❌ Annuler
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="alliance-stats-simple">
                            <h3>📊 Statistiques</h3>
                            <ul class="stats-list">
                                <li class="stat-line">
                                    <span class="stat-icon">👥</span>
                                    <span class="stat-text">{{ $alliance->member_count }} Membres</span>
                                </li>
                                <li class="stat-line">
                                    <span class="stat-icon">⚡</span>
                                    <span class="stat-text">{{ number_format($alliance->deuterium_bank) }} Deuterium</span>
                                </li>
                                <li class="stat-line">
                                    <span class="stat-icon">🕒</span>
                                    <span class="stat-text">Créée {{ $alliance->created_at->diffForHumans() }}</span>
                                </li>
                            </ul>
                            
                            @if(!Auth::user()->isAllianceLeader())
                                <div style="margin-top: 20px; text-align: center;">
                                    <button class="btn btn-danger" wire:click="confirmLeave">
                                        🚪 Quitter l'alliance
                                    </button>
                                </div>
                            @else
                                <div style="margin-top: 20px; text-align: center;">
                                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                                        <button class="btn btn-warning" wire:click="showTransferLeadershipModal">
                                            👑 Céder l'alliance
                                        </button>
                                        <button class="btn btn-danger" wire:click="confirmDelete">
                                            🗑️ Supprimer l'alliance
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                <!-- Members Tab -->
                @if($currentTab === 'members')
                    <h3>👥 Membres de l'Alliance ({{ $members->total() }})</h3>
                    
                    <div class="members-list">
                        @foreach($members as $member)
                            <div class="member-item">
                                <div class="member-info">
                                    <span class="member-name">{{ $member->user->name }}</span>
                                    @if($member->rank)
                                        <span class="member-rank">{{ $member->rank->name }}</span>
                                    @endif
                                    @if($member->user_id === $alliance->leader_id)
                                        <span style="color: gold;">👑</span>
                                    @endif
                                </div>
                                <div class="member-stats">
                                    <span>Rejoint: {{ $member->joined_at->format('d/m/Y') }}</span>
                                    <span>Contribution: {{ number_format($member->contributed_deuterium) }} D</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    {{ $members->links() }}
                @endif
                
                <!-- Bank Tab -->
                @if($currentTab === 'bank')
                    <h3 class="bank-title">🏦 Banque de l'Alliance</h3>
                    
                    <div class="bank-section">
                        <div class="bank-card">
                            <h4>💰 Solde Actuel</h4>
                            <div class="bank-balance">
                                <span class="bank-balance-value">{{ number_format($alliance->deuterium_bank) }}</span>
                                <span class="bank-balance-label">Deuterium</span>
                            </div>
                            <div class="bank-capacity" style="margin-top: 8px; color: var(--stargate-text-secondary);">
                                <span>Capacité maximale:</span>
                                <span style="color: var(--stargate-accent); font-weight: 600; margin-left: 6px;">{{ number_format($alliance->getMaxDeuteriumStorage()) }}</span>
                                <span style="margin-left: 4px;">Deuterium</span>
                            </div>
                        </div>
                        
                        <div class="bank-card">
                            <h4>📥 Déposer du Deuterium</h4>
                            <div class="bank-actions">
                                <input type="number" class="bank-input" wire:model="bankDepositAmount" 
                                    placeholder="Quantité" min="1">
                                <button class="btn btn-primary" wire:click="depositToDeuteriumBank">
                                    Déposer
                                </button>
                            </div>
                            @error('bankDepositAmount') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
                        </div>
                        
                        @if($userAllianceMember && $userAllianceMember->hasPermission('manage_bank'))
                            <div class="bank-card">
                                <h4>📤 Retirer du Deuterium</h4>
                                <div class="bank-actions">
                                    <input type="number" class="bank-input" wire:model="bankWithdrawAmount" 
                                        placeholder="Quantité" min="1">
                                    <button class="btn btn-danger" wire:click="withdrawFromDeuteriumBank">
                                        Retirer
                                    </button>
                                </div>
                                @error('bankWithdrawAmount') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                @endif
                
                <!-- Wars Tab -->
                @if($currentTab === 'wars')
                    <h3>⚔️ Guerres</h3>
                    
                    @if($wars->count() > 0)
                        <div class="wars-list">
                            @foreach($wars as $war)
                                <div class="war-item">
                                    <div class="war-header">
                                        <div class="war-alliances">
                                            {{ $war->attackerAlliance->name }} ⚔️ {{ $war->defenderAlliance->name }}
                                        </div>
                                        <span class="war-status {{ $war->status }}">
                                            {{ $war->formatted_status }}
                                        </span>
                                    </div>
                                    
                                    @if($war->reason)
                                        <p><strong>Raison:</strong> {{ $war->reason }}</p>
                                    @endif
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                        <small style="color: var(--stargate-text-secondary);">
                                            Déclarée par {{ $war->declaredBy->name }} le {{ $war->created_at->format('d/m/Y H:i') }}
                                        </small>
                                        
                                        @if($war->isActive() && $war->canBeEndedBy(Auth::user()))
                                            <button class="btn btn-sm btn-secondary" 
                                                    wire:click="confirmEndWar({{ $war->id }})">
                                                🏳️ Terminer
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        {{ $wars->links() }}
                    @else
                        <p style="text-align: center; color: var(--stargate-text-secondary); padding: 40px;">
                            Aucune guerre en cours
                        </p>
                    @endif
                @endif
                
                <!-- Technologies Tab -->
                @if($currentTab === 'technologies')
                    <h3>🔬 Technologies d'Alliance</h3>
                    
                    <div class="technologies-section">
                        <div class="alliance-info-card">
                            <p style="color: var(--stargate-text-secondary); margin-bottom: 20px;">
                                Les technologies d'alliance améliorent les capacités de votre alliance. 
                                Chaque amélioration coûte du deuterium de la banque d'alliance.
                            </p>
                            
                            <div class="bank-info" style="margin-bottom: 30px;">
                                <strong>💰 Deuterium en banque: </strong>
                                <span style="color: var(--stargate-accent);">{{ number_format($alliance->deuterium_bank) }}</span>
                            </div>
                        </div>
                        
                        <div class="technologies-grid">
                            <!-- Technology: Members -->
                            @php
                                $membersTech = $technologies['members'] ?? null;
                            @endphp
                            <div class="technology-card">
                                <div class="technology-header">
                                    <div class="technology-icon">👥</div>
                                    <div class="technology-info">
                                        <h4>{{ $membersTech ? $membersTech->getName() : 'Expansion des Membres' }}</h4>
                                        <p>{{ $membersTech ? $membersTech->getDescription() : 'Augmente la capacité maximale de membres de l\'alliance' }}</p>
                                    </div>
                                </div>
                                
                                <div class="technology-stats">
                                    <div class="stat-row">
                                        <span>Niveau actuel:</span>
                                        <span class="stat-value">{{ $membersTech ? $membersTech->level : 0 }}/15</span>
                                    </div>
                                    <div class="stat-row">
                                        <span>Bonus actuel:</span>
                                        <span class="stat-value">+{{ $membersTech ? $membersTech->getBonus() : 0 }} membres</span>
                                    </div>
                                    <div class="stat-row">
                                        <span>Capacité totale:</span>
                                        <span class="stat-value">{{ $alliance->getMaxMembers() }} membres</span>
                                    </div>
                                    @if($membersTech && $membersTech->canUpgrade())
                                        <div class="stat-row">
                                            <span>Prochain niveau:</span>
                                            <span class="stat-value">+{{ $membersTech->getNextLevelBonus() }} membres</span>
                                        </div>
                                        <div class="stat-row">
                                            <span>Coût:</span>
                                            <span class="stat-value cost">{{ number_format($membersTech->getUpgradeCost()) }} deuterium</span>
                                        </div>
                                    @endif
                                </div>
                                
                                @if($membersTech && $membersTech->canUpgrade())
                                    <button class="btn btn-primary" 
                                            wire:click="showTechnologyUpgrade('members')"
                                            {{ $alliance->deuterium_bank < $membersTech->getUpgradeCost() ? 'disabled' : '' }}>
                                        🔬 Améliorer
                                    </button>
                                @else
                                    <button class="btn btn-secondary" disabled>
                                        ✅ Niveau Maximum
                                    </button>
                                @endif
                            </div>
                            
                            <!-- Technology: Bank -->
                            @php
                                $bankTech = $technologies['bank'] ?? null;
                            @endphp
                            <div class="technology-card">
                                <div class="technology-header">
                                    <div class="technology-icon">🏦</div>
                                    <div class="technology-info">
                                        <h4>{{ $bankTech ? $bankTech->getName() : 'Stockage Avancé' }}</h4>
                                        <p>{{ $bankTech ? $bankTech->getDescription() : 'Augmente la capacité de stockage de deuterium de la banque' }}</p>
                                    </div>
                                </div>
                                
                                <div class="technology-stats">
                                    <div class="stat-row">
                                        <span>Niveau actuel:</span>
                                        <span class="stat-value">{{ $bankTech ? $bankTech->level : 0 }}/15</span>
                                    </div>
                                    <div class="stat-row">
                                        <span>Bonus actuel:</span>
                                        <span class="stat-value">+{{ $bankTech ? number_format($bankTech->getBonus()) : 0 }} deuterium</span>
                                    </div>
                                    <div class="stat-row">
                                        <span>Capacité totale:</span>
                                        <span class="stat-value">{{ number_format($alliance->getMaxDeuteriumStorage()) }} deuterium</span>
                                    </div>
                                    @if($bankTech && $bankTech->canUpgrade())
                                        <div class="stat-row">
                                            <span>Prochain niveau:</span>
                                            <span class="stat-value">+{{ number_format($bankTech->getNextLevelBonus()) }} deuterium</span>
                                        </div>
                                        <div class="stat-row">
                                            <span>Coût:</span>
                                            <span class="stat-value cost">{{ number_format($bankTech->getUpgradeCost()) }} deuterium</span>
                                        </div>
                                    @endif
                                </div>
                                
                                @if($bankTech && $bankTech->canUpgrade())
                                    <button class="btn btn-primary" 
                                            wire:click="showTechnologyUpgrade('bank')"
                                            {{ $alliance->deuterium_bank < $bankTech->getUpgradeCost() ? 'disabled' : '' }}>
                                        🔬 Améliorer
                                    </button>
                                @else
                                    <button class="btn btn-secondary" disabled>
                                        ✅ Niveau Maximum
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Ranks Tab -->
                @if($currentTab === 'ranks')
                    <h3>🎖️ Gestion des Rangs</h3>
                    
                    <div class="ranks-section">
                        <!-- Existing Ranks -->
                        <div class="ranks-list">
                            <h4>Rangs existants</h4>
                            @foreach($ranks as $rank)
                                <div class="rank-item">
                                    <div class="rank-info">
                                        <span class="rank-name">{{ $rank->name }}</span>
                                        <span class="rank-level">Niveau {{ $rank->level }}</span>
                                    </div>
                                    <div class="rank-permissions">
                                        @foreach($rank->permissions as $permission)
                                            <span class="permission-badge">{{ $availablePermissions[$permission] ?? $permission }}</span>
                                        @endforeach
                                    </div>
                                    <div class="rank-actions">
                                        <button class="btn btn-sm btn-secondary" wire:click="editRank({{ $rank->id }})">
                                            ✏️ Modifier
                                        </button>
                                        @if($rank->level > 1)
                                            <button class="btn btn-sm btn-danger" wire:click="deleteRank({{ $rank->id }})"
                                                    onclick="return confirm('Supprimer ce rang ?')">
                                                🗑️ Supprimer
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Create/Edit Rank Form -->
                        <div class="rank-form">
                            <h4>{{ $editingRank ? 'Modifier le rang' : 'Créer un nouveau rang' }}</h4>
                            
                            <div class="form-group">
                                <label>Nom du rang</label>
                                <input type="text" class="form-input" wire:model="newRankName" 
                                    placeholder="Nom du rang">
                                @error('newRankName') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Niveau (1-10)</label>
                                <input type="number" class="form-input" wire:model="newRankLevel" 
                                    min="1" max="10">
                                @error('newRankLevel') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Permissions</label>
                                <div class="permissions-grid">
                                    @foreach($availablePermissions as $key => $label)
                                        <label class="permission-checkbox">
                                            <input type="checkbox" wire:model="newRankPermissions" value="{{ $key }}">
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button class="btn btn-primary" wire:click="{{ $editingRank ? 'updateRank' : 'createRank' }}">
                                    {{ $editingRank ? '💾 Mettre à jour' : '➕ Créer le rang' }}
                                </button>
                                @if($editingRank)
                                    <button class="btn btn-secondary" wire:click="cancelEditRank">
                                        ❌ Annuler
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Member Management Tab -->
                @if($currentTab === 'member-management')
                    <h3>👥 Gestion des Membres</h3>
                    
                    <div class="member-management-section">
                        @foreach($members as $member)
                            <div class="member-management-item">
                                <div class="member-basic-info">
                                    <div class="member-avatar">
                                        <span class="member-initial">{{ substr($member->user->name, 0, 1) }}</span>
                                    </div>
                                    <div class="member-details">
                                        <span class="member-name">{{ $member->user->name }}</span>
                                        <span class="member-current-rank">
                                            @if($member->rank)
                                                {{ $member->rank->name }}
                                            @else
                                                Aucun rang
                                            @endif
                                        </span>
                                        @if($member->user_id === $alliance->leader_id)
                                            <span class="leader-badge">👑 Leader</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="member-stats-detailed">
                                    <div class="stat-item">
                                        <span class="stat-label">Rejoint:</span>
                                        <span class="stat-value">{{ $member->joined_at->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Contribution:</span>
                                        <span class="stat-value">{{ number_format($member->contributed_deuterium) }} D</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Dernière activité:</span>
                                        <span class="stat-value">{{ $member->user->last_activity ? $member->user->last_activity->diffForHumans() : 'Inconnue' }}</span>
                                    </div>
                                </div>
                                
                                @if($member->user_id !== $alliance->leader_id && $userAllianceMember && $userAllianceMember->hasPermission('manage_members'))
                                    <div class="member-actions">
                                        <div class="rank-assignment">
                                            <select class="form-select" wire:change="assignRank({{ $member->id }}, $event.target.value)">
                                                <option value="">Aucun rang</option>
                                                @foreach($ranks->where('level', '<', $userAllianceMember->rank->level ?? 999) as $rank)
                                                    <option value="{{ $rank->id }}" {{ $member->rank_id == $rank->id ? 'selected' : '' }}>
                                                        {{ $rank->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <button class="btn btn-sm btn-danger" 
                                                wire:click="confirmKick({{ $member->id }})">
                                            🚫 Exclure
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        
                        {{ $members->links() }}
                    </div>
                @endif
                
                <!-- Applications Tab -->
                @if($currentTab === 'applications')
                    <h3>📝 Candidatures</h3>
                    
                    <div class="applications-section">
                        @if(isset($pendingApplications) && $pendingApplications->count() > 0)
                            @foreach($pendingApplications as $application)
                                <div class="application-item">
                                    <div class="application-header">
                                        <div class="applicant-info">
                                            <div class="applicant-avatar">
                                                <span class="applicant-initial">{{ substr($application->user->name, 0, 1) }}</span>
                                            </div>
                                            <div class="applicant-details">
                                                <h4 class="applicant-name">{{ $application->user->name }}</h4>
                                                <span class="application-date">Candidature du {{ $application->created_at->format('d/m/Y à H:i') }}</span>
                                            </div>
                                        </div>
                                        <div class="application-status">
                                            <span class="status-badge status-pending">En attente</span>
                                        </div>
                                    </div>
                                    
                                    @if($application->message)
                                        <div class="application-message">
                                            <h5>💬 Message de candidature:</h5>
                                            <p>{{ $application->message }}</p>
                                        </div>
                                    @endif
                                                                        
                                    <div class="application-actions">
                                        <button class="btn btn-success" 
                                                wire:click="confirmAcceptApplication({{ $application->id }})">
                                            ✅ Accepter
                                        </button>
                                        <button class="btn btn-danger" 
                                                wire:click="confirmRejectApplication({{ $application->id }})">
                                            ❌ Rejeter
                                        </button>
                                        <button class="btn btn-secondary" 
                                                wire:click="viewUserProfile({{ $application->user->id }})">
                                            👤 Voir le profil
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                            
                            <!-- Pagination -->
                            <div class="applications-pagination">
                                {{ $pendingApplications->links() }}
                            </div>
                        @else
                            <div class="no-applications">
                                <div class="empty-state">
                                    <div class="empty-icon">📭</div>
                                    <h4>Aucune candidature en attente</h4>
                                    <p>Il n'y a actuellement aucune candidature à examiner.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Modales de confirmation réutilisables -->
                <x-input.modal-confirmation
                    wire:model="showLeaveModal"
                    wire:key="alliance-modal-leave"
                    title="Quitter l'alliance"
                    message="Êtes-vous sûr de vouloir quitter l'alliance ?"
                    icon="fas fa-question-circle text-warning"
                    confirmText="Oui, quitter"
                    cancelText="Rester dans l'alliance"
                    onConfirm="performLeave"
                    onCancel="dismissModals"
                />

                <x-input.modal-confirmation
                    wire:model="showDeleteModal"
                    wire:key="alliance-modal-delete"
                    title="Supprimer l'alliance"
                    message="Êtes-vous sûr de vouloir supprimer définitivement l'alliance ? Cette action est irréversible."
                    icon="fas fa-exclamation-triangle text-danger"
                    confirmText="Oui, supprimer"
                    cancelText="Annuler"
                    onConfirm="performDelete"
                    onCancel="dismissModals"
                />

                <x-input.modal-confirmation
                    wire:model="showEndWarModal"
                    wire:key="alliance-modal-endwar"
                    title="Terminer la guerre"
                    message="Terminer cette guerre ?"
                    icon="fas fa-flag text-warning"
                    confirmText="Oui, terminer"
                    cancelText="Continuer la guerre"
                    onConfirm="performEndWar"
                    onCancel="dismissModals"
                />

                <x-input.modal-confirmation
                    wire:model="showKickModal"
                    wire:key="alliance-modal-kick"
                    title="Exclure le membre"
                    message="Êtes-vous sûr de vouloir exclure ce membre de l'alliance ?"
                    icon="fas fa-user-slash text-danger"
                    confirmText="Oui, exclure"
                    cancelText="Annuler"
                    onConfirm="performKick"
                    onCancel="dismissModals"
                />

                <x-input.modal-confirmation
                    wire:model="showAcceptAppModal"
                    wire:key="alliance-modal-accept-app"
                    title="Accepter la candidature"
                    message="Accepter cette candidature ?"
                    icon="fas fa-check-circle text-success"
                    confirmText="Accepter"
                    cancelText="Annuler"
                    onConfirm="performAcceptApplication"
                    onCancel="dismissModals"
                />

                <x-input.modal-confirmation
                    wire:model="showRejectAppModal"
                    wire:key="alliance-modal-reject-app"
                    title="Rejeter la candidature"
                    message="Rejeter cette candidature ?"
                    icon="fas fa-times-circle text-danger"
                    confirmText="Rejeter"
                    cancelText="Annuler"
                    onConfirm="performRejectApplication"
                    onCancel="dismissModals"
                />

            @else
                <!-- Search Alliances -->
                @if($currentTab === 'search')
                    <h3>🔍 Rechercher une Alliance</h3>
                    
                    <div class="alliance-search">
                        <input type="text" class="search-input" wire:model.live="searchQuery" 
                            placeholder="Rechercher par nom ou tag...">
                    </div>
                    
                    @if(count($searchResults) > 0)
                        <div class="search-results">
                            @foreach($searchResults as $result)
                                <div class="alliance-result">
                                    <div class="alliance-result-info">
                                        <h4>{{ $result->name }} [{{ $result->tag }}]</h4>
                                        <p>Leader: {{ $result->leader->name }}</p>
                                        @if($result->external_description)
                                            <p>{!! Str::limit($result->external_description, 100) !!}</p>
                                        @endif
                                    </div>
                                    
                                    <div class="alliance-result-stats">
                                        <div class="result-stat">
                                            <span class="result-stat-value">{{ $result->member_count }}</span>
                                            <span class="result-stat-label">Membres</span>
                                        </div>
                                        <div class="result-stat">
                                            <span class="result-stat-value">{{ $result->open_recruitment ? 'Ouvert' : 'Fermé' }}</span>
                                            <span class="result-stat-label">Recrutement</span>
                                        </div>
                                        
                                        @if($result->open_recruitment && $result->canAcceptNewMembers())
                                            <button class="btn btn-primary" 
                                                    wire:click="applyToAlliance({{ $result->id }})">
                                                📝 Candidater
                                            </button>
                                        @else
                                            <span style="color: var(--stargate-text-secondary); font-size: 12px;">
                                                {{ $result->canAcceptNewMembers() ? 'Recrutement fermé' : 'Alliance complète' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif(!empty($searchQuery))
                        <p style="text-align: center; color: var(--stargate-text-secondary); padding: 40px;">
                            Aucune alliance trouvée
                        </p>
                    @endif
                @endif
                
                <!-- Create Alliance -->
                @if($currentTab === 'create')
                    <h3>➕ Créer une Alliance</h3>
                    
                    <div class="alliance-form">
                        <div class="form-group">
                            <label>Nom de l'alliance</label>
                            <input type="text" class="form-input" wire:model="createAllianceName" 
                                placeholder="Nom de votre alliance">
                            @error('createAllianceName') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Tag (3-10 caractères)</label>
                            <input type="text" class="form-input" wire:model="createAllianceTag" 
                                placeholder="TAG" maxlength="10">
                            @error('createAllianceTag') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Description (optionnelle)</label>
                            <textarea class="form-input form-textarea" wire:model="createAllianceDescription" 
                                    placeholder="Décrivez votre alliance..."></textarea>
                            @error('createAllianceDescription') <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span> @enderror
                        </div>
                        
                        <button class="btn btn-primary btn-lg" wire:click="createAlliance">
                            🛡️ Créer l'Alliance
                        </button>
                    </div>
                @endif
            @endif
        </div>

        <!-- Modal de transfert de leadership -->
        @if($showTransferModal)
            <div class="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 1000; display: flex; align-items: center; justify-content: center;">
                <div class="modal-content" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-radius: 15px; padding: 30px; max-width: 500px; width: 90%; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <div class="modal-header" style="text-align: center; margin-bottom: 25px;">
                        <h3 style="color: #fff; margin: 0; font-size: 1.5rem; font-weight: 600;">
                            👑 Transfert de Leadership
                        </h3>
                        <p style="color: #b0b0b0; margin: 10px 0 0 0; font-size: 0.9rem;">
                            Sélectionnez le nouveau leader de l'alliance
                        </p>
                    </div>

                    <div class="modal-body">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; color: #fff; margin-bottom: 10px; font-weight: 500;">
                                Nouveau Leader :
                            </label>
                            <select wire:model="selectedNewLeaderId" 
                                    style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2); background: rgba(255, 255, 255, 0.1); color: #fff; font-size: 1rem;">
                                <option value="">-- Sélectionner un membre --</option>
                                @if($alliance && $alliance->members)
                                    @foreach($alliance->members as $member)
                                        @if($member->user_id !== auth()->id())
                                            <option value="{{ $member->user_id }}" style="background: #2a2a3e; color: #fff;">
                                                {{ $member->user->name }} 
                                                @if($member->rank)
                                                    ({{ $member->rank->name }})
                                                @endif
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            @error('selectedNewLeaderId')
                                <span style="color: #ff6b6b; font-size: 0.8rem; margin-top: 5px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div style="background: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                <span style="color: #ffc107; font-size: 1.2rem;">⚠️</span>
                                <strong style="color: #ffc107;">Attention</strong>
                            </div>
                            <p style="color: #fff; margin: 0; font-size: 0.9rem; line-height: 1.4;">
                                En transférant le leadership, vous perdrez tous vos privilèges de leader et serez rétrogradé au rang de membre normal. Cette action est irréversible.
                            </p>
                        </div>
                    </div>

                    <div class="modal-footer" style="display: flex; gap: 15px; justify-content: flex-end;">
                        <button wire:click="closeTransferModal" 
                                style="padding: 10px 20px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.3); background: transparent; color: #fff; cursor: pointer; transition: all 0.3s ease;">
                            Annuler
                        </button>
                        <button wire:click="transferLeadership" 
                                style="padding: 10px 20px; border-radius: 8px; border: none; background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%); color: #000; font-weight: 600; cursor: pointer; transition: all 0.3s ease;"
                                onclick="return confirm('Êtes-vous absolument sûr de vouloir transférer le leadership ? Cette action est irréversible.')">
                            👑 Transférer le Leadership
                        </button>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Technology Upgrade Modal -->
        @if($showUpgradeModal && $selectedTechnology)
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center; z-index: 1000; animation: fadeIn 0.3s ease;">
                <div style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.95) 100%); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 30px; max-width: 500px; width: 90%; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5); animation: slideIn 0.3s ease;">
                    <h3 style="color: #fff; margin-bottom: 20px; text-align: center;">
                        🔬 Améliorer {{ $selectedTechnology->getName() }}
                    </h3>
                    
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="color: var(--stargate-text-secondary);">Niveau actuel:</span>
                            <span style="color: #fff; font-weight: 600;">{{ $selectedTechnology->level }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="color: var(--stargate-text-secondary);">Niveau suivant:</span>
                            <span style="color: var(--stargate-accent); font-weight: 600;">{{ $selectedTechnology->level + 1 }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="color: var(--stargate-text-secondary);">Bonus actuel:</span>
                            <span style="color: #fff;">{{ $selectedTechnology->technology_type === 'members' ? '+' . $selectedTechnology->getBonus() . ' membres' : '+' . number_format($selectedTechnology->getBonus()) . ' deuterium' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <span style="color: var(--stargate-text-secondary);">Nouveau bonus:</span>
                            <span style="color: var(--stargate-accent); font-weight: 600;">{{ $selectedTechnology->technology_type === 'members' ? '+' . $selectedTechnology->getNextLevelBonus() . ' membres' : '+' . number_format($selectedTechnology->getNextLevelBonus()) . ' deuterium' }}</span>
                        </div>
                        <hr style="border: none; border-top: 1px solid rgba(255, 255, 255, 0.1); margin: 15px 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--stargate-text-secondary);">Coût d'amélioration:</span>
                            <span style="color: #ffc107; font-weight: 700; font-size: 18px;">{{ number_format($selectedTechnology->getUpgradeCost()) }} deuterium</span>
                        </div>
                    </div>
                    
                    @if($alliance->deuterium_bank < $selectedTechnology->getUpgradeCost())
                        <div style="background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                            <p style="color: #dc3545; margin: 0; text-align: center;">
                                ⚠️ Deuterium insuffisant en banque
                            </p>
                        </div>
                    @else
                        <div style="background: rgba(40, 167, 69, 0.1); border: 1px solid rgba(40, 167, 69, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                            <p style="color: #28a745; margin: 0; text-align: center;">
                                ✅ Amélioration disponible
                            </p>
                        </div>
                    @endif
                    
                    <div style="display: flex; gap: 15px; justify-content: center;">
                        <button wire:click="closeUpgradeModal" 
                                style="padding: 12px 24px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.3); background: transparent; color: #fff; cursor: pointer; transition: all 0.3s ease; font-weight: 500;">
                            Annuler
                        </button>
                        <button wire:click="upgradeTechnology" 
                                {{ $alliance->deuterium_bank < $selectedTechnology->getUpgradeCost() ? 'disabled' : '' }}
                                style="padding: 12px 24px; border-radius: 8px; border: none; background: {{ $alliance->deuterium_bank < $selectedTechnology->getUpgradeCost() ? 'rgba(108, 117, 125, 0.5)' : 'linear-gradient(135deg, #007bff 0%, #0056b3 100%)' }}; color: #fff; font-weight: 600; cursor: {{ $alliance->deuterium_bank < $selectedTechnology->getUpgradeCost() ? 'not-allowed' : 'pointer' }}; transition: all 0.3s ease;">
                            🔬 Améliorer
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>