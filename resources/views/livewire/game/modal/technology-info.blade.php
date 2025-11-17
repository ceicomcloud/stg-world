<div>
    <!-- Indicateur de type -->
    <div class="modal-type-indicator modal-type-{{ $technologyData['type'] }}">
        <i class="fas fa-{{ $this->getTypeIcon() }}"></i>
        {{ $this->getTypeLabel() }}
    </div>

    <!-- Image -->
    @if($technologyData['icon'])
        <img src="{{ asset('images/technologies/' . $technologyData['icon']) }}"  alt="{{ $technologyData['label'] }}" class="modal-image">
    @endif

    <!-- Description -->
    @if($technologyData['description'])
        <div class="modal-description">
            {{ $technologyData['description'] }}
        </div>
    @endif

    <!-- Niveau actuel -->
    <div class="modal-level-info">
        <i class="fas fa-layer-group"></i>
        <span class="level-text">Niveau actuel:</span>
        <span class="level-value">
            {{ $technologyData['current_level'] }}
            @if($technologyData['max_level'] > 0)
                / {{ $technologyData['max_level'] }}
            @endif
        </span>
    </div>

    <!-- Avantages -->
    @if(count($technologyData['advantages']) > 0)
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-plus-circle"></i>
                Avantages
            </h3>
            <div class="advantages-list">
                @foreach($technologyData['advantages'] as $advantage)
                    <div class="advantage-item">
                        <i class="fas fa-check"></i>
                        <span>{{ $advantage['description'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Désavantages -->
    @if(count($technologyData['disadvantages']) > 0)
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-minus-circle"></i>
                Désavantages
            </h3>
            <div class="disadvantages-list">
                @foreach($technologyData['disadvantages'] as $disadvantage)
                    <div class="disadvantage-item">
                        <i class="fas fa-times"></i>
                        <span>{{ $disadvantage['description'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Prérequis -->
    @if(count($technologyData['requirements']) > 0)
        <div class="modal-section">
            <h3 class="section-title">
                <i class="fas fa-list-check"></i>
                Prérequis
            </h3>
            <div class="requirements-list">
                @foreach($technologyData['requirements'] as $requirement)
                    @php
                        $isMet = $this->checkRequirement($requirement);
                    @endphp
                    <div class="requirement-item {{ $isMet ? 'requirement-met' : 'requirement-not-met' }}">
                        <i class="fas fa-{{ $isMet ? 'check' : 'times' }}"></i>
                        <span>
                            {{ $requirement['required_build']['label'] ?? $requirement['required_build']['name'] ?? 'Technologie requise' }}
                            niveau {{ $requirement['required_level'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>