<div>
    <div class="alliance-overview">
        @if($alliance)
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
        @else
            <div class="alliance-empty" style="padding: 16px;">
                <h3>Vous n'êtes pas dans une alliance</h3>
                <p>Recherchez ou créez une alliance pour accéder à la vue d'ensemble.</p>
                <div style="margin-top: 12px; display: flex; gap: 10px;">
                    <a href="{{ route('game.alliance.search') }}" wire:navigate.hover class="btn btn-primary">🔍 Rechercher</a>
                    <a href="{{ route('game.alliance.create') }}" wire:navigate.hover class="btn btn-secondary">➕ Créer</a>
                </div>
            </div>
        @endif
    </div>

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
</div>
