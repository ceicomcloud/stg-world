<div>
    <!-- Indicateur de type -->
    <div class="modal-type-indicator modal-type-report">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Signalement de problème</span>
    </div>
    
    <form wire:submit.prevent="submitReport">
        <div class="form-group mb-4">
            <label class="form-label">
                <i class="fas fa-tag"></i> Catégorie
            </label>
            <div class="category-selector">
                <div class="category-dropdown" x-data="{ open: false }" x-on:click.outside="open = false">
                    <button type="button" class="category-button" x-on:click="open = !open" :class="{ 'open': open }">
                        <i class="fas" :class="{
                            'fa-bug': '{{ $category }}' === 'bug',
                            'fa-gamepad': '{{ $category }}' === 'gameplay',
                            'fa-lightbulb': '{{ $category }}' === 'suggestion',
                            'fa-question-circle': '{{ $category }}' === 'other'
                        }"></i>
                        <span>
                            @foreach($categories as $value => $label)
                                <span x-show="'{{ $category }}' === '{{ $value }}'">{{ $label }}</span>
                            @endforeach
                        </span>
                        <i class="fas fa-chevron-down dropdown-icon" :class="{ 'rotated': open }"></i>
                    </button>

                    <div class="category-options" x-show="open" x-transition style="display: none;">
                        @foreach($categories as $value => $label)
                            <div class="category-option {{ $category === $value ? 'active' : '' }}" 
                                    wire:click="$set('category', '{{ $value }}')" 
                                    x-on:click="open = false">
                                <div class="category-option-info">
                                    <i class="fas {{ 
                                        $value === 'bug' ? 'fa-bug' : 
                                        ($value === 'gameplay' ? 'fa-gamepad' : 
                                        ($value === 'suggestion' ? 'fa-lightbulb' : 'fa-question-circle')) 
                                    }}"></i>
                                    <div class="category-details">
                                        <span class="category-name">{{ $label }}</span>
                                    </div>
                                </div>
                                @if($category === $value)
                                <div class="category-badge">
                                    <i class="fas fa-check"></i>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @error('category') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        
        <div class="form-group mb-4">
            <label for="problem" class="form-label">
                <i class="fas fa-comment-alt"></i> Description du problème
            </label>
            <textarea wire:model="problem" id="problem" class="form-control" rows="5" placeholder="Décrivez le problème rencontré en détail..."></textarea>
            @error('problem') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" wire:click="$dispatch('closeModal')">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="submitReport">
                <i class="fas fa-paper-plane" wire:loading.class.remove="fa-paper-plane" wire:loading.class.add="fa-spinner fa-spin" wire:target="submitReport"></i>
                <span wire:loading.remove wire:target="submitReport">Envoyer</span>
                <span wire:loading wire:target="submitReport">Envoi en cours...</span>
            </button>
        </div>
    </form>
</div>
