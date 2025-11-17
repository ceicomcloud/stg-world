<div class="mission-info-modal">
    <!-- En-tête avec type et statut de mission -->
    <div class="mission-info-header">
        <div class="mission-info-type-icon mission-info-type-{{ $missionData['type'] ?? 'default' }}">
            <i class="fas fa-{{ $this->getMissionTypeIcon() }}"></i>
        </div>
        <div class="mission-info-details">
            <h2 class="mission-info-title">{{ $missionData['type_label'] ?? 'Mission' }}</h2>
            <div class="mission-info-status mission-info-status-{{ $missionData['status'] ?? 'default' }}">
                <i class="fas fa-{{ $this->getMissionStatusIcon() }}"></i>
                <span>{{ $missionData['status_label'] ?? 'Statut inconnu' }}</span>
                @if(isset($missionData['time_remaining']) && in_array($missionData['status'], ['traveling', 'returning']))
                    <span class="mission-timer" data-end-time="{{ $missionData['status'] === 'traveling' && isset($missionData['arrival_time']) ? $missionData['arrival_time']->unix() : (isset($missionData['return_time']) ? $missionData['return_time']->unix() : '') }}">
                        {{ $missionData['time_remaining'] }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Informations sur l'itinéraire -->
    <div class="mission-info-section">
        <h3 class="mission-info-section-title">
            <i class="fas fa-route"></i>
            Itinéraire
        </h3>
        <div class="mission-info-route">
            <div class="mission-info-planet">
                <i class="fas fa-globe"></i>
                <span class="mission-info-planet-name">{{ $missionData['from_planet']['name'] ?? 'Planète inconnue' }}</span>
                <span class="mission-info-planet-coords">[{{ $missionData['from_planet']['coordinates']['galaxy'] ?? 'N/A' }}:{{ $missionData['from_planet']['coordinates']['system'] ?? 'N/A' }}:{{ $missionData['from_planet']['coordinates']['position'] ?? 'N/A' }}]</span>
            </div>
            <div class="mission-info-direction">
                <i class="fas fa-long-arrow-alt-right"></i>
            </div>
            <div class="mission-info-planet">
                <i class="fas fa-globe"></i>
                @if(isset($missionData['to_planet']))
                    <span class="mission-info-planet-name">{{ $missionData['to_planet']['name'] ?? 'Planète inconnue' }}</span>
                @endif
                <span class="mission-info-planet-coords">[{{ $missionData['to_coordinates']['galaxy'] ?? 'N/A' }}:{{ $missionData['to_coordinates']['system'] ?? 'N/A' }}:{{ $missionData['to_coordinates']['position'] ?? 'N/A' }}]</span>
            </div>
        </div>
        <div class="mission-info-times">
            <div class="mission-info-time">
                <i class="fas fa-calendar-day"></i>
                <span class="mission-info-time-label">Départ</span>
                <span class="mission-info-time-value">{{ isset($missionData['departure_time']) ? $missionData['departure_time']->format('d/m/Y H:i:s') : 'N/A' }}</span>
            </div>
            <div class="mission-info-time">
                <i class="fas fa-calendar-check"></i>
                <span class="mission-info-time-label">Arrivée</span>
                <span class="mission-info-time-value">{{ isset($missionData['arrival_time']) ? $missionData['arrival_time']->format('d/m/Y H:i:s') : 'N/A' }}</span>
            </div>
            @if(isset($missionData['return_time']))
                <div class="mission-info-time">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="mission-info-time-label">Retour</span>
                    <span class="mission-info-time-value">{{ $missionData['return_time']->format('d/m/Y H:i:s') }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Vaisseaux -->
    @if(isset($missionData['ships']) && count($missionData['ships']) > 0)
        <div class="mission-info-section">
            <h3 class="mission-info-section-title">
                <i class="fas fa-rocket"></i>
                Vaisseaux
            </h3>
            <div class="mission-info-ships">
                @foreach($missionData['ships'] as $key => $ship)
                    @php
                        // Support both legacy and normalized payloads
                        $quantity = null;
                        $name = null;
                        $icon = null;
                        $type = null;
                        $templateId = null;

                        if (is_array($ship)) {
                            // Legacy: detailed array structure
                            if (isset($ship['quantity'])) {
                                $quantity = is_array($ship['quantity']) ? ($ship['quantity']['quantity'] ?? null) : $ship['quantity'];
                            }
                            $name = $ship['name'] ?? null;
                            $icon = $ship['icon'] ?? null;
                            $type = $ship['type'] ?? null;
                            $templateId = $ship['template_id'] ?? ($ship['id'] ?? null);
                        } else {
                            // Normalized: map of template_id => quantity
                            $templateId = $key;
                            $quantity = $ship;
                        }

                        // Backfill from template when needed
                        if ((!$name || !$icon || !$type) && $templateId) {
                            $tpl = \App\Models\Template\TemplateBuild::find($templateId);
                            if ($tpl) {
                                $name = $name ?? ($tpl->label ?? $tpl->name);
                                $icon = $icon ?? $tpl->icon;
                                $type = $type ?? $tpl->type;
                            }
                        }

                        $isUnit = ($type === \App\Models\Template\TemplateBuild::TYPE_UNIT);
                        $imgFolder = $isUnit ? 'images/units/' : 'images/ships/';
                    @endphp
                    <div class="mission-info-ship">
                        @if(!empty($icon))
                            <img src="{{ asset($imgFolder . $icon) }}" alt="{{ $name ?? 'Vaisseau' }}" class="mission-info-ship-icon">
                        @else
                            <i class="fas fa-rocket mission-info-ship-icon"></i>
                        @endif
                        <span class="mission-info-ship-name">{{ $name ?? 'Unité/Vaisseau' }}</span>
                        @if(is_numeric($quantity))
                            <span class="mission-info-ship-quantity">x{{ number_format($quantity) }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Ressources -->
    @if(isset($missionData['resources']) && count($missionData['resources']) > 0)
        <div class="mission-info-section">
            <h3 class="mission-info-section-title">
                <i class="fas fa-boxes"></i>
                Ressources
            </h3>
            <div class="mission-info-resources">
                @foreach($missionData['resources'] as $resource)
                    @if(isset($resource['amount']) && $resource['amount'] > 0)
                        <div class="mission-info-resource">
                            <img src="{{ asset('images/resources/' . ($resource['icon'] ?? 'default.png')) }}" alt="{{ $resource['name'] ?? 'Ressource' }}" class="mission-info-resource-icon">
                            <span class="mission-info-resource-name">{{ $resource['name'] ?? 'Ressource' }}</span>
                            <span class="mission-info-resource-amount">{{ number_format($resource['amount']) }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <!-- Résultat de la mission -->
    @if(isset($missionData['result']) && count($missionData['result']) > 0)
        <div class="mission-info-section">
            <h3 class="mission-info-section-title">
                <i class="fas fa-clipboard-list"></i>
                Résultat
            </h3>
            <div class="mission-info-result">
                @if(isset($missionData['result']['message']))
                    <div class="mission-info-result-message">
                        {{ $missionData['result']['message'] }}
                    </div>
                @endif
                
                @if(isset($missionData['result']['resources_found']))
                    <div class="mission-info-resources">
                        <h4>Ressources trouvées</h4>
                        @php
                        // Récupérer tous les templates de ressources pour obtenir les noms
                        $templateResources = \App\Models\Template\TemplateResource::all()->keyBy('id');
                        @endphp
                        
                        @foreach($missionData['result']['resources_found'] as $resourceId => $amount)
                            @if($amount > 0)
                                @php
                                $resourceName = isset($templateResources[$resourceId]) ? $templateResources[$resourceId]->name : 'resource';
                                $resourceDisplayName = isset($templateResources[$resourceId]) ? ($templateResources[$resourceId]->display_name ?? ucfirst($resourceName)) : 'Ressource';
                                $resourceIcon = $resourceName . '.png';
                                @endphp
                                <div class="mission-info-resource">
                                    <img src="{{ asset('images/resources/' . $resourceIcon) }}" alt="{{ $resourceDisplayName }}" class="mission-info-resource-icon">
                                    <span class="mission-info-resource-name">{{ $resourceDisplayName }}</span>
                                    <span class="mission-info-resource-amount">{{ number_format($amount) }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
                
                @if(isset($missionData['result']['spy_report']))
                    <div class="mission-info-spy-report">
                        <h4>Rapport d'espionnage</h4>
                        <div class="mission-info-spy-details">
                            {!! $missionData['result']['spy_report'] !!}
                        </div>
                    </div>
                @endif
                
                @if(isset($missionData['result']['battle_report']))
                    <div class="mission-info-battle-report">
                        <h4>Rapport de bataille</h4>
                        <div class="mission-info-battle-details">
                            {!! $missionData['result']['battle_report'] !!}
                        </div>
                    </div>
                @endif
                
                @if(isset($missionData['result']['discoveries']))
                    <div class="mission-info-discoveries">
                        <h4>Découvertes</h4>
                        @foreach($missionData['result']['discoveries'] as $discovery)
                            <div class="mission-info-discovery">
                                <div class="mission-info-discovery-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="mission-info-discovery-details">
                                    <div class="mission-info-discovery-title">Découverte</div>
                                    <div class="mission-info-discovery-description">{{ $discovery }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>