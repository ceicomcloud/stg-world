<div page="technology">
    <div class="technology-container">
        <!-- En-tête -->
        <div class="technology-header">
            <div class="research-points">
                <div class="points-display">
                    <i class="fas fa-atom"></i>
                    <span class="points-value">{{ number_format($researchPoints) }}</span>
                    <span class="points-label">Points de Recherche</span>
                </div>
            </div>


        </div>

        <!-- Grille de technologies -->
        <div class="technologies-grid">
            @foreach($technologies as $technology)
                <div class="technology-card">
                    <!-- En-tête de la carte -->
                    <div class="technology-card-header" wire:click="openTechnologyModal({{ $technology['id'] }})">
                        @if($technology['icon'])
                            <img src="{{ asset('images/technologies/' . $technology['icon']) }}" 
                                 alt="{{ $technology['label'] }}" 
                                 class="technology-image">
                        @else
                            <div class="technology-image" style="background: linear-gradient(135deg, #1e293b, #334155); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-flask" style="font-size: 3rem; color: var(--stargate-primary);"></i>
                            </div>
                        @endif
                        
                        <div class="technology-level">
                            <i class="fas fa-layer-group"></i>
                            Niveau {{ $technology['current_level'] }}
                            @if($technology['max_level'] > 0)
                                / {{ $technology['max_level'] }}
                            @endif
                        </div>
                    </div>

                    <!-- Contenu de la carte -->
                    <div class="technology-card-content">
                        <h3 class="technology-name">
                            <i class="fas fa-{{ $technology['category'] === 'research' ? 'microscope' : ($technology['category'] === 'military' ? 'shield-alt' : 'cog') }}"></i>
                            {{ $technology['label'] }}
                        </h3>
                        
                        @if($technology['description'])
                            <p class="technology-description">{{ Str::limit($technology['description'], 100) }}</p>
                        @endif

                        <!-- Coût de recherche -->
                        @if($technology['current_level'] < $technology['max_level'] || $technology['max_level'] == 0)
                            <div class="research-cost">
                                <div class="cost-item">
                                    <i class="fas fa-atom"></i>
                                    <span>{{ number_format($technology['research_cost']) }} Points</span>
                                </div>
                            </div>
                        @endif

                        <!-- Prérequis -->
                        @if(!$technology['requirements_met'])
                            <div class="requirements-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Prérequis non satisfaits
                            </div>
                        @endif

                        <!-- Bouton de recherche -->
                        <div class="technology-actions">
                            @if($technology['current_level'] >= $technology['max_level'] && $technology['max_level'] > 0)
                                <button class="btn btn-completed" disabled>
                                    <i class="fas fa-check"></i>
                                    Recherche terminée
                                </button>
                            @elseif($technology['can_research'])
                                <button class="btn btn-research" wire:click="startResearch({{ $technology['id'] }})" 
                                        wire:loading.attr="disabled" wire:target="startResearch">
                                    <i class="fas fa-play"></i>
                                    Rechercher
                                </button>
                            @else
                                <button class="btn btn-disabled" disabled>
                                    @if(!$technology['requirements_met'])
                                        <i class="fas fa-lock"></i>
                                        Prérequis manquants
                                    @elseif($researchPoints < $technology['research_cost'])
                                        <i class="fas fa-coins"></i>
                                        Points insuffisants
                                    @else
                                        <i class="fas fa-ban"></i>
                                        Non disponible
                                    @endif
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if(empty($technologies))
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <h3>Aucune technologie disponible</h3>
                <p>Les technologies seront bientôt disponibles pour la recherche.</p>
            </div>
        @endif
    </div>
</div>