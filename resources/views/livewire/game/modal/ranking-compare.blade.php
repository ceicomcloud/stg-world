<div class="ranking-compare-modal">
    <div class="ranking-compare-container">
        @if($user1Stats && $user2Stats)
            <div class="ranking-compare-header">
                <div class="ranking-compare-player">
                    <div class="ranking-compare-player-rank {{ $this->getRankColor($user1Stats['rank']) }}">
                        {{ $user1Stats['rank'] }}
                    </div>
                    <div class="ranking-compare-player-name">{{ $user1Stats['name'] }}</div>
                    <div class="ranking-compare-player-alliance">
                        @if(isset($user1Stats['alliance_tag']))
                            [{{ $user1Stats['alliance_tag'] }}]
                        @else
                            <span class="text-muted">Sans alliance</span>
                        @endif
                    </div>
                </div>
                <div class="ranking-compare-vs">
                    VS
                </div>
                <div class="ranking-compare-player">
                    <div class="ranking-compare-player-rank {{ $this->getRankColor($user2Stats['rank']) }}">
                        {{ $user2Stats['rank'] }}
                    </div>
                    <div class="ranking-compare-player-name">{{ $user2Stats['name'] }}</div>
                    <div class="ranking-compare-player-alliance">
                        @if(isset($user2Stats['alliance_tag']))
                            [{{ $user2Stats['alliance_tag'] }}]
                        @else
                            <span class="text-muted">Sans alliance</span>
                        @endif
                    </div>
                </div>
            </div>

        <!-- Points totaux -->
        <div class="ranking-compare-section">
            <div class="ranking-compare-section-title">Points Totaux</div>
            <div class="ranking-compare-total">
                <div class="ranking-compare-total-player">
                    <div class="ranking-compare-total-points {{ $user1Stats['total_points'] > $user2Stats['total_points'] ? 'positive' : '' }}">
                        {{ number_format($user1Stats['total_points']) }}
                    </div>
                    <div class="ranking-compare-total-label">Points</div>
                </div>
                
                @php
                    $difference = $this->calculateDifference($user1Stats['total_points'], $user2Stats['total_points']);
                @endphp
                <div class="ranking-compare-difference">
                    @if($difference['equal'])
                        <div class="ranking-compare-difference-value">Égalité</div>
                    @else
                        <div class="ranking-compare-difference-value {{ $difference['positive'] ? 'positive' : 'negative' }}">
                            {{ $difference['positive'] ? '+' : '-' }} {{ number_format(abs($difference['value'])) }}
                        </div>
                        <div class="ranking-compare-difference-label">
                            @if($difference['positive'])
                                <i class="fas fa-arrow-left"></i> Avantage
                            @else
                                Avantage <i class="fas fa-arrow-right"></i>
                            @endif
                        </div>
                    @endif
                </div>
                
                <div class="ranking-compare-total-player">
                    <div class="ranking-compare-total-points {{ $user2Stats['total_points'] > $user1Stats['total_points'] ? 'positive' : '' }}">
                        {{ number_format($user2Stats['total_points']) }}
                    </div>
                    <div class="ranking-compare-total-label">Points</div>
                </div>
            </div>
        </div>

        <!-- Répartition des points -->
        <div class="ranking-compare-section">
            <div class="ranking-compare-section-title">Répartition des Points</div>
            <div class="ranking-compare-categories">
                <!-- Bâtiments -->
                <div class="ranking-compare-category">
                    <div class="ranking-compare-category-header">
                        <div class="ranking-compare-category-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="ranking-compare-category-title">Bâtiments</div>
                    </div>
                    <div class="ranking-compare-category-players">
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points {{ $user1Stats['building_points'] > $user2Stats['building_points'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['building_points']) }}
                            </div>
                            <div class="ranking-compare-category-percentage">
                                {{ $this->calculatePercentage($user1Stats['building_points'], $user1Stats['total_points']) }}% du total
                            </div>
                        </div>
                        
                        @php
                            $buildingDiff = $this->calculateDifference($user1Stats['building_points'], $user2Stats['building_points']);
                        @endphp
                        <div class="ranking-compare-category-difference {{ $buildingDiff['positive'] ? 'positive' : ($buildingDiff['negative'] ? 'negative' : '') }}">
                            @if(!$buildingDiff['equal'])
                                {{ $buildingDiff['positive'] ? '+' : '-' }} {{ number_format(abs($buildingDiff['value'])) }}
                            @else
                                =
                            @endif
                        </div>
                        
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points {{ $user2Stats['building_points'] > $user1Stats['building_points'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['building_points']) }}
                            </div>
                            <div class="ranking-compare-category-percentage">
                                {{ $this->calculatePercentage($user2Stats['building_points'], $user2Stats['total_points']) }}% du total
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Unités -->
                <div class="ranking-compare-category">
                    <div class="ranking-compare-category-header">
                        <div class="ranking-compare-category-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="ranking-compare-category-title">Unités</div>
                    </div>
                    <div class="ranking-compare-category-players">
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points {{ $user1Stats['units_points'] > $user2Stats['units_points'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['units_points']) }}
                            </div>
                            <div class="ranking-compare-category-percentage">
                                {{ $this->calculatePercentage($user1Stats['units_points'], $user1Stats['total_points']) }}% du total
                            </div>
                        </div>
                        
                        @php
                            $unitsDiff = $this->calculateDifference($user1Stats['units_points'], $user2Stats['units_points']);
                        @endphp
                        <div class="ranking-compare-category-difference {{ $unitsDiff['positive'] ? 'positive' : ($unitsDiff['negative'] ? 'negative' : '') }}">
                            @if(!$unitsDiff['equal'])
                                {{ $unitsDiff['positive'] ? '+' : '-' }} {{ number_format(abs($unitsDiff['value'])) }}
                            @else
                                =
                            @endif
                        </div>
                        
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points {{ $user2Stats['units_points'] > $user1Stats['units_points'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['units_points']) }}
                            </div>
                            <div class="ranking-compare-category-percentage">
                                {{ $this->calculatePercentage($user2Stats['units_points'], $user2Stats['total_points']) }}% du total
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Défenses -->
                <div class="ranking-compare-category">
                    <div class="ranking-compare-category-header">
                        <div class="ranking-compare-category-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="ranking-compare-category-title">Défenses</div>
                    </div>
                    <div class="ranking-compare-category-players">
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points {{ $user1Stats['defense_points'] > $user2Stats['defense_points'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['defense_points']) }}
                            </div>
                            <div class="ranking-compare-category-percentage">
                                {{ $this->calculatePercentage($user1Stats['defense_points'], $user1Stats['total_points']) }}% du total
                            </div>
                        </div>
                        
                        @php
                            $defenseDiff = $this->calculateDifference($user1Stats['defense_points'], $user2Stats['defense_points']);
                        @endphp
                        <div class="ranking-compare-category-difference {{ $defenseDiff['positive'] ? 'positive' : ($defenseDiff['negative'] ? 'negative' : '') }}">
                            @if(!$defenseDiff['equal'])
                                {{ $defenseDiff['positive'] ? '+' : '-' }} {{ number_format(abs($defenseDiff['value'])) }}
                            @else
                                =
                            @endif
                        </div>
                        
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points {{ $user2Stats['defense_points'] > $user1Stats['defense_points'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['defense_points']) }}
                            </div>
                            <div class="ranking-compare-category-percentage">
                                {{ $this->calculatePercentage($user2Stats['defense_points'], $user2Stats['total_points']) }}% du total
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Vaisseaux -->
                <div class="ranking-compare-category">
                    <div class="ranking-compare-category-header">
                        <div class="ranking-compare-category-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <div class="ranking-compare-category-title">Vaisseaux</div>
                    </div>
                    <div class="ranking-compare-category-players">
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points {{ $user1Stats['ship_points'] > $user2Stats['ship_points'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['ship_points']) }}
                            </div>
                            <div class="ranking-compare-category-percentage">
                                {{ $this->calculatePercentage($user1Stats['ship_points'], $user1Stats['total_points']) }}% du total
                            </div>
                        </div>
                        
                        @php
                            $shipDiff = $this->calculateDifference($user1Stats['ship_points'], $user2Stats['ship_points']);
                        @endphp
                        <div class="ranking-compare-category-difference {{ $shipDiff['positive'] ? 'positive' : ($shipDiff['negative'] ? 'negative' : '') }}">
                            @if(!$shipDiff['equal'])
                                {{ $shipDiff['positive'] ? '+' : '-' }} {{ number_format(abs($shipDiff['value'])) }}
                            @else
                                =
                            @endif
                        </div>
                        
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points {{ $user2Stats['ship_points'] > $user1Stats['ship_points'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['ship_points']) }}
                            </div>
                            <div class="ranking-compare-category-percentage">
                                {{ $this->calculatePercentage($user2Stats['ship_points'], $user2Stats['total_points']) }}% du total
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Technologies -->
                <div class="ranking-compare-category">
                    <div class="ranking-compare-category-header">
                        <div class="ranking-compare-category-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="ranking-compare-category-title">Technologies</div>
                    </div>
                    <div class="ranking-compare-category-players">
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points {{ $user1Stats['technology_points'] > $user2Stats['technology_points'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['technology_points']) }}
                            </div>
                            <div class="ranking-compare-category-percentage">
                                {{ $this->calculatePercentage($user1Stats['technology_points'], $user1Stats['total_points']) }}% du total
                            </div>
                        </div>
                        
                        @php
                            $techDiff = $this->calculateDifference($user1Stats['technology_points'], $user2Stats['technology_points']);
                        @endphp
                        <div class="ranking-compare-category-difference {{ $techDiff['positive'] ? 'positive' : ($techDiff['negative'] ? 'negative' : '') }}">
                            @if(!$techDiff['equal'])
                                {{ $techDiff['positive'] ? '+' : '-' }} {{ number_format(abs($techDiff['value'])) }}
                            @else
                                =
                            @endif
                        </div>
                        
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points {{ $user2Stats['technology_points'] > $user1Stats['technology_points'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['technology_points']) }}
                            </div>
                            <div class="ranking-compare-category-percentage">
                                {{ $this->calculatePercentage($user2Stats['technology_points'], $user2Stats['total_points']) }}% du total
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Combat Terrestre -->
        <div class="ranking-compare-section">
            <div class="ranking-compare-section-title">Combat Terrestre</div>
            <div class="ranking-compare-combat">
                <!-- Points d'attaque et défense -->
                <div class="ranking-compare-combat-type">
                    <div class="ranking-compare-combat-type-title">Puissance</div>
                    
                    <!-- Points d'attaque -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-fist-raised"></i> Points d'attaque
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player {{ $user1Stats['earth_attack'] > $user2Stats['earth_attack'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['earth_attack']) }}
                            </div>
                            
                            @php
                                $attackDiff = $this->calculateDifference($user1Stats['earth_attack'], $user2Stats['earth_attack']);
                            @endphp
                            <div class="ranking-compare-combat-stat-difference {{ $attackDiff['positive'] ? 'positive' : ($attackDiff['negative'] ? 'negative' : '') }}">
                                @if(!$attackDiff['equal'])
                                    {{ $attackDiff['positive'] ? '+' : '-' }} {{ number_format(abs($attackDiff['value'])) }}
                                @else
                                    =
                                @endif
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player {{ $user2Stats['earth_attack'] > $user1Stats['earth_attack'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['earth_attack']) }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Points de défense -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-shield-alt"></i> Points de défense
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player {{ $user1Stats['earth_defense'] > $user2Stats['earth_defense'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['earth_defense']) }}
                            </div>
                            
                            @php
                                $defenseDiff = $this->calculateDifference($user1Stats['earth_defense'], $user2Stats['earth_defense']);
                            @endphp
                            <div class="ranking-compare-combat-stat-difference {{ $defenseDiff['positive'] ? 'positive' : ($defenseDiff['negative'] ? 'negative' : '') }}">
                                @if(!$defenseDiff['equal'])
                                    {{ $defenseDiff['positive'] ? '+' : '-' }} {{ number_format(abs($defenseDiff['value'])) }}
                                @else
                                    =
                                @endif
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player {{ $user2Stats['earth_defense'] > $user1Stats['earth_defense'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['earth_defense']) }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistiques de combat -->
                <div class="ranking-compare-combat-type">
                    <div class="ranking-compare-combat-type-title">Statistiques</div>
                    
                    <!-- Victoires en attaque -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-trophy"></i> Victoires en attaque
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player {{ $user1Stats['earth_attack_count'] > $user2Stats['earth_attack_count'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['earth_attack_count']) }}
                            </div>
                            
                            @php
                                $attackCountDiff = $this->calculateDifference($user1Stats['earth_attack_count'], $user2Stats['earth_attack_count']);
                            @endphp
                            <div class="ranking-compare-combat-stat-difference {{ $attackCountDiff['positive'] ? 'positive' : ($attackCountDiff['negative'] ? 'negative' : '') }}">
                                @if(!$attackCountDiff['equal'])
                                    {{ $attackCountDiff['positive'] ? '+' : '-' }} {{ number_format(abs($attackCountDiff['value'])) }}
                                @else
                                    =
                                @endif
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player {{ $user2Stats['earth_attack_count'] > $user1Stats['earth_attack_count'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['earth_attack_count']) }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Victoires en défense -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-medal"></i> Victoires en défense
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player {{ $user1Stats['earth_defense_count'] > $user2Stats['earth_defense_count'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['earth_defense_count']) }}
                            </div>
                            
                            @php
                                $defenseCountDiff = $this->calculateDifference($user1Stats['earth_defense_count'], $user2Stats['earth_defense_count']);
                            @endphp
                            <div class="ranking-compare-combat-stat-difference {{ $defenseCountDiff['positive'] ? 'positive' : ($defenseCountDiff['negative'] ? 'negative' : '') }}">
                                @if(!$defenseCountDiff['equal'])
                                    {{ $defenseCountDiff['positive'] ? '+' : '-' }} {{ number_format(abs($defenseCountDiff['value'])) }}
                                @else
                                    =
                                @endif
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player {{ $user2Stats['earth_defense_count'] > $user1Stats['earth_defense_count'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['earth_defense_count']) }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Défaites -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-skull"></i> Défaites
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player {{ $user1Stats['earth_loser_count'] < $user2Stats['earth_loser_count'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['earth_loser_count']) }}
                            </div>
                            
                            @php
                                // Inverse la différence car moins de défaites est mieux
                                $loserCountDiff = $this->calculateDifference($user2Stats['earth_loser_count'], $user1Stats['earth_loser_count']);
                            @endphp
                            <div class="ranking-compare-combat-stat-difference {{ $loserCountDiff['positive'] ? 'positive' : ($loserCountDiff['negative'] ? 'negative' : '') }}">
                                @if(!$loserCountDiff['equal'])
                                    {{ $loserCountDiff['positive'] ? '-' : '+' }} {{ number_format(abs($loserCountDiff['value'])) }}
                                @else
                                    =
                                @endif
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player {{ $user2Stats['earth_loser_count'] < $user1Stats['earth_loser_count'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['earth_loser_count']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Combat Spatial -->
        <div class="ranking-compare-section">
            <div class="ranking-compare-section-title">Combat Spatial</div>
            <div class="ranking-compare-combat">
                <!-- Points d'attaque et défense -->
                <div class="ranking-compare-combat-type">
                    <div class="ranking-compare-combat-type-title">Puissance</div>
                    
                    <!-- Points d'attaque -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-fighter-jet"></i> Points d'attaque
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player {{ $user1Stats['spatial_attack'] > $user2Stats['spatial_attack'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['spatial_attack']) }}
                            </div>
                            
                            @php
                                $spaceAttackDiff = $this->calculateDifference($user1Stats['spatial_attack'], $user2Stats['spatial_attack']);
                            @endphp
                            <div class="ranking-compare-combat-stat-difference {{ $spaceAttackDiff['positive'] ? 'positive' : ($spaceAttackDiff['negative'] ? 'negative' : '') }}">
                                @if(!$spaceAttackDiff['equal'])
                                    {{ $spaceAttackDiff['positive'] ? '+' : '-' }} {{ number_format(abs($spaceAttackDiff['value'])) }}
                                @else
                                    =
                                @endif
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player {{ $user2Stats['spatial_attack'] > $user1Stats['spatial_attack'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['spatial_attack']) }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Points de défense -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-shield-alt"></i> Points de défense
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player {{ $user1Stats['spatial_defense'] > $user2Stats['spatial_defense'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['spatial_defense']) }}
                            </div>
                            
                            @php
                                $spaceDefenseDiff = $this->calculateDifference($user1Stats['spatial_defense'], $user2Stats['spatial_defense']);
                            @endphp
                            <div class="ranking-compare-combat-stat-difference {{ $spaceDefenseDiff['positive'] ? 'positive' : ($spaceDefenseDiff['negative'] ? 'negative' : '') }}">
                                @if(!$spaceDefenseDiff['equal'])
                                    {{ $spaceDefenseDiff['positive'] ? '+' : '-' }} {{ number_format(abs($spaceDefenseDiff['value'])) }}
                                @else
                                    =
                                @endif
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player {{ $user2Stats['spatial_defense'] > $user1Stats['spatial_defense'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['spatial_defense']) }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistiques de combat -->
                <div class="ranking-compare-combat-type">
                    <div class="ranking-compare-combat-type-title">Statistiques</div>
                    
                    <!-- Victoires en attaque -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-trophy"></i> Victoires en attaque
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player {{ $user1Stats['spatial_attack_count'] > $user2Stats['spatial_attack_count'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['spatial_attack_count']) }}
                            </div>
                            
                            @php
                                $spaceAttackCountDiff = $this->calculateDifference($user1Stats['spatial_attack_count'], $user2Stats['spatial_attack_count']);
                            @endphp
                            <div class="ranking-compare-combat-stat-difference {{ $spaceAttackCountDiff['positive'] ? 'positive' : ($spaceAttackCountDiff['negative'] ? 'negative' : '') }}">
                                @if(!$spaceAttackCountDiff['equal'])
                                    {{ $spaceAttackCountDiff['positive'] ? '+' : '-' }} {{ number_format(abs($spaceAttackCountDiff['value'])) }}
                                @else
                                    =
                                @endif
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player {{ $user2Stats['spatial_attack_count'] > $user1Stats['spatial_attack_count'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['spatial_attack_count']) }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Victoires en défense -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-medal"></i> Victoires en défense
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player {{ $user1Stats['spatial_defense_count'] > $user2Stats['spatial_defense_count'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['spatial_defense_count']) }}
                            </div>
                            
                            @php
                                $spaceDefenseCountDiff = $this->calculateDifference($user1Stats['spatial_defense_count'], $user2Stats['spatial_defense_count']);
                            @endphp
                            <div class="ranking-compare-combat-stat-difference {{ $spaceDefenseCountDiff['positive'] ? 'positive' : ($spaceDefenseCountDiff['negative'] ? 'negative' : '') }}">
                                @if(!$spaceDefenseCountDiff['equal'])
                                    {{ $spaceDefenseCountDiff['positive'] ? '+' : '-' }} {{ number_format(abs($spaceDefenseCountDiff['value'])) }}
                                @else
                                    =
                                @endif
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player {{ $user2Stats['spatial_defense_count'] > $user1Stats['spatial_defense_count'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['spatial_defense_count']) }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Défaites -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-skull"></i> Défaites
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player {{ $user1Stats['spatial_loser_count'] < $user2Stats['spatial_loser_count'] ? 'highlight' : '' }}">
                                {{ number_format($user1Stats['spatial_loser_count']) }}
                            </div>
                            
                            @php
                                // Inverse la différence car moins de défaites est mieux
                                $spaceLoserCountDiff = $this->calculateDifference($user2Stats['spatial_loser_count'], $user1Stats['spatial_loser_count']);
                            @endphp
                            <div class="ranking-compare-combat-stat-difference {{ $spaceLoserCountDiff['positive'] ? 'positive' : ($spaceLoserCountDiff['negative'] ? 'negative' : '') }}">
                                @if(!$spaceLoserCountDiff['equal'])
                                    {{ $spaceLoserCountDiff['positive'] ? '-' : '+' }} {{ number_format(abs($spaceLoserCountDiff['value'])) }}
                                @else
                                    =
                                @endif
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player {{ $user2Stats['spatial_loser_count'] < $user1Stats['spatial_loser_count'] ? 'highlight' : '' }}">
                                {{ number_format($user2Stats['spatial_loser_count']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations Générales -->
        <div class="ranking-compare-section">
            <div class="ranking-compare-section-title">Informations Générales</div>
            <div class="ranking-compare-info">
                <!-- Nombre de planètes -->
                <div class="ranking-compare-info-stat">
                    <div class="ranking-compare-info-stat-label">
                        <i class="fas fa-globe"></i> Nombre de planètes
                    </div>
                    <div class="ranking-compare-info-stat-values">
                        <div class="ranking-compare-info-stat-player {{ $user1Stats['planets_count'] > $user2Stats['planets_count'] ? 'highlight' : '' }}">
                            {{ number_format($user1Stats['planets_count']) }}
                        </div>
                        
                        @php
                            $planetsCountDiff = $this->calculateDifference($user1Stats['planets_count'], $user2Stats['planets_count']);
                        @endphp
                        <div class="ranking-compare-info-stat-difference {{ $planetsCountDiff['positive'] ? 'positive' : ($planetsCountDiff['negative'] ? 'negative' : '') }}">
                            @if(!$planetsCountDiff['equal'])
                                {{ $planetsCountDiff['positive'] ? '+' : '-' }} {{ number_format(abs($planetsCountDiff['value'])) }}
                            @else
                                =
                            @endif
                        </div>
                        
                        <div class="ranking-compare-info-stat-player {{ $user2Stats['planets_count'] > $user1Stats['planets_count'] ? 'highlight' : '' }}">
                            {{ number_format($user2Stats['planets_count']) }}
                        </div>
                    </div>
                </div>
                
                <!-- Date d'inscription -->
                <div class="ranking-compare-info-stat">
                    <div class="ranking-compare-info-stat-label">
                        <i class="fas fa-calendar-alt"></i> Date d'inscription
                    </div>
                    <div class="ranking-compare-info-stat-values">
                        <div class="ranking-compare-info-stat-player">
                            {{ $user1Stats['created_at']->format('d/m/Y') }}
                        </div>
                        
                        <div class="ranking-compare-info-stat-difference">
                            @php
                                $days = $user1Stats['created_at']->diffInDays($user2Stats['created_at'], false);
                            @endphp
                            @if($days > 0)
                                <span class="positive">+{{ $days }} jours</span>
                            @elseif($days < 0)
                                <span class="negative">{{ $days }} jours</span>
                            @else
                                <span>=</span>
                            @endif
                        </div>
                        
                        <div class="ranking-compare-info-stat-player">
                            {{ $user2Stats['created_at']->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="ranking-compare-empty">
            <div class="ranking-compare-empty-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="ranking-compare-empty-message">Impossible de charger les données des joueurs</div>
            <div class="ranking-compare-empty-description">Veuillez réessayer ultérieurement.</div>
        </div>
    @endif
</div>