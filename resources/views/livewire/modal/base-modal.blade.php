<div>
    @if($isOpen)
        <div page="modal">
            <div class="building-modal show" wire:click="close">
                <div class="modal-content" wire:click.stop>
                    <!-- En-tête du modal -->
                    <div class="modal-header">
                        <h2 class="modal-title">
                            {{ $modalTitle }}
                        </h2>
                        <button class="modal-close" wire:click="close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Corps du modal -->
                    <div class="modal-body">
                        @if($modalComponent)
                            @livewire($modalComponent, $modalData, key($modalComponent . '-' . now()))
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>