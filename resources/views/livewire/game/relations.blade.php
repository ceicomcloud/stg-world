<div page="relations">
    <div class="empire-container">
        <div class="empire-header">
            <h1 class="empire-title"><i class="fas fa-handshake"></i> Relations (Pactes)</h1>
            <p class="empire-subtitle">Gérez vos demandes et vos pactes actifs</p>
        </div>

        <div class="empire-planets">
            <div class="planet-card">
                <div class="planet-header">
                    <div class="planet-name">Demandes reçues</div>
                </div>
                <div class="planet-section">
                    @forelse($incoming as $r)
                        @php
                            $other = $r->requester;
                            $isOnline = optional($other->last_activity)?->gt(\Carbon\Carbon::now()->subMinutes(5));
                        @endphp
                        <div class="grid-item">
                            <span class="item-name" wire:click="openUserProfile({{ $other->id }})" style="cursor: pointer;">
                                {{ $other->name }}
                                @if($isOnline)
                                    <span class="relation-badge ally" title="En ligne" style="margin-left:6px;">
                                        ● En ligne
                                    </span>
                                @endif
                            </span>
                            <span class="item-meta">
                                <button class="vip-btn" wire:click="accept({{ $r->id }})">Accepter</button>
                                <button class="vip-btn" style="background:#ef4444" wire:click="reject({{ $r->id }})">Refuser</button>
                            </span>
                        </div>
                    @empty
                        <div class="empty-note">Aucune demande en attente</div>
                    @endforelse
                </div>
            </div>

            <div class="planet-card">
                <div class="planet-header">
                    <div class="planet-name">Demandes envoyées</div>
                </div>
                <div class="planet-section">
                    @forelse($outgoing as $r)
                        @php
                            $other = $r->receiver;
                            $isOnline = optional($other->last_activity)?->gt(\Carbon\Carbon::now()->subMinutes(5));
                        @endphp
                        <div class="grid-item">
                            <span class="item-name" wire:click="openUserProfile({{ $other->id }})" style="cursor: pointer;">
                                {{ $other->name }}
                                @if($isOnline)
                                    <span class="relation-badge ally" title="En ligne" style="margin-left:6px;">
                                        ● En ligne
                                    </span>
                                @endif
                            </span>
                            <span class="item-meta">
                                <button class="vip-btn" style="background:#ef4444" wire:click="confirmCancel({{ $r->id }})">Annuler</button>
                            </span>
                        </div>
                    @empty
                        <div class="empty-note">Aucune demande envoyée</div>
                    @endforelse
                </div>
            </div>

            <div class="planet-card">
                <div class="planet-header">
                    <div class="planet-name">Pactes actifs</div>
                </div>
                <div class="planet-section">
                    @forelse($accepted as $r)
                        @php
                            $other = $r->requester_id === auth()->id() ? $r->receiver : $r->requester;
                            $isOnline = optional($other->last_activity)?->gt(\Carbon\Carbon::now()->subMinutes(5));
                        @endphp
                        <div class="grid-item">
                            <span class="item-name" wire:click="openUserProfile({{ $other->id }})" style="cursor: pointer;">
                                {{ $other->name }}
                                @if($isOnline)
                                    <span class="relation-badge ally" title="En ligne" style="margin-left:6px;">
                                        ● En ligne
                                    </span>
                                @endif
                            </span>
                            <span class="item-meta">
                                Depuis {{ optional($r->accepted_at)->format('d/m/Y') }}
                                <button class="vip-btn" style="margin-left:8px;background:#ef4444" wire:click="confirmBreak({{ $r->id }})">Annuler le pacte</button>
                            </span>
                        </div>
                    @empty
                        <div class="empty-note">Aucun pacte actif</div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <x-input.modal-confirmation
            wire:model="showCancelModal"
            title="Confirmer l'annulation"
            message="Êtes-vous sûr de vouloir annuler cette demande de pacte ?"
            icon="fas fa-question-circle text-warning"
            confirmText="Confirmer l'annulation"
            cancelText="Annuler"
            onConfirm="performCancel"
            onCancel="dismissModals"
        />

        <x-input.modal-confirmation
            wire:model="showBreakModal"
            title="Annuler le pacte"
            message="Êtes-vous sûr de vouloir annuler ce pacte ? L'autre joueur sera notifié."
            icon="fas fa-exclamation-triangle text-danger"
            confirmText="Oui, annuler le pacte"
            cancelText="Conserver le pacte"
            onConfirm="performBreak"
            onCancel="dismissModals"
        />
    </div>
</div>