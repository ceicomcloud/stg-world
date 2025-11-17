<div>
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
</div>
