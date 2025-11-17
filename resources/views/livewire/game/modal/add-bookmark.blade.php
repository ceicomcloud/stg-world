<div>
    <!-- Coordonnées -->
    <div class="modal-section">
        <div class="general-info-grid">
            <div class="info-item">
                <i class="fas fa-location-dot"></i>
                <span class="info-label">Coordonnées</span>
                <span class="info-value">[{{ $galaxy }}:{{ $system }}:{{ $position }}]</span>
            </div>
        </div>
    </div>

    <!-- Nom du bookmark -->
    <div class="modal-section">
        <input type="text" class="form-control" wire:model.defer="label" placeholder="Ex: Raids G5-S123-P7" />
    </div>

    <!-- Actions -->
    <div class="modal-section">
        <div class="modal-footer">
            <button class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                <i class="fas fa-save"></i>
                Enregistrer
            </button>
        </div>
    </div>
</div>