<div page="customization-page">
    <div class="customization-container">
        <div class="customization-header">
            <h1 class="customization-title"><i class="fas fa-paint-brush"></i> Personnalisation des unités et vaisseaux</h1>
            <p class="customization-subtitle">Vue d’ensemble de vos planètes, ressources et forces</p>
        </div>

        @if(empty($items))
            <p class="text-muted">Aucune unité ou vaisseau disponible sur votre planète actuelle.</p>
        @else
            <div class="items-grid">
                @foreach($items as $item)
                    <div class="item-card">
                        <img src="{{ $item['default_icon'] }}" alt="Icône" class="item-icon" />
                        <div class="item-body">
                            <div class="item-title">{{ $item['default_name'] }}</div>
                            <div class="item-type">{{ $item['type'] === \App\Models\Template\TemplateBuild::TYPE_SHIP ? 'Vaisseau' : 'Unité' }}</div>
                        </div>
                        <div class="item-actions">
                            <button class="btn btn-primary" wire:click="edit({{ $item['template_id'] }})" wire:loading.attr="disabled" wire:target="edit">Modifier</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($editing)
            <div class="edit-modal">
                <div class="edit-card">
                    <h3>Modifier l'élément</h3>
                    <div class="form-group">
                        <label>Nouveau nom (optionnel)</label>
                        <input type="text" class="form-control" wire:model.defer="form.display_name" maxlength="50" />
                    </div>
                    <div class="form-group">
                        <label>Nouvelle icône (PNG/JPG, max 512KB)</label>
                        <input type="file" class="form-control" wire:model="form.icon" accept="image/*" />
                        <div wire:loading wire:target="form.icon" class="uploading-indicator">
                            Téléversement en cours...
                        </div>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-success" wire:click="save" wire:loading.attr="disabled" wire:target="save">Enregistrer</button>
                        <button class="btn btn-secondary" wire:click="$set('editing', null)">Annuler</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Styles dédiés dans resources/css/customization-page.css --}}