<div>
    <h3>🔍 Rechercher une Alliance</h3>
                    
    <div class="alliance-search">
        <input type="text" class="search-input" wire:model.live="searchQuery" 
            placeholder="Rechercher par nom ou tag...">
    </div>
    
    @if(count($searchResults) > 0)
        <div class="search-results">
            @foreach($searchResults as $result)
                <div class="alliance-result">
                    <div class="alliance-result-info">
                        <h4>{{ $result->name }} [{{ $result->tag }}]</h4>
                        <p>Leader: {{ $result->leader->name }}</p>
                        @if($result->external_description)
                            <p>{!! Str::limit($result->external_description, 100) !!}</p>
                        @endif
                    </div>
                    
                    <div class="alliance-result-stats">
                        <div class="result-stat">
                            <span class="result-stat-value">{{ $result->member_count }}</span>
                            <span class="result-stat-label">Membres</span>
                        </div>
                        <div class="result-stat">
                            <span class="result-stat-value">{{ $result->open_recruitment ? 'Ouvert' : 'Fermé' }}</span>
                            <span class="result-stat-label">Recrutement</span>
                        </div>
                        
                        @if($result->open_recruitment && $result->canAcceptNewMembers())
                            <button class="btn btn-primary" 
                                    wire:click="applyToAlliance({{ $result->id }})">
                                📝 Candidater
                            </button>
                        @else
                            <span style="color: var(--stargate-text-secondary); font-size: 12px;">
                                {{ $result->canAcceptNewMembers() ? 'Recrutement fermé' : 'Alliance complète' }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @elseif(!empty($searchQuery))
        <p style="text-align: center; color: var(--stargate-text-secondary); padding: 40px;">
            Aucune alliance trouvée
        </p>
    @endif
</div>
