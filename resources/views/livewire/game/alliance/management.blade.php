<div>
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
</div>
