<div>
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
</div>
