<div class="ranking-compare-modal">
    <div class="ranking-compare-container">
        <?php if($user1Stats && $user2Stats): ?>
            <div class="ranking-compare-header">
                <div class="ranking-compare-player">
                    <div class="ranking-compare-player-rank <?php echo e($this->getRankColor($user1Stats['rank'])); ?>">
                        <?php echo e($user1Stats['rank']); ?>

                    </div>
                    <div class="ranking-compare-player-name"><?php echo e($user1Stats['name']); ?></div>
                    <div class="ranking-compare-player-alliance">
                        <?php if(isset($user1Stats['alliance_tag'])): ?>
                            [<?php echo e($user1Stats['alliance_tag']); ?>]
                        <?php else: ?>
                            <span class="text-muted">Sans alliance</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ranking-compare-vs">
                    VS
                </div>
                <div class="ranking-compare-player">
                    <div class="ranking-compare-player-rank <?php echo e($this->getRankColor($user2Stats['rank'])); ?>">
                        <?php echo e($user2Stats['rank']); ?>

                    </div>
                    <div class="ranking-compare-player-name"><?php echo e($user2Stats['name']); ?></div>
                    <div class="ranking-compare-player-alliance">
                        <?php if(isset($user2Stats['alliance_tag'])): ?>
                            [<?php echo e($user2Stats['alliance_tag']); ?>]
                        <?php else: ?>
                            <span class="text-muted">Sans alliance</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <!-- Points totaux -->
        <div class="ranking-compare-section">
            <div class="ranking-compare-section-title">Points Totaux</div>
            <div class="ranking-compare-total">
                <div class="ranking-compare-total-player">
                    <div class="ranking-compare-total-points <?php echo e($user1Stats['total_points'] > $user2Stats['total_points'] ? 'positive' : ''); ?>">
                        <?php echo e(number_format($user1Stats['total_points'])); ?>

                    </div>
                    <div class="ranking-compare-total-label">Points</div>
                </div>
                
                <?php
                    $difference = $this->calculateDifference($user1Stats['total_points'], $user2Stats['total_points']);
                ?>
                <div class="ranking-compare-difference">
                    <?php if($difference['equal']): ?>
                        <div class="ranking-compare-difference-value">Égalité</div>
                    <?php else: ?>
                        <div class="ranking-compare-difference-value <?php echo e($difference['positive'] ? 'positive' : 'negative'); ?>">
                            <?php echo e($difference['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($difference['value']))); ?>

                        </div>
                        <div class="ranking-compare-difference-label">
                            <?php if($difference['positive']): ?>
                                <i class="fas fa-arrow-left"></i> Avantage
                            <?php else: ?>
                                Avantage <i class="fas fa-arrow-right"></i>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="ranking-compare-total-player">
                    <div class="ranking-compare-total-points <?php echo e($user2Stats['total_points'] > $user1Stats['total_points'] ? 'positive' : ''); ?>">
                        <?php echo e(number_format($user2Stats['total_points'])); ?>

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
                            <div class="ranking-compare-category-points <?php echo e($user1Stats['building_points'] > $user2Stats['building_points'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['building_points'])); ?>

                            </div>
                            <div class="ranking-compare-category-percentage">
                                <?php echo e($this->calculatePercentage($user1Stats['building_points'], $user1Stats['total_points'])); ?>% du total
                            </div>
                        </div>
                        
                        <?php
                            $buildingDiff = $this->calculateDifference($user1Stats['building_points'], $user2Stats['building_points']);
                        ?>
                        <div class="ranking-compare-category-difference <?php echo e($buildingDiff['positive'] ? 'positive' : ($buildingDiff['negative'] ? 'negative' : '')); ?>">
                            <?php if(!$buildingDiff['equal']): ?>
                                <?php echo e($buildingDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($buildingDiff['value']))); ?>

                            <?php else: ?>
                                =
                            <?php endif; ?>
                        </div>
                        
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points <?php echo e($user2Stats['building_points'] > $user1Stats['building_points'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['building_points'])); ?>

                            </div>
                            <div class="ranking-compare-category-percentage">
                                <?php echo e($this->calculatePercentage($user2Stats['building_points'], $user2Stats['total_points'])); ?>% du total
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
                            <div class="ranking-compare-category-points <?php echo e($user1Stats['units_points'] > $user2Stats['units_points'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['units_points'])); ?>

                            </div>
                            <div class="ranking-compare-category-percentage">
                                <?php echo e($this->calculatePercentage($user1Stats['units_points'], $user1Stats['total_points'])); ?>% du total
                            </div>
                        </div>
                        
                        <?php
                            $unitsDiff = $this->calculateDifference($user1Stats['units_points'], $user2Stats['units_points']);
                        ?>
                        <div class="ranking-compare-category-difference <?php echo e($unitsDiff['positive'] ? 'positive' : ($unitsDiff['negative'] ? 'negative' : '')); ?>">
                            <?php if(!$unitsDiff['equal']): ?>
                                <?php echo e($unitsDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($unitsDiff['value']))); ?>

                            <?php else: ?>
                                =
                            <?php endif; ?>
                        </div>
                        
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points <?php echo e($user2Stats['units_points'] > $user1Stats['units_points'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['units_points'])); ?>

                            </div>
                            <div class="ranking-compare-category-percentage">
                                <?php echo e($this->calculatePercentage($user2Stats['units_points'], $user2Stats['total_points'])); ?>% du total
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
                            <div class="ranking-compare-category-points <?php echo e($user1Stats['defense_points'] > $user2Stats['defense_points'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['defense_points'])); ?>

                            </div>
                            <div class="ranking-compare-category-percentage">
                                <?php echo e($this->calculatePercentage($user1Stats['defense_points'], $user1Stats['total_points'])); ?>% du total
                            </div>
                        </div>
                        
                        <?php
                            $defenseDiff = $this->calculateDifference($user1Stats['defense_points'], $user2Stats['defense_points']);
                        ?>
                        <div class="ranking-compare-category-difference <?php echo e($defenseDiff['positive'] ? 'positive' : ($defenseDiff['negative'] ? 'negative' : '')); ?>">
                            <?php if(!$defenseDiff['equal']): ?>
                                <?php echo e($defenseDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($defenseDiff['value']))); ?>

                            <?php else: ?>
                                =
                            <?php endif; ?>
                        </div>
                        
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points <?php echo e($user2Stats['defense_points'] > $user1Stats['defense_points'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['defense_points'])); ?>

                            </div>
                            <div class="ranking-compare-category-percentage">
                                <?php echo e($this->calculatePercentage($user2Stats['defense_points'], $user2Stats['total_points'])); ?>% du total
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
                            <div class="ranking-compare-category-points <?php echo e($user1Stats['ship_points'] > $user2Stats['ship_points'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['ship_points'])); ?>

                            </div>
                            <div class="ranking-compare-category-percentage">
                                <?php echo e($this->calculatePercentage($user1Stats['ship_points'], $user1Stats['total_points'])); ?>% du total
                            </div>
                        </div>
                        
                        <?php
                            $shipDiff = $this->calculateDifference($user1Stats['ship_points'], $user2Stats['ship_points']);
                        ?>
                        <div class="ranking-compare-category-difference <?php echo e($shipDiff['positive'] ? 'positive' : ($shipDiff['negative'] ? 'negative' : '')); ?>">
                            <?php if(!$shipDiff['equal']): ?>
                                <?php echo e($shipDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($shipDiff['value']))); ?>

                            <?php else: ?>
                                =
                            <?php endif; ?>
                        </div>
                        
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points <?php echo e($user2Stats['ship_points'] > $user1Stats['ship_points'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['ship_points'])); ?>

                            </div>
                            <div class="ranking-compare-category-percentage">
                                <?php echo e($this->calculatePercentage($user2Stats['ship_points'], $user2Stats['total_points'])); ?>% du total
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
                            <div class="ranking-compare-category-points <?php echo e($user1Stats['technology_points'] > $user2Stats['technology_points'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['technology_points'])); ?>

                            </div>
                            <div class="ranking-compare-category-percentage">
                                <?php echo e($this->calculatePercentage($user1Stats['technology_points'], $user1Stats['total_points'])); ?>% du total
                            </div>
                        </div>
                        
                        <?php
                            $techDiff = $this->calculateDifference($user1Stats['technology_points'], $user2Stats['technology_points']);
                        ?>
                        <div class="ranking-compare-category-difference <?php echo e($techDiff['positive'] ? 'positive' : ($techDiff['negative'] ? 'negative' : '')); ?>">
                            <?php if(!$techDiff['equal']): ?>
                                <?php echo e($techDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($techDiff['value']))); ?>

                            <?php else: ?>
                                =
                            <?php endif; ?>
                        </div>
                        
                        <div class="ranking-compare-category-player">
                            <div class="ranking-compare-category-points <?php echo e($user2Stats['technology_points'] > $user1Stats['technology_points'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['technology_points'])); ?>

                            </div>
                            <div class="ranking-compare-category-percentage">
                                <?php echo e($this->calculatePercentage($user2Stats['technology_points'], $user2Stats['total_points'])); ?>% du total
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
                            <div class="ranking-compare-combat-stat-player <?php echo e($user1Stats['earth_attack'] > $user2Stats['earth_attack'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['earth_attack'])); ?>

                            </div>
                            
                            <?php
                                $attackDiff = $this->calculateDifference($user1Stats['earth_attack'], $user2Stats['earth_attack']);
                            ?>
                            <div class="ranking-compare-combat-stat-difference <?php echo e($attackDiff['positive'] ? 'positive' : ($attackDiff['negative'] ? 'negative' : '')); ?>">
                                <?php if(!$attackDiff['equal']): ?>
                                    <?php echo e($attackDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($attackDiff['value']))); ?>

                                <?php else: ?>
                                    =
                                <?php endif; ?>
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player <?php echo e($user2Stats['earth_attack'] > $user1Stats['earth_attack'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['earth_attack'])); ?>

                            </div>
                        </div>
                    </div>
                    
                    <!-- Points de défense -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-shield-alt"></i> Points de défense
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player <?php echo e($user1Stats['earth_defense'] > $user2Stats['earth_defense'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['earth_defense'])); ?>

                            </div>
                            
                            <?php
                                $defenseDiff = $this->calculateDifference($user1Stats['earth_defense'], $user2Stats['earth_defense']);
                            ?>
                            <div class="ranking-compare-combat-stat-difference <?php echo e($defenseDiff['positive'] ? 'positive' : ($defenseDiff['negative'] ? 'negative' : '')); ?>">
                                <?php if(!$defenseDiff['equal']): ?>
                                    <?php echo e($defenseDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($defenseDiff['value']))); ?>

                                <?php else: ?>
                                    =
                                <?php endif; ?>
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player <?php echo e($user2Stats['earth_defense'] > $user1Stats['earth_defense'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['earth_defense'])); ?>

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
                            <div class="ranking-compare-combat-stat-player <?php echo e($user1Stats['earth_attack_count'] > $user2Stats['earth_attack_count'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['earth_attack_count'])); ?>

                            </div>
                            
                            <?php
                                $attackCountDiff = $this->calculateDifference($user1Stats['earth_attack_count'], $user2Stats['earth_attack_count']);
                            ?>
                            <div class="ranking-compare-combat-stat-difference <?php echo e($attackCountDiff['positive'] ? 'positive' : ($attackCountDiff['negative'] ? 'negative' : '')); ?>">
                                <?php if(!$attackCountDiff['equal']): ?>
                                    <?php echo e($attackCountDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($attackCountDiff['value']))); ?>

                                <?php else: ?>
                                    =
                                <?php endif; ?>
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player <?php echo e($user2Stats['earth_attack_count'] > $user1Stats['earth_attack_count'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['earth_attack_count'])); ?>

                            </div>
                        </div>
                    </div>
                    
                    <!-- Victoires en défense -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-medal"></i> Victoires en défense
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player <?php echo e($user1Stats['earth_defense_count'] > $user2Stats['earth_defense_count'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['earth_defense_count'])); ?>

                            </div>
                            
                            <?php
                                $defenseCountDiff = $this->calculateDifference($user1Stats['earth_defense_count'], $user2Stats['earth_defense_count']);
                            ?>
                            <div class="ranking-compare-combat-stat-difference <?php echo e($defenseCountDiff['positive'] ? 'positive' : ($defenseCountDiff['negative'] ? 'negative' : '')); ?>">
                                <?php if(!$defenseCountDiff['equal']): ?>
                                    <?php echo e($defenseCountDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($defenseCountDiff['value']))); ?>

                                <?php else: ?>
                                    =
                                <?php endif; ?>
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player <?php echo e($user2Stats['earth_defense_count'] > $user1Stats['earth_defense_count'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['earth_defense_count'])); ?>

                            </div>
                        </div>
                    </div>
                    
                    <!-- Défaites -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-skull"></i> Défaites
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player <?php echo e($user1Stats['earth_loser_count'] < $user2Stats['earth_loser_count'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['earth_loser_count'])); ?>

                            </div>
                            
                            <?php
                                // Inverse la différence car moins de défaites est mieux
                                $loserCountDiff = $this->calculateDifference($user2Stats['earth_loser_count'], $user1Stats['earth_loser_count']);
                            ?>
                            <div class="ranking-compare-combat-stat-difference <?php echo e($loserCountDiff['positive'] ? 'positive' : ($loserCountDiff['negative'] ? 'negative' : '')); ?>">
                                <?php if(!$loserCountDiff['equal']): ?>
                                    <?php echo e($loserCountDiff['positive'] ? '-' : '+'); ?> <?php echo e(number_format(abs($loserCountDiff['value']))); ?>

                                <?php else: ?>
                                    =
                                <?php endif; ?>
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player <?php echo e($user2Stats['earth_loser_count'] < $user1Stats['earth_loser_count'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['earth_loser_count'])); ?>

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
                            <div class="ranking-compare-combat-stat-player <?php echo e($user1Stats['spatial_attack'] > $user2Stats['spatial_attack'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['spatial_attack'])); ?>

                            </div>
                            
                            <?php
                                $spaceAttackDiff = $this->calculateDifference($user1Stats['spatial_attack'], $user2Stats['spatial_attack']);
                            ?>
                            <div class="ranking-compare-combat-stat-difference <?php echo e($spaceAttackDiff['positive'] ? 'positive' : ($spaceAttackDiff['negative'] ? 'negative' : '')); ?>">
                                <?php if(!$spaceAttackDiff['equal']): ?>
                                    <?php echo e($spaceAttackDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($spaceAttackDiff['value']))); ?>

                                <?php else: ?>
                                    =
                                <?php endif; ?>
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player <?php echo e($user2Stats['spatial_attack'] > $user1Stats['spatial_attack'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['spatial_attack'])); ?>

                            </div>
                        </div>
                    </div>
                    
                    <!-- Points de défense -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-shield-alt"></i> Points de défense
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player <?php echo e($user1Stats['spatial_defense'] > $user2Stats['spatial_defense'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['spatial_defense'])); ?>

                            </div>
                            
                            <?php
                                $spaceDefenseDiff = $this->calculateDifference($user1Stats['spatial_defense'], $user2Stats['spatial_defense']);
                            ?>
                            <div class="ranking-compare-combat-stat-difference <?php echo e($spaceDefenseDiff['positive'] ? 'positive' : ($spaceDefenseDiff['negative'] ? 'negative' : '')); ?>">
                                <?php if(!$spaceDefenseDiff['equal']): ?>
                                    <?php echo e($spaceDefenseDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($spaceDefenseDiff['value']))); ?>

                                <?php else: ?>
                                    =
                                <?php endif; ?>
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player <?php echo e($user2Stats['spatial_defense'] > $user1Stats['spatial_defense'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['spatial_defense'])); ?>

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
                            <div class="ranking-compare-combat-stat-player <?php echo e($user1Stats['spatial_attack_count'] > $user2Stats['spatial_attack_count'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['spatial_attack_count'])); ?>

                            </div>
                            
                            <?php
                                $spaceAttackCountDiff = $this->calculateDifference($user1Stats['spatial_attack_count'], $user2Stats['spatial_attack_count']);
                            ?>
                            <div class="ranking-compare-combat-stat-difference <?php echo e($spaceAttackCountDiff['positive'] ? 'positive' : ($spaceAttackCountDiff['negative'] ? 'negative' : '')); ?>">
                                <?php if(!$spaceAttackCountDiff['equal']): ?>
                                    <?php echo e($spaceAttackCountDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($spaceAttackCountDiff['value']))); ?>

                                <?php else: ?>
                                    =
                                <?php endif; ?>
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player <?php echo e($user2Stats['spatial_attack_count'] > $user1Stats['spatial_attack_count'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['spatial_attack_count'])); ?>

                            </div>
                        </div>
                    </div>
                    
                    <!-- Victoires en défense -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-medal"></i> Victoires en défense
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player <?php echo e($user1Stats['spatial_defense_count'] > $user2Stats['spatial_defense_count'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['spatial_defense_count'])); ?>

                            </div>
                            
                            <?php
                                $spaceDefenseCountDiff = $this->calculateDifference($user1Stats['spatial_defense_count'], $user2Stats['spatial_defense_count']);
                            ?>
                            <div class="ranking-compare-combat-stat-difference <?php echo e($spaceDefenseCountDiff['positive'] ? 'positive' : ($spaceDefenseCountDiff['negative'] ? 'negative' : '')); ?>">
                                <?php if(!$spaceDefenseCountDiff['equal']): ?>
                                    <?php echo e($spaceDefenseCountDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($spaceDefenseCountDiff['value']))); ?>

                                <?php else: ?>
                                    =
                                <?php endif; ?>
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player <?php echo e($user2Stats['spatial_defense_count'] > $user1Stats['spatial_defense_count'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['spatial_defense_count'])); ?>

                            </div>
                        </div>
                    </div>
                    
                    <!-- Défaites -->
                    <div class="ranking-compare-combat-stat">
                        <div class="ranking-compare-combat-stat-label">
                            <i class="fas fa-skull"></i> Défaites
                        </div>
                        <div class="ranking-compare-combat-stat-values">
                            <div class="ranking-compare-combat-stat-player <?php echo e($user1Stats['spatial_loser_count'] < $user2Stats['spatial_loser_count'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user1Stats['spatial_loser_count'])); ?>

                            </div>
                            
                            <?php
                                // Inverse la différence car moins de défaites est mieux
                                $spaceLoserCountDiff = $this->calculateDifference($user2Stats['spatial_loser_count'], $user1Stats['spatial_loser_count']);
                            ?>
                            <div class="ranking-compare-combat-stat-difference <?php echo e($spaceLoserCountDiff['positive'] ? 'positive' : ($spaceLoserCountDiff['negative'] ? 'negative' : '')); ?>">
                                <?php if(!$spaceLoserCountDiff['equal']): ?>
                                    <?php echo e($spaceLoserCountDiff['positive'] ? '-' : '+'); ?> <?php echo e(number_format(abs($spaceLoserCountDiff['value']))); ?>

                                <?php else: ?>
                                    =
                                <?php endif; ?>
                            </div>
                            
                            <div class="ranking-compare-combat-stat-player <?php echo e($user2Stats['spatial_loser_count'] < $user1Stats['spatial_loser_count'] ? 'highlight' : ''); ?>">
                                <?php echo e(number_format($user2Stats['spatial_loser_count'])); ?>

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
                        <div class="ranking-compare-info-stat-player <?php echo e($user1Stats['planets_count'] > $user2Stats['planets_count'] ? 'highlight' : ''); ?>">
                            <?php echo e(number_format($user1Stats['planets_count'])); ?>

                        </div>
                        
                        <?php
                            $planetsCountDiff = $this->calculateDifference($user1Stats['planets_count'], $user2Stats['planets_count']);
                        ?>
                        <div class="ranking-compare-info-stat-difference <?php echo e($planetsCountDiff['positive'] ? 'positive' : ($planetsCountDiff['negative'] ? 'negative' : '')); ?>">
                            <?php if(!$planetsCountDiff['equal']): ?>
                                <?php echo e($planetsCountDiff['positive'] ? '+' : '-'); ?> <?php echo e(number_format(abs($planetsCountDiff['value']))); ?>

                            <?php else: ?>
                                =
                            <?php endif; ?>
                        </div>
                        
                        <div class="ranking-compare-info-stat-player <?php echo e($user2Stats['planets_count'] > $user1Stats['planets_count'] ? 'highlight' : ''); ?>">
                            <?php echo e(number_format($user2Stats['planets_count'])); ?>

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
                            <?php echo e($user1Stats['created_at']->format('d/m/Y')); ?>

                        </div>
                        
                        <div class="ranking-compare-info-stat-difference">
                            <?php
                                $days = $user1Stats['created_at']->diffInDays($user2Stats['created_at'], false);
                            ?>
                            <?php if($days > 0): ?>
                                <span class="positive">+<?php echo e($days); ?> jours</span>
                            <?php elseif($days < 0): ?>
                                <span class="negative"><?php echo e($days); ?> jours</span>
                            <?php else: ?>
                                <span>=</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="ranking-compare-info-stat-player">
                            <?php echo e($user2Stats['created_at']->format('d/m/Y')); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="ranking-compare-empty">
            <div class="ranking-compare-empty-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="ranking-compare-empty-message">Impossible de charger les données des joueurs</div>
            <div class="ranking-compare-empty-description">Veuillez réessayer ultérieurement.</div>
        </div>
    <?php endif; ?>
</div><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/livewire/game/modal/ranking-compare.blade.php ENDPATH**/ ?>