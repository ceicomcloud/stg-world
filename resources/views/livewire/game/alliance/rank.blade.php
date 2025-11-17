<div>
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
</div>
