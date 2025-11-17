@props([
    'title' => 'Confirmation',
    'message' => '',
    'confirmText' => 'Confirmer',
    'cancelText' => 'Annuler',
    'icon' => 'fas fa-question-circle',
    // Actions Livewire sous forme de chaînes: e.g. 'performBreak' ou 'dismissModals'
    'onConfirm' => null,
    'onCancel' => null,
])

<div page="modal"
     x-data="{ open: @entangle($attributes->wire('model')).live }"
     x-show="open"
     x-cloak
>
    <div class="building-modal show">
        <div class="modal-content" x-on:click.stop>
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="{{ $icon }}"></i>
                    {{ $title }}
                </h5>
                <button class="modal-close" x-on:click="open = false" @if($onCancel) wire:click="{{ $onCancel }}" @endif>
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                @if($message)
                    <p>{{ $message }}</p>
                @endif

                {{ $slot }}

                <div class="planet-actions">
                    <button class="action-btn secondary" x-on:click="open = false" @if($onCancel) wire:click="{{ $onCancel }}" @endif>
                        {{ $cancelText }}
                    </button>
                    <button class="action-btn danger" @if($onConfirm) wire:click="{{ $onConfirm }}" @endif wire:loading.attr="disabled">
                        <i class="fas fa-check"></i> {{ $confirmText }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>