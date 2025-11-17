<?php

namespace App\Services;

use App\Models\Planet\Planet;
use App\Models\Planet\PlanetUnit;
use App\Models\Planet\PlanetResource;
use App\Models\Planet\PlanetShip;
use App\Models\Planet\PlanetDefense;
use App\Models\Planet\PlanetMission;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplateBuildAdvantage;
use App\Models\Template\TemplateBuildCost;
use App\Models\Template\TemplateResource;
use App\Models\User\UserStat;
use App\Models\Player\PlayerAttackLog;
use App\Models\Server\ServerConfig;
use App\Services\PrivateMessageService;
use App\Services\UserCustomizationService;
use Illuminate\Support\Facades\Log;

class AttackService
{
    protected $privateMessageService;

    public function __construct(PrivateMessageService $privateMessageService)
    {
        $this->privateMessageService = $privateMessageService;
    }

    /**
     * Check if attacker can attack defender based on daily limit
     */
    public function canAttackPlayer(int $attackerUserId, int $defenderUserId): array
    {
        // Check if daily attack limit is enabled
        if (!ServerConfig::isDailyAttackLimitEnabled()) {
            return ['can_attack' => true, 'remaining_attacks' => null, 'message' => null];
        }

        $maxAttacksPerDay = ServerConfig::getDailyAttackLimitPerPlayer();
        $remainingAttacks = PlayerAttackLog::getRemainingAttacks($attackerUserId, $defenderUserId, $maxAttacksPerDay);
        
        if ($remainingAttacks <= 0) {
            return [
                'can_attack' => false,
                'remaining_attacks' => 0,
                'message' => "Vous avez atteint la limite quotidienne d'attaques ({$maxAttacksPerDay}) contre ce joueur. Réessayez demain."
            ];
        }

        return [
            'can_attack' => true,
            'remaining_attacks' => $remainingAttacks,
            'message' => $remainingAttacks === 1 
                ? "Dernière attaque possible contre ce joueur aujourd'hui."
                : "Il vous reste {$remainingAttacks} attaques contre ce joueur aujourd'hui."
        ];
    }

    /**
     * Execute ground combat between attacker and defender
     */
    public function executeGroundCombat($attackerPlanetId, $defenderPlanetId, $attackerUnits)
    {
        // Get planets
        $attackerPlanet = Planet::find($attackerPlanetId);
        $defenderPlanet = Planet::find($defenderPlanetId);

        if (!$attackerPlanet || !$defenderPlanet) {
            throw new \Exception('Planète introuvable');
        }

        // Get defender units
        $defenderUnits = $this->getDefenderUnits($defenderPlanetId);

        // Get planet resources
        $defenderResources = $this->getDefenderResources($defenderPlanetId);

        // Calculate combat powers with bonuses
        $attackerPower = $this->calculateCombatPower($attackerPlanetId, $attackerUnits, 'attack');
        $defenderPower = $this->calculateCombatPower($defenderPlanetId, $defenderUnits, 'defense');

        // Execute combat rounds
        $combatResult = $this->executeCombatRounds($attackerUnits, $defenderUnits, $attackerPower, $defenderPower, $attackerPlanetId, $defenderPlanetId);

        // Update defender units with losses
        $this->updateDefenderUnits($defenderPlanetId, $defenderUnits, $combatResult['surviving_defender_units']);

        // Handle resource pillaging if attacker wins
        $pillagedResources = [];
        if ($combatResult['winner'] === 'attacker' && !empty($combatResult['surviving_attacker_units'])) {
            $pillagedResources = $this->pillageResources($defenderPlanetId, $combatResult['surviving_attacker_units'], $attackerPlanetId);
        }

        // Generate combat report
        $reportHtml = $this->generateCombatReport([
            'attacker_planet' => $attackerPlanet,
            'defender_planet' => $defenderPlanet,
            'initial_attacker_units' => $attackerUnits,
            'initial_defender_units' => $defenderUnits,
            'combat_result' => $combatResult,
            'pillaged_resources' => $pillagedResources,
            'attacker_power' => $attackerPower,
            'defender_power' => $defenderPower
        ]);
        
        // Log the attack to obtain a shareable access key
        $attackLog = $this->logAttack([
            'attacker_user_id' => $attackerPlanet->user_id,
            'defender_user_id' => $defenderPlanet->user_id,
            'attacker_planet_id' => $attackerPlanetId,
            'defender_planet_id' => $defenderPlanetId,
            'attack_type' => 'earth',
            'attacker_units' => $attackerUnits,
            'combat_result' => $combatResult,
            'attacker_won' => $combatResult['winner'] === 'attacker',
            'points_gained' => $this->calculatePointsFromCombat($combatResult, $attackerPlanet->user_id),
            'resources_pillaged' => $pillagedResources,
            'report_data' => $this->buildReportData('earth', [
                'attacker_planet' => $attackerPlanet,
                'defender_planet' => $defenderPlanet,
                'initial_attacker_units' => $attackerUnits,
                'initial_defender_units' => $defenderUnits,
                'combat_result' => $combatResult,
                'pillaged_resources' => $pillagedResources,
                'attacker_power' => $attackerPower,
                'defender_power' => $defenderPower,
            ]),
            'attacked_at' => now(),
        ]);

        // Build shareable URL for the report
        $shareUrl = $attackLog ? route('game.rapport', ['key' => $attackLog->access_key]) : url('/rapport/no-key');

        // Send combat report to both players with share link (terrestrial title)
        $this->sendCombatReports($attackerPlanet->user, $defenderPlanet->user, $reportHtml, $shareUrl, 'Rapport de combat terrestre');

        // Record event stats (attack points & pillage) for attacker
        $pointsGained = $this->calculatePointsFromCombat($combatResult, $attackerPlanet->user_id);
        if ($pointsGained > 0 && $combatResult['winner'] === 'attacker') {
            app(\App\Services\EventService::class)->recordAttackPoints($attackerPlanet->user_id, (int) $pointsGained);
        }
        if (!empty($pillagedResources)) {
            $pillageAmount = (int) array_sum(array_map('intval', array_values($pillagedResources)));
            if ($pillageAmount > 0) {
                app(\App\Services\EventService::class)->recordPillage($attackerPlanet->user_id, $pillageAmount);
            }
        }

        // Award combat points to both players
        $this->awardCombatPoints($attackerPlanet->user_id, $defenderPlanet->user_id, $combatResult, $attackerPower, $defenderPower);

        return $combatResult;
    }

    /**
     * Execute spatial combat between attacker ships and defender ships/defenses
     */
    public function executeSpatialCombat(PlanetMission $mission)
    {
        // Get planets
        $attackerPlanet = $mission->fromPlanet;
        $defenderPlanet = Planet::find($mission->to_planet_id);

        if (!$attackerPlanet || !$defenderPlanet) {
            throw new \Exception('Planète introuvable');
        }

        // Get attacker ships from mission (normalize payload format)
        $attackerShips = $this->normalizeUnitsPayload($mission->ships ?? []);
        
        // Get defender ships and defenses
        $defenderShips = $this->getDefenderShips($mission->to_planet_id);
        $defenderDefenses = $this->getDefenderDefenses($mission->to_planet_id);
        
        // Combine defender units (ships + defenses)
        $defenderUnits = array_merge($defenderShips, $defenderDefenses);

        // Calculate combat powers with bonuses
        $attackerPower = $this->calculateSpatialCombatPower($attackerPlanet->id, $attackerShips, 'attack');
        $defenderPower = $this->calculateSpatialCombatPower($defenderPlanet->id, $defenderUnits, 'defense');

        // Execute combat rounds
        $combatResult = $this->executeSpatialCombatRounds($attackerShips, $defenderUnits, $attackerPower, $defenderPower, $attackerPlanet->id, $defenderPlanet->id);

        // Update defender ships and defenses with losses
        $this->updateDefenderShipsAndDefenses($mission->to_planet_id, $defenderShips, $defenderDefenses, $combatResult);

        // Handle resource pillaging if attacker wins
        $pillagedResources = [];
        if ($combatResult['winner'] === 'attacker' && !empty($combatResult['surviving_attacker_units'])) {
            // Carry pillaged resources in cargo; deposit happens on mission return
            $pillagedResources = $this->pillageResources(
                $mission->to_planet_id,
                $combatResult['surviving_attacker_units'],
                $attackerPlanet->id,
                false
            );
        }

        // Generate combat report
        $reportHtml = $this->generateSpatialCombatReport([
            'attacker_planet' => $attackerPlanet,
            'defender_planet' => $defenderPlanet,
            'initial_attacker_ships' => $attackerShips,
            'initial_defender_ships' => $defenderShips,
            'initial_defender_defenses' => $defenderDefenses,
            'combat_result' => $combatResult,
            'pillaged_resources' => $pillagedResources,
            'attacker_power' => $attackerPower,
            'defender_power' => $defenderPower
        ]);
        
        // Log the attack to obtain a shareable access key
        $attackLog = $this->logAttack([
            'attacker_user_id' => $attackerPlanet->user_id,
            'defender_user_id' => $defenderPlanet->user_id,
            'attacker_planet_id' => $attackerPlanet->id,
            'defender_planet_id' => $mission->to_planet_id,
            'attack_type' => 'spatial',
            'attacker_units' => $attackerShips,
            'combat_result' => $combatResult,
            'attacker_won' => $combatResult['winner'] === 'attacker',
            'points_gained' => $this->calculatePointsFromCombat($combatResult, $attackerPlanet->user_id),
            'resources_pillaged' => $pillagedResources,
            'report_data' => $this->buildReportData('spatial', [
                'attacker_planet' => $attackerPlanet,
                'defender_planet' => $defenderPlanet,
                'initial_attacker_units' => $attackerShips,
                'initial_defender_units' => $defenderUnits,
                'initial_defender_ships' => $defenderShips,
                'initial_defender_defenses' => $defenderDefenses,
                'combat_result' => $combatResult,
                'pillaged_resources' => $pillagedResources,
                'attacker_power' => $attackerPower,
                'defender_power' => $defenderPower,
            ]),
            'attacked_at' => now(),
        ]);

        // Build shareable URL for the report
        $shareUrl = $attackLog ? route('game.rapport', ['key' => $attackLog->access_key]) : url('/rapport/no-key');

        // Send combat report to both players with share link (spatial title)
        $this->sendCombatReports($attackerPlanet->user, $defenderPlanet->user, $reportHtml, $shareUrl, 'Rapport de combat spatial');

        // Record event stats (attack points & pillage) for attacker
        $pointsGained = $this->calculatePointsFromCombat($combatResult, $attackerPlanet->user_id);
        if ($pointsGained > 0 && $combatResult['winner'] === 'attacker') {
            app(\App\Services\EventService::class)->recordAttackPoints($attackerPlanet->user_id, (int) $pointsGained);
        }
        if (!empty($pillagedResources)) {
            $pillageAmount = (int) array_sum(array_map('intval', array_values($pillagedResources)));
            if ($pillageAmount > 0) {
                app(\App\Services\EventService::class)->recordPillage($attackerPlanet->user_id, $pillageAmount);
            }
        }

        // Award combat points to both players
        $this->awardCombatPoints($attackerPlanet->user_id, $defenderPlanet->user_id, $combatResult, $attackerPower, $defenderPower);

        // Update mission with result
        $mission->update([
            'status' => 'returning',
            'return_time' => \Carbon\Carbon::now()->addMinutes(
                PlanetMission::calculateMissionDuration(
                    $mission->to_system,
                    $mission->fromPlanet->system,
                    $this->getShipSpeedFromMission($mission),
                    $mission->user_id
                )
            ),
            'result' => [
                'success' => true,
                'message' => 'Attaque spatiale terminée',
                'combat_result' => $combatResult,
                'pillaged_resources' => $pillagedResources
            ],
            // Store pillaged resources on the mission to be deposited upon return
            'resources' => !empty($pillagedResources) ? $pillagedResources : null
        ]);

        return $combatResult;
    }

    /**
     * Get defender units from planet
     */
    protected function getDefenderUnits($planetId)
    {
        return PlanetUnit::where('planet_id', $planetId)
            ->where('quantity', '>', 0)
            ->with('unit')
            ->get()
            ->mapWithKeys(function ($unit) {
                return [$unit->unit_id => $unit->quantity];
            })
            ->toArray();
    }

    /**
     * Get defender resources from planet
     */
    protected function getDefenderResources($planetId)
    {
        return PlanetResource::where('planet_id', $planetId)
            ->get()
            ->mapWithKeys(function ($resource) {
                return [$resource->resource_id => $resource->current_amount];
            })
            ->toArray();
    }

    /**
     * Calculate total combat power with bonuses
     */
    protected function calculateCombatPower($planetId, $units, $type = 'attack')
    {
        $totalPower = 0;
        $bonusMethod = $type === 'attack' ? 'getAttackBonus' : 'getDefenseBonus';
        $powerField = $type === 'attack' ? 'attack_power' : 'defense_power';
        
        // Get bonus from buildings and technologies
        $bonus = TemplateBuildAdvantage::$bonusMethod($planetId);
        
        // Get planet to access user
        $planet = \App\Models\Planet\Planet::find($planetId);
        
        // Apply faction bonus if available
        if ($planet && $planet->user && $planet->user->faction) {
            // Get faction bonus based on combat type
            if ($type === 'attack') {
                $factionBonus = $planet->user->faction->getBonusAttackPower();
            } else { // defense
                $factionBonus = $planet->user->faction->getBonusDefensePower(); 
            }
            
            // Add faction bonus to existing bonus
            $bonus += $factionBonus;
        }
        
        foreach ($units as $unitId => $quantity) {
            $unit = TemplateBuild::find($unitId);
            if ($unit && $quantity > 0) {
                $basePower = $unit->$powerField ?? 0;
                $unitPower = $basePower * (1 + $bonus / 100);
                $totalPower += $unitPower * $quantity;
            }
        }
        
        return $totalPower;
    }

    /**
     * Execute combat rounds
     */
    protected function executeCombatRounds($attackerUnits, $defenderUnits, $attackerPower, $defenderPower, $attackerPlanetId, $defenderPlanetId)
    {
        $rounds = [];
        $currentAttackerUnits = $attackerUnits;
        $currentDefenderUnits = $defenderUnits;
        $roundNumber = 1;
        $maxRounds = 10; // Limite de sécurité

        while ($roundNumber <= $maxRounds && !empty($currentAttackerUnits) && !empty($currentDefenderUnits)) {
            $roundResult = $this->executeRound(
                $currentAttackerUnits, 
                $currentDefenderUnits, 
                $attackerPlanetId, 
                $defenderPlanetId,
                $roundNumber
            );
            
            $rounds[] = $roundResult;
            $currentAttackerUnits = $roundResult['remaining_attacker_units'];
            $currentDefenderUnits = $roundResult['remaining_defender_units'];
            
            $roundNumber++;
        }

        // Determine winner
        $winner = 'draw';
        if (empty($currentDefenderUnits) && !empty($currentAttackerUnits)) {
            $winner = 'attacker';
        } elseif (empty($currentAttackerUnits) && !empty($currentDefenderUnits)) {
            $winner = 'defender';
        }

        return [
            'winner' => $winner,
            'rounds' => $rounds,
            'surviving_attacker_units' => $currentAttackerUnits,
            'surviving_defender_units' => $currentDefenderUnits,
            'total_rounds' => count($rounds)
        ];
    }

    /**
     * Execute a single combat round
     */
    protected function executeRound($attackerUnits, $defenderUnits, $attackerPlanetId, $defenderPlanetId, $roundNumber)
    {
        // Calculate current powers
        $attackerPower = $this->calculateCombatPower($attackerPlanetId, $attackerUnits, 'attack');
        $defenderPower = $this->calculateCombatPower($defenderPlanetId, $defenderUnits, 'defense');
        
        // Calculate shield bonuses from buildings and technologies
        $attackerShieldBonus = TemplateBuildAdvantage::getShieldBonus($attackerPlanetId);
        $defenderShieldBonus = TemplateBuildAdvantage::getShieldBonus($defenderPlanetId);
        
        // Calculate total shield power for each side
        $attackerTotalShield = $this->calculateTotalShieldPower($attackerPlanetId, $attackerUnits) + $attackerShieldBonus;
        $defenderTotalShield = $this->calculateTotalShieldPower($defenderPlanetId, $defenderUnits) + $defenderShieldBonus;
        
        // Calculate effective damage after shields
        $rawDamageToDefender = $attackerPower;
        $rawDamageToAttacker = $defenderPower;
        
        // Shield absorption (shields reduce damage by their percentage)
        $shieldAbsorptionDefender = min(95, $defenderTotalShield); // Cap at 95% absorption
        $shieldAbsorptionAttacker = min(95, $attackerTotalShield); // Cap at 95% absorption
        
        $damageToDefender = max(0, $rawDamageToDefender * (1 - $shieldAbsorptionDefender / 100));
        $damageToAttacker = max(0, $rawDamageToAttacker * (1 - $shieldAbsorptionAttacker / 100));
        
        // Calculate losses based on damage vs total life points
        $attackerLosses = $this->calculateLifeBasedLosses($attackerUnits, $damageToAttacker, $attackerPlanetId);
        $defenderLosses = $this->calculateLifeBasedLosses($defenderUnits, $damageToDefender, $defenderPlanetId);
        
        return [
            'round' => $roundNumber,
            'attacker_power' => $attackerPower,
            'defender_power' => $defenderPower,
            'attacker_shield' => $attackerTotalShield,
            'defender_shield' => $defenderTotalShield,
            'raw_damage_to_defender' => $rawDamageToDefender,
            'raw_damage_to_attacker' => $rawDamageToAttacker,
            'damage_to_defender' => $damageToDefender,
            'damage_to_attacker' => $damageToAttacker,
            'attacker_losses' => $attackerLosses,
            'defender_losses' => $defenderLosses,
            'remaining_attacker_units' => $this->subtractUnits($attackerUnits, $attackerLosses),
            'remaining_defender_units' => $this->subtractUnits($defenderUnits, $defenderLosses)
        ];
    }

    /**
     * Calculate total shield power for units
     */
    protected function calculateTotalShieldPower($planetId, $units)
    {
        $totalShield = 0;
        
        foreach ($units as $unitId => $quantity) {
            $unit = TemplateBuild::find($unitId);
            if ($unit && $quantity > 0) {
                $totalShield += ($unit->shield_power ?? 0) * $quantity;
            }
        }
        
        return $totalShield;
    }
    
    /**
     * Calculate losses based on damage vs life points
     */
    protected function calculateLifeBasedLosses($units, $totalDamage, $planetId)
    {
        $losses = [];
        $remainingDamage = $totalDamage;
        
        // Sort units by life (weakest first for realistic combat)
        $sortedUnits = [];
        foreach ($units as $unitId => $quantity) {
            $unit = TemplateBuild::find($unitId);
            if ($unit && $quantity > 0) {
                $sortedUnits[] = [
                    'id' => $unitId,
                    'quantity' => $quantity,
                    // Clamp life to at least 1 to avoid division by zero
                    'life' => max(1, (int) ($unit->life ?? 1)),
                    'unit' => $unit
                ];
            }
        }
        
        // Sort by life points (ascending)
        usort($sortedUnits, function($a, $b) {
            return $a['life'] <=> $b['life'];
        });
        
        // Apply damage to units
        foreach ($sortedUnits as $unitData) {
            if ($remainingDamage <= 0) break;
            
            $unitLife = $unitData['life'];
            // Safety guard in case life is invalid
            if ($unitLife <= 0) {
                continue;
            }
            $maxKillable = (int) floor($remainingDamage / $unitLife);
            $actualKilled = min($maxKillable, $unitData['quantity']);
            
            if ($actualKilled > 0) {
                $losses[$unitData['id']] = $actualKilled;
                $remainingDamage -= $actualKilled * $unitLife;
            }
        }
        
        return $losses;
    }
    
    /**
     * Apply losses to units based on loss rate (legacy method for compatibility)
     */
    protected function applyLosses($units, $lossRate)
    {
        $losses = [];
        foreach ($units as $unitId => $quantity) {
            $lost = (int) ceil($quantity * $lossRate);
            if ($lost > 0) {
                $losses[$unitId] = min($lost, $quantity);
            }
        }
        return $losses;
    }

    /**
     * Subtract losses from units
     */
    protected function subtractUnits($units, $losses)
    {
        $remaining = $units;
        foreach ($losses as $unitId => $lossQuantity) {
            if (isset($remaining[$unitId])) {
                $remaining[$unitId] = max(0, $remaining[$unitId] - $lossQuantity);
                if ($remaining[$unitId] === 0) {
                    unset($remaining[$unitId]);
                }
            }
        }
        return $remaining;
    }

    /**
     * Pillage resources based on surviving units cargo capacity with storage protection
     */
    protected function pillageResources($defenderPlanetId, $survivingUnits, $attackerPlanetId, bool $immediateDeposit = true)
    {
        $totalCargoCapacity = 0;
        
        // Calculate total cargo capacity
        foreach ($survivingUnits as $unitId => $quantity) {
            if ($quantity > 0) {
                $capacity = $this->getCargoCapacityForTemplate($unitId, $attackerPlanetId);
                $totalCargoCapacity += $capacity * (int) $quantity;
            }
        }
        
        if ($totalCargoCapacity <= 0) {
            return [];
        }
        
        // Get available resources limited to basic or tradeable types
        $availableResources = PlanetResource::where('planet_id', $defenderPlanetId)
            ->where('current_amount', '>', 0)
            ->whereHas('resource', function ($q) {
                $q->where('type', 'basic')->orWhere('is_tradeable', true);
            })
            ->get();
        
        $pillagedResources = [];
        $remainingCapacity = $totalCargoCapacity;
        
        foreach ($availableResources as $resource) {
            if ($remainingCapacity <= 0) break;
            
            // Calculate storage protection based on storage bonus
            $storageBonus = TemplateBuildAdvantage::getStorageBonus($defenderPlanetId, $resource->resource_id);
            
            // Enhanced protection: 10% base + (storage bonus / 50), capped at 50%
            $protectionPercentage = min(50, 10 + ($storageBonus / 50));
            
            // Calculate protected amount
            $protectedAmount = (int) ceil($resource->current_amount * ($protectionPercentage / 100));
            
            // Available amount for pillaging (total - protected)
            $availableForPillage = max(0, $resource->current_amount - $protectedAmount);
            
            // Limit pillaging to maximum 30% of total cargo capacity per resource type
            $maxPillagePerResource = (int) ceil($totalCargoCapacity * 0.3);
            $toPillage = min($availableForPillage, $remainingCapacity, $maxPillagePerResource);
            
            if ($toPillage > 0) {
                $pillagedResources[$resource->resource_id] = $toPillage;
                
                // Update defender's resources
                $resource->current_amount -= $toPillage;
                $resource->save();
                
                // Optionally deposit immediately to attacker (ground combat)
                if ($immediateDeposit) {
                    $this->addResourcesToAttacker($attackerPlanetId, $resource->resource_id, $toPillage);
                }
                
                $remainingCapacity -= $toPillage;
            }
        }
        
        return $pillagedResources;
    }

    /**
     * Get cargo capacity for a template unit/ship with faction bonus applied
     */
    protected function getCargoCapacityForTemplate(int $templateId, int $planetId): int
    {
        $template = TemplateBuild::find($templateId);
        if (!$template) {
            return 0;
        }
        $baseCapacity = (int) ($template->cargo_capacity ?? 0);

        // Apply faction bonus if available
        $planet = \App\Models\Planet\Planet::find($planetId);
        if ($planet && $planet->user && $planet->user->faction) {
            $factionBonus = $planet->user->faction->getBonusShipCapacity();
            if ($factionBonus > 0) {
                $baseCapacity += (int) floor($baseCapacity * ($factionBonus / 100));
            }
        }

        return $baseCapacity;
    }
    
    /**
     * Add pillaged resources to attacker's planet
     */
    protected function addResourcesToAttacker($attackerPlanetId, $resourceId, $amount)
    {
        $attackerResource = PlanetResource::where('planet_id', $attackerPlanetId)
            ->where('resource_id', $resourceId)
            ->first();
            
        if ($attackerResource) {
            // Get storage capacity for this resource
            $templateResource = TemplateResource::find($resourceId);
            $storageCapacity = TemplateBuildAdvantage::getStorageBonus($attackerPlanetId, $resourceId);
            
            // Calculate maximum storage (base storage + bonus)
            $maxStorage = $templateResource->base_storage + $storageCapacity;
            
            // Add resources but don't exceed storage capacity
            $newAmount = min($attackerResource->current_amount + $amount, $maxStorage);
            $attackerResource->current_amount = $newAmount;
            $attackerResource->save();
        }
    }

    /**
     * Generate HTML combat report
     */
    protected function generateCombatReport($data)
    {
        $attackerPlanet = $data['attacker_planet'];
        $defenderPlanet = $data['defender_planet'];
        $combatResult = $data['combat_result'];
        $pillagedResources = $data['pillaged_resources'];
        $initialAttackerUnits = $data['initial_attacker_units'];
        $initialDefenderUnits = $data['initial_defender_units'];
        $attackerPower = $data['attacker_power'];
        $defenderPower = $data['defender_power'];

        // Personnalisation des noms et icônes par joueur
        /** @var UserCustomizationService $customizer */
        $customizer = app(UserCustomizationService::class);
        $resolveAttackerDisplay = function(TemplateBuild $tpl) use ($customizer, $attackerPlanet) {
            $d = $customizer->resolveBuild($attackerPlanet->user, $tpl);
            return [
                'name' => $d['name'] ?? ($tpl->label ?? $tpl->name),
                'icon' => $d['icon_url'] ?? null,
            ];
        };
        $resolveDefenderDisplay = function(TemplateBuild $tpl) use ($customizer, $defenderPlanet) {
            $d = $customizer->resolveBuild($defenderPlanet->user, $tpl);
            return [
                'name' => $d['name'] ?? ($tpl->label ?? $tpl->name),
                'icon' => $d['icon_url'] ?? null,
            ];
        };
        
        $html = '<div class="combat-report">';
        
        // Header
        $html .= '<div class="report-header">';
        $html .= '<h3>🌍 Rapport de Combat Terrestre</h3>';
        $html .= '</div>';
        
        // Combat info
        $html .= '<div class="combat-info">';
        $html .= '<div class="attacker-info">';
        $html .= '<h4>⚔️ Attaquant</h4>';
        $html .= '<p><strong>Planète:</strong> ' . $attackerPlanet->name . '</p>';
        $html .= '<p><strong>Coordonnées:</strong> [' . $attackerPlanet->galaxy . ':' . $attackerPlanet->system . ':' . $attackerPlanet->position . ']</p>';
        $html .= '<p><strong>Joueur:</strong> ' . $attackerPlanet->user->name . '</p>';
        $html .= '<p><strong>Puissance initiale:</strong> ' . number_format($attackerPower) . '</p>';
        $html .= '</div>';
        
        $html .= '<div class="defender-info">';
        $html .= '<h4>🛡️ Défenseur</h4>';
        $html .= '<p><strong>Planète:</strong> ' . $defenderPlanet->name . '</p>';
        $html .= '<p><strong>Coordonnées:</strong> [' . $defenderPlanet->galaxy . ':' . $defenderPlanet->system . ':' . $defenderPlanet->position . ']</p>';
        $html .= '<p><strong>Joueur:</strong> ' . $defenderPlanet->user->name . '</p>';
        $html .= '<p><strong>Puissance initiale:</strong> ' . number_format($defenderPower) . '</p>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Combat result
        $winnerColor = $combatResult['winner'] === 'attacker' ? '#ef4444' : ($combatResult['winner'] === 'defender' ? '#22c55e' : '#f59e0b');
        $winnerText = $combatResult['winner'] === 'attacker' ? '⚔️ Victoire de l\'attaquant' : ($combatResult['winner'] === 'defender' ? '🛡️ Victoire du défenseur' : '⚖️ Match nul');
        
        $html .= '<div class="combat-result">';
        $html .= '<h4 style="color: ' . $winnerColor . ';">🏆 ' . $winnerText . '</h4>';
        $html .= '<p>Combat terminé en ' . $combatResult['total_rounds'] . ' round(s)</p>';
        $html .= '</div>';
        
        // Initial forces
        $html .= '<div class="forces-summary">';
        $html .= '<div class="attacker-forces">';
        $html .= '<h4>Forces de l\'attaquant</h4>';
        $html .= '<ul>';
        foreach ($initialAttackerUnits as $unitId => $quantity) {
            $unit = TemplateBuild::find($unitId);
            if ($unit) {
                $attDisp = $resolveAttackerDisplay($unit);
                $iconPath = $attDisp['icon'] ?? $this->getUnitIconPath($unit);
                $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($attDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($attDisp['name']) . ': ' . number_format($quantity) . '</li>';
            }
        }
        $html .= '</ul>';
        $html .= '</div>';
        
        $html .= '<div class="defender-forces">';
        $html .= '<h4>Forces du défenseur</h4>';
        $html .= '<ul>';
        if (empty($initialDefenderUnits)) {
            $html .= '<li>Aucune unité de défense</li>';
        } else {
            foreach ($initialDefenderUnits as $unitId => $quantity) {
                $unit = TemplateBuild::find($unitId);
                if ($unit) {
                    $defDisp = $resolveDefenderDisplay($unit);
                    $iconPath = $defDisp['icon'] ?? $this->getUnitIconPath($unit);
                    $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($defDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($defDisp['name']) . ': ' . number_format($quantity) . '</li>';
                }
            }
        }
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Rounds details
        if (!empty($combatResult['rounds'])) {
            $html .= '<div class="rounds-details">';
            $html .= '<h4>📊 Détails des Rounds</h4>';
            
            foreach ($combatResult['rounds'] as $round) {
                $html .= '<div class="round">';
                $html .= '<h5>Round ' . $round['round'] . '</h5>';
                $html .= '<div class="round-powers">';
                $html .= '<span class="attacker-power">Puissance Attaque: ' . number_format($round['attacker_power']) . '</span>';
                $html .= '<span class="defender-power">Puissance Défense: ' . number_format($round['defender_power']) . '</span>';
                $html .= '</div>';
                
                // Pertes
                if (!empty($round['attacker_losses']) || !empty($round['defender_losses'])) {
                    $html .= '<div class="round-losses">';
                    $html .= '<h6>Pertes:</h6>';
                    
                    if (!empty($round['attacker_losses'])) {
                        $html .= '<div class="attacker-losses">';
                        $html .= '<p>Attaquant:</p>';
                        $html .= '<ul>';
                        foreach ($round['attacker_losses'] as $unitId => $quantity) {
                            $unit = TemplateBuild::find($unitId);
                            if ($unit) {
                                $attDisp = $resolveAttackerDisplay($unit);
                                $iconPath = $attDisp['icon'] ?? $this->getUnitIconPath($unit);
                                $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($attDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($attDisp['name']) . ': -' . number_format($quantity) . '</li>';
                            }
                        }
                        $html .= '</ul>';
                        $html .= '</div>';
                    }
                    
                    if (!empty($round['defender_losses'])) {
                        $html .= '<div class="defender-losses">';
                        $html .= '<p>Défenseur:</p>';
                        $html .= '<ul>';
                        foreach ($round['defender_losses'] as $unitId => $quantity) {
                            $unit = TemplateBuild::find($unitId);
                            if ($unit) {
                                $defDisp = $resolveDefenderDisplay($unit);
                                $iconPath = $defDisp['icon'] ?? $this->getUnitIconPath($unit);
                                $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($defDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($defDisp['name']) . ': -' . number_format($quantity) . '</li>';
                            }
                        }
                        $html .= '</ul>';
                        $html .= '</div>';
                    }
                    
                    $html .= '</div>';
                }
                
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        
        // Surviving units
        $html .= '<div class="surviving-units">';
        $html .= '<h4>Unités survivantes</h4>';
        
        $html .= '<div class="attacker-survivors">';
        $html .= '<h5>Attaquant:</h5>';
        if (empty($combatResult['surviving_attacker_units'])) {
            $html .= '<p>Aucune unité survivante</p>';
        } else {
            $html .= '<ul>';
            foreach ($combatResult['surviving_attacker_units'] as $unitId => $quantity) {
                $unit = TemplateBuild::find($unitId);
                if ($unit) {
                    $attDisp = $resolveAttackerDisplay($unit);
                    $iconPath = $attDisp['icon'] ?? $this->getUnitIconPath($unit);
                    $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($attDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($attDisp['name']) . ': ' . number_format($quantity) . '</li>';
                }
            }
            $html .= '</ul>';
        }
        $html .= '</div>';
        
        $html .= '<div class="defender-survivors">';
        $html .= '<h5>Défenseur:</h5>';
        if (empty($combatResult['surviving_defender_units'])) {
            $html .= '<p>Aucune unité survivante</p>';
        } else {
            $html .= '<ul>';
            foreach ($combatResult['surviving_defender_units'] as $unitId => $quantity) {
                $unit = TemplateBuild::find($unitId);
                if ($unit) {
                    $defDisp = $resolveDefenderDisplay($unit);
                    $iconPath = $defDisp['icon'] ?? $this->getUnitIconPath($unit);
                    $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($defDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($defDisp['name']) . ': ' . number_format($quantity) . '</li>';
                }
            }
            $html .= '</ul>';
        }
        $html .= '</div>';
        $html .= '</div>';
        
        // Total destroyed units summary
        $html .= '<div class="destroyed-units-summary">';
        $html .= '<h4>💀 Résumé des Pertes Totales</h4>';
        
        // Calculate total losses for both sides
        $totalAttackerLosses = [];
        $totalDefenderLosses = [];
        
        if (!empty($combatResult['rounds'])) {
            foreach ($combatResult['rounds'] as $round) {
                if (!empty($round['attacker_losses'])) {
                    foreach ($round['attacker_losses'] as $unitId => $quantity) {
                        $totalAttackerLosses[$unitId] = ($totalAttackerLosses[$unitId] ?? 0) + $quantity;
                    }
                }
                if (!empty($round['defender_losses'])) {
                    foreach ($round['defender_losses'] as $unitId => $quantity) {
                        $totalDefenderLosses[$unitId] = ($totalDefenderLosses[$unitId] ?? 0) + $quantity;
                    }
                }
            }
        }
        
        $html .= '<div class="total-losses">';
        $html .= '<div class="attacker-total-losses">';
        $html .= '<h5>Pertes de l\'attaquant:</h5>';
        if (empty($totalAttackerLosses)) {
            $html .= '<p>Aucune perte</p>';
        } else {
            $html .= '<ul>';
            foreach ($totalAttackerLosses as $unitId => $quantity) {
                $unit = TemplateBuild::find($unitId);
                if ($unit) {
                    $attDisp = $resolveAttackerDisplay($unit);
                    $iconPath = $attDisp['icon'] ?? $this->getUnitIconPath($unit);
                    $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($attDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($attDisp['name']) . ': -' . number_format($quantity) . '</li>';
                }
            }
            $html .= '</ul>';
        }
        $html .= '</div>';
        
        $html .= '<div class="defender-total-losses">';
        $html .= '<h5>Pertes du défenseur:</h5>';
        if (empty($totalDefenderLosses)) {
            $html .= '<p>Aucune perte</p>';
        } else {
            $html .= '<ul>';
            foreach ($totalDefenderLosses as $unitId => $quantity) {
                $unit = TemplateBuild::find($unitId);
                if ($unit) {
                    $defDisp = $resolveDefenderDisplay($unit);
                    $iconPath = $defDisp['icon'] ?? $this->getUnitIconPath($unit);
                    $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($defDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($defDisp['name']) . ': -' . number_format($quantity) . '</li>';
                }
            }
            $html .= '</ul>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Combat points awarded
        $html .= '<div class="combat-points">';
        $html .= '<h4>⭐ Points de Combat</h4>';
        
        // Calculate points based on losses (same logic as in awardCombatPoints)
        $attackerPoints = 0;
        $defenderPoints = 0;
        
        if ($combatResult['winner'] === 'attacker') {
            $defenderPoints = $this->calculatePointsFromLosses($totalDefenderLosses);
            $html .= '<div class="points-awarded">';
            $html .= '<p><strong>🏆 ' . $attackerPlanet->user->name . ' (Attaquant)</strong> gagne <span style="color: #22c55e; font-weight: bold;">' . number_format($defenderPoints) . ' points</span></p>';
            $html .= '<p>' . $defenderPlanet->user->name . ' (Défenseur) ne gagne aucun point</p>';
            $html .= '</div>';
        } elseif ($combatResult['winner'] === 'defender') {
            $attackerPoints = $this->calculatePointsFromLosses($totalAttackerLosses);
            $html .= '<div class="points-awarded">';
            $html .= '<p><strong>🏆 ' . $defenderPlanet->user->name . ' (Défenseur)</strong> gagne <span style="color: #22c55e; font-weight: bold;">' . number_format($attackerPoints) . ' points</span></p>';
            $html .= '<p>' . $attackerPlanet->user->name . ' (Attaquant) ne gagne aucun point</p>';
            $html .= '</div>';
        } else {
            $html .= '<div class="points-awarded">';
            $html .= '<p>Match nul - Aucun point attribué</p>';
            $html .= '</div>';
        }
        $html .= '</div>';

        // Pillaged resources
        if (!empty($pillagedResources)) {
            $html .= '<div class="pillaged-resources">';
            $html .= '<h4>💰 Ressources Pillées</h4>';
            $html .= '<ul>';
            foreach ($pillagedResources as $resourceId => $quantity) {
                $resource = TemplateResource::find($resourceId);
                $resourceName = $resource ? $resource->display_name : 'Ressource ' . $resourceId;
                $html .= '<li>' . $resourceName . ': ' . number_format($quantity) . '</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Send combat reports to both players
     */
    protected function sendCombatReports($attackerUser, $defenderUser, $reportHtml, string $shareUrl = null, string $title = 'Rapport de combat terrestre')
    {
        // Append shareable link if present
        $messageHtml = $reportHtml;
        if (!empty($shareUrl)) {
            $messageHtml .= '<div class="share-report" style="margin-top:12px; padding-top:8px; border-top:1px solid #e5e7eb;">'
                . '<p>🔗 <strong>Lien de partage du rapport:</strong> '
                . '<a href="' . $shareUrl . '" target="_blank" rel="noopener">Voir le rapport complet</a></p>'
                . '</div>';
        }
        // Send to attacker
        $this->privateMessageService->createSystemMessage(
            $attackerUser,
            'attack',
            $title,
            $messageHtml
        );
        
        // Send to defender
        $this->privateMessageService->createSystemMessage(
            $defenderUser,
            'attack',
            $title,
            $messageHtml
        );
    }

    /**
     * Update defender units after combat
     */
    protected function updateDefenderUnits($defenderPlanetId, $initialUnits, $survivingUnits)
    {
        foreach ($initialUnits as $unitId => $initialQuantity) {
            $survivingQuantity = $survivingUnits[$unitId] ?? 0;
            
            // Find the planet unit record
            $planetUnit = PlanetUnit::where('planet_id', $defenderPlanetId)
                ->where('unit_id', $unitId)
                ->first();
            
            if ($planetUnit) {
                if ($survivingQuantity > 0) {
                    // Update quantity with surviving units
                    $planetUnit->quantity = $survivingQuantity;
                    $planetUnit->save();
                } else {
                    // Delete the record if no units survived
                    $planetUnit->delete();
                }
            }
        }
    }

    /**
     * Award combat points based on losses inflicted
     */
    protected function awardCombatPoints($attackerUserId, $defenderUserId, $combatResult, $attackerPower, $defenderPower)
    {
        $attackerPoints = 0;
        $defenderPoints = 0;
        
        // Calculate total losses for each side across all rounds
        $totalAttackerLosses = [];
        $totalDefenderLosses = [];
        
        foreach ($combatResult['rounds'] as $round) {
            // Accumulate attacker losses
            if (!empty($round['attacker_losses'])) {
                foreach ($round['attacker_losses'] as $unitId => $quantity) {
                    $totalAttackerLosses[$unitId] = ($totalAttackerLosses[$unitId] ?? 0) + $quantity;
                }
            }
            
            // Accumulate defender losses
            if (!empty($round['defender_losses'])) {
                foreach ($round['defender_losses'] as $unitId => $quantity) {
                    $totalDefenderLosses[$unitId] = ($totalDefenderLosses[$unitId] ?? 0) + $quantity;
                }
            }
        }
        
        // Award points based on combat result and losses inflicted
        if ($combatResult['winner'] === 'attacker') {
            // Attacker wins: gets points for defender losses, defender gets nothing
            $attackerPoints = $this->calculatePointsFromLosses($totalDefenderLosses);
            $defenderPoints = 0;
            
            // Award points to attacker
            if ($attackerPoints > 0) {
                $attackerUserStat = UserStat::firstOrCreate(['user_id' => $attackerUserId]);
                $attackerUserStat->addEarthAttackStats($attackerPoints);
            }
            
            // Add loser count to defender
            $defenderUserStat = UserStat::firstOrCreate(['user_id' => $defenderUserId]);
            $defenderUserStat->addEarthLoserCount();
            
        } elseif ($combatResult['winner'] === 'defender') {
            // Defender wins: gets points for attacker losses, attacker gets nothing
            $defenderPoints = $this->calculatePointsFromLosses($totalAttackerLosses);
            $attackerPoints = 0;
            
            // Award points to defender
            if ($defenderPoints > 0) {
                $defenderUserStat = UserStat::firstOrCreate(['user_id' => $defenderUserId]);
                $defenderUserStat->addEarthDefenseStats($defenderPoints);
            }
            
            // Add loser count to attacker
            $attackerUserStat = UserStat::firstOrCreate(['user_id' => $attackerUserId]);
            $attackerUserStat->addEarthLoserCount();
            
        } else {
            // Draw: nobody gets points
            $attackerPoints = 0;
            $defenderPoints = 0;
        }
        
        Log::info('Combat points awarded based on losses', [
            'attacker_user_id' => $attackerUserId,
            'defender_user_id' => $defenderUserId,
            'attacker_points' => $attackerPoints,
            'defender_points' => $defenderPoints,
            'winner' => $combatResult['winner'],
            'attacker_losses' => $totalAttackerLosses,
            'defender_losses' => $totalDefenderLosses
        ]);
    }
    
    /**
      * Calculate points based on losses inflicted (cost of destroyed units)
      */
     protected function calculatePointsFromLosses($losses)
     {
         $totalPoints = 0;
         
         foreach ($losses as $unitId => $quantity) {
             if ($quantity <= 0) continue;
             
             // Get unit costs from TemplateBuildCost
             $unitCosts = TemplateBuildCost::where('build_id', $unitId)
                 ->where('level', 1) // Level 1 cost for units
                 ->get();
             
             $totalUnitCost = 0;
             foreach ($unitCosts as $cost) {
                 $totalUnitCost += $cost->base_cost;
             }
             
             // Calculate points: total cost of destroyed units / 500 = points
             // Formula: (unit_cost * quantity_destroyed) / 500
             $unitPoints = ($totalUnitCost * $quantity) / 500;
             $totalPoints += $unitPoints;
         }
         
         // Round down to integer and ensure minimum of 0
        return max(0, (int) floor($totalPoints));
    }

    /**
     * Log attack for daily limit tracking
     */
    protected function logAttack(array $attackData): ?PlayerAttackLog
    {
        try {
            return PlayerAttackLog::logAttack($attackData);
        } catch (\Exception $e) {
            Log::error('Failed to log attack: ' . $e->getMessage(), $attackData);
            return null;
        }
    }

    /**
     * Construire un snapshot complet du rapport pour stockage (report_data).
     * Ce snapshot fige les métadonnées (noms, icônes, stats) au moment du combat.
     */
    protected function buildReportData(string $type, array $data): array
    {
        $attackerPlanet = $data['attacker_planet'];
        $defenderPlanet = $data['defender_planet'];
        $initialAttacker = (array) ($data['initial_attacker_units'] ?? []);
        $initialDefender = (array) ($data['initial_defender_units'] ?? []);
        $combatResult = (array) ($data['combat_result'] ?? []);
        $pillaged = (array) ($data['pillaged_resources'] ?? []);
        $attackerPower = (float) ($data['attacker_power'] ?? 0);
        $defenderPower = (float) ($data['defender_power'] ?? 0);

        /** @var UserCustomizationService $customizer */
        $customizer = app(UserCustomizationService::class);

        // Cataloguer toutes les unités impliquées avec leurs métadonnées et stats
        $allUnitIds = array_unique(array_merge(array_keys($initialAttacker), array_keys($initialDefender)));
        $unitsCatalog = [];
        foreach ($allUnitIds as $unitId) {
            $tpl = TemplateBuild::find($unitId);
            if ($tpl) {
                // Resolve per-user display (name, icon) overrides
                $attackerDisplay = $customizer->resolveBuild($attackerPlanet->user, $tpl);
                $defenderDisplay = $customizer->resolveBuild($defenderPlanet->user, $tpl);
                $unitsCatalog[$unitId] = [
                    'id' => $tpl->id,
                    'type' => $tpl->type,
                    'name' => $tpl->name,
                    'label' => $tpl->label ?? $tpl->name,
                    'icon' => $tpl->icon,
                    'attacker' => [
                        'name' => $attackerDisplay['name'] ?? ($tpl->label ?? $tpl->name),
                        'icon_url' => $attackerDisplay['icon_url'] ?? null,
                    ],
                    'defender' => [
                        'name' => $defenderDisplay['name'] ?? ($tpl->label ?? $tpl->name),
                        'icon_url' => $defenderDisplay['icon_url'] ?? null,
                    ],
                    'stats' => [
                        'life' => (int) ($tpl->life ?? 0),
                        'attack_power' => (int) ($tpl->attack_power ?? 0),
                        'defense_power' => (int) ($tpl->defense_power ?? 0),
                        'shield_power' => (int) ($tpl->shield_power ?? 0),
                        'speed' => (int) ($tpl->speed ?? 0),
                        'cargo_capacity' => (int) ($tpl->cargo_capacity ?? 0),
                    ],
                ];
            }
        }

        // Décomposer par catégories (utile en spatial)
        $initialDefenderShips = (array) ($data['initial_defender_ships'] ?? []);
        $initialDefenderDefenses = (array) ($data['initial_defender_defenses'] ?? []);

        return [
            'meta' => [
                'type' => $type,
                'generated_at' => now()->toISOString(),
            ],
            'attacker' => [
                'user_id' => $attackerPlanet->user_id,
                'planet_id' => $attackerPlanet->id,
                'planet_name' => $attackerPlanet->name,
                'coords' => [
                    'galaxy' => $attackerPlanet->galaxy,
                    'system' => $attackerPlanet->system,
                    'position' => $attackerPlanet->position,
                ],
                'total_power' => $attackerPower,
                'initial_units' => $initialAttacker,
            ],
            'defender' => [
                'user_id' => $defenderPlanet->user_id,
                'planet_id' => $defenderPlanet->id,
                'planet_name' => $defenderPlanet->name,
                'coords' => [
                    'galaxy' => $defenderPlanet->galaxy,
                    'system' => $defenderPlanet->system,
                    'position' => $defenderPlanet->position,
                ],
                'total_power' => $defenderPower,
                'initial_units' => $initialDefender,
                'initial_ships' => $initialDefenderShips,
                'initial_defenses' => $initialDefenderDefenses,
            ],
            'units_catalog' => $unitsCatalog,
            'combat' => [
                'winner' => $combatResult['winner'] ?? 'draw',
                'total_rounds' => $combatResult['total_rounds'] ?? 0,
                'rounds' => $combatResult['rounds'] ?? [],
                'surviving_attacker_units' => $combatResult['surviving_attacker_units'] ?? [],
                'surviving_defender_units' => $combatResult['surviving_defender_units'] ?? [],
            ],
            'pillaged_resources' => $pillaged,
        ];
    }

    /**
     * Calculate points gained from combat for the attacker
     */
    protected function calculatePointsFromCombat(array $combatResult, int $attackerUserId): int
    {
        if ($combatResult['winner'] !== 'attacker') {
            return 0;
        }

        $totalPoints = 0;
        foreach ($combatResult['rounds'] as $round) {
            if (!empty($round['defender_losses'])) {
                $totalPoints += $this->calculatePointsFromLosses($round['defender_losses']);
            }
        }

        return $totalPoints;
    }

    /**
     * Get the icon path for a unit based on its type
     */
    protected function getUnitIconPath($unit)
    {
        $baseUrl = '/images/';
        
        switch ($unit->type) {
            case TemplateBuild::TYPE_UNIT:
                return $baseUrl . 'units/' . $unit->icon;
            case TemplateBuild::TYPE_DEFENSE:
                return $baseUrl . 'defenses/' . $unit->icon;
            case TemplateBuild::TYPE_SHIP:
                return $baseUrl . 'ships/' . $unit->icon;
            default:
                return $baseUrl . 'units/' . $unit->icon;
        }
    }

    /**
     * Get defender ships from planet
     */
    protected function getDefenderShips($planetId)
    {
        return PlanetShip::where('planet_id', $planetId)
            ->where('quantity', '>', 0)
            ->with('ship')
            ->get()
            ->mapWithKeys(function ($ship) {
                return [$ship->ship_id => $ship->quantity];
            })
            ->toArray();
    }

    /**
     * Get defender defenses from planet
     */
    protected function getDefenderDefenses($planetId)
    {
        return PlanetDefense::where('planet_id', $planetId)
            ->where('quantity', '>', 0)
            ->with('defense')
            ->get()
            ->mapWithKeys(function ($defense) {
                return [$defense->defense_id => $defense->quantity];
            })
            ->toArray();
    }

    /**
     * Calculate spatial combat power with bonuses
     */
    protected function calculateSpatialCombatPower($planetId, $units, $type = 'attack')
    {
        $totalPower = 0;
        $powerField = $type === 'attack' ? 'attack_power' : 'defense_power';

        foreach ($units as $unitId => $quantity) {
            $unit = TemplateBuild::find($unitId);
            if ($unit) {
                $basePower = $unit->{$powerField} ?? 0;
                $totalPower += $basePower * $quantity;
            }
        }

        // Apply bonuses from buildings and technologies
        $bonus = TemplateBuildAdvantage::getShieldBonus($planetId, 'spatial');
        
        // Get planet to access user
        $planet = \App\Models\Planet\Planet::find($planetId);
        
        // Apply faction bonus if available
        if ($planet && $planet->user && $planet->user->faction) {
            // Get faction bonus based on combat type
            if ($type === 'attack') {
                $factionBonus = $planet->user->faction->getBonusAttackPower();
            } else { // defense
                $factionBonus = $planet->user->faction->getBonusDefensePower();
            }
            
            // Add faction bonus to existing bonus
            $bonus += $factionBonus;
        }
        
        $totalPower = $totalPower * (1 + $bonus / 100);

        return $totalPower;
    }

    /**
     * Execute spatial combat rounds
     */
    protected function executeSpatialCombatRounds($attackerShips, $defenderUnits, $attackerPower, $defenderPower, $attackerPlanetId, $defenderPlanetId)
    {
        $rounds = [];
        $currentAttackerShips = $attackerShips;
        $currentDefenderUnits = $defenderUnits;
        $roundNumber = 1;
        $maxRounds = 6;

        while ($roundNumber <= $maxRounds && !empty($currentAttackerShips) && !empty($currentDefenderUnits)) {
            $round = $this->executeSpatialRound(
                $currentAttackerShips,
                $currentDefenderUnits,
                $attackerPlanetId,
                $defenderPlanetId,
                $roundNumber
            );

            $rounds[] = $round;

            // Update units after round
            $currentAttackerShips = $round['surviving_attacker_units'];
            $currentDefenderUnits = $round['surviving_defender_units'];

            $roundNumber++;
        }

        // Determine winner
        $winner = 'draw';
        if (!empty($currentAttackerShips) && empty($currentDefenderUnits)) {
            $winner = 'attacker';
        } elseif (empty($currentAttackerShips) && !empty($currentDefenderUnits)) {
            $winner = 'defender';
        }

        return [
            'winner' => $winner,
            'rounds' => $rounds,
            'surviving_attacker_units' => $currentAttackerShips,
            'surviving_defender_units' => $currentDefenderUnits
        ];
    }

    /**
     * Execute a single spatial combat round
     */
    protected function executeSpatialRound($attackerShips, $defenderUnits, $attackerPlanetId, $defenderPlanetId, $roundNumber)
    {
        // Calculate current round power
        $attackerPower = $this->calculateSpatialCombatPower($attackerPlanetId, $attackerShips, 'attack');
        $defenderPower = $this->calculateSpatialCombatPower($defenderPlanetId, $defenderUnits, 'defense');

        // Calculate shield powers
        $attackerShieldPower = $this->calculateTotalShieldPower($attackerPlanetId, $attackerShips);
        $defenderShieldPower = $this->calculateTotalShieldPower($defenderPlanetId, $defenderUnits);

        // Calculate damage dealt
        $attackerDamage = max(0, $attackerPower - $defenderShieldPower * 0.95);
        $defenderDamage = max(0, $defenderPower - $attackerShieldPower * 0.95);

        // Calculate losses based on damage vs life (pass planetId for potential bonuses/logging)
        $attackerLosses = $this->calculateLifeBasedLosses($attackerShips, $defenderDamage, $attackerPlanetId);
        $defenderLosses = $this->calculateLifeBasedLosses($defenderUnits, $attackerDamage, $defenderPlanetId);

        // Apply losses
        $survivingAttackerShips = $this->subtractUnits($attackerShips, $attackerLosses);
        $survivingDefenderUnits = $this->subtractUnits($defenderUnits, $defenderLosses);

        return [
            'round' => $roundNumber,
            'attacker_power' => $attackerPower,
            'defender_power' => $defenderPower,
            'attacker_shield_power' => $attackerShieldPower,
            'defender_shield_power' => $defenderShieldPower,
            'attacker_damage' => $attackerDamage,
            'defender_damage' => $defenderDamage,
            'attacker_losses' => $attackerLosses,
            'defender_losses' => $defenderLosses,
            'surviving_attacker_units' => $survivingAttackerShips,
            'surviving_defender_units' => $survivingDefenderUnits
        ];
    }

    /**
     * Update defender ships and defenses with losses
     */
    protected function updateDefenderShipsAndDefenses($planetId, $defenderShips, $defenderDefenses, $combatResult)
    {
        $survivingUnits = $combatResult['surviving_defender_units'];

        // Update ships
        foreach ($defenderShips as $shipId => $originalQuantity) {
            $survivingQuantity = $survivingUnits[$shipId] ?? 0;
            PlanetShip::where('planet_id', $planetId)
                ->where('ship_id', $shipId)
                ->update(['quantity' => $survivingQuantity]);
        }

        // Update defenses
        foreach ($defenderDefenses as $defenseId => $originalQuantity) {
            $survivingQuantity = $survivingUnits[$defenseId] ?? 0;
            PlanetDefense::where('planet_id', $planetId)
                ->where('defense_id', $defenseId)
                ->update(['quantity' => $survivingQuantity]);
        }
    }

    /**
     * Generate spatial combat report
     */
    protected function generateSpatialCombatReport($data)
    {
        $attackerPlanet = $data['attacker_planet'];
        $defenderPlanet = $data['defender_planet'];
        $combatResult = $data['combat_result'];
        $pillagedResources = $data['pillaged_resources'] ?? [];
        $attackerPower = $data['attacker_power'];
        $defenderPower = $data['defender_power'];

        // Personnalisation des noms et icônes par joueur
        /** @var UserCustomizationService $customizer */
        $customizer = app(UserCustomizationService::class);
        $resolveAttackerDisplay = function(TemplateBuild $tpl) use ($customizer, $attackerPlanet) {
            $d = $customizer->resolveBuild($attackerPlanet->user, $tpl);
            return [
                'name' => $d['name'] ?? ($tpl->label ?? $tpl->name),
                'icon' => $d['icon_url'] ?? null,
            ];
        };
        $resolveDefenderDisplay = function(TemplateBuild $tpl) use ($customizer, $defenderPlanet) {
            $d = $customizer->resolveBuild($defenderPlanet->user, $tpl);
            return [
                'name' => $d['name'] ?? ($tpl->label ?? $tpl->name),
                'icon' => $d['icon_url'] ?? null,
            ];
        };

        $html = '<div class="combat-report spatial-combat">';

        // Header
        $html .= '<div class="report-header">';
        $html .= '<h3>Rapport de combat spatial</h3>';
        $html .= '</div>';

        // Combat info
        $html .= '<div class="combat-info">';
        $html .= '<div class="attacker-info">';
        $html .= '<h4>⚔️ Attaquant</h4>';
        $html .= '<p><strong>Planète:</strong> ' . $attackerPlanet->name . '</p>';
        $html .= '<p><strong>Coordonnées:</strong> [' . $attackerPlanet->galaxy . ':' . $attackerPlanet->system . ':' . $attackerPlanet->position . ']</p>';
        $html .= '<p><strong>Joueur:</strong> ' . $attackerPlanet->user->name . '</p>';
        $html .= '<p><strong>Puissance initiale:</strong> ' . number_format($attackerPower) . '</p>';
        $html .= '</div>';

        $html .= '<div class="defender-info">';
        $html .= '<h4>🛡️ Défenseur</h4>';
        $html .= '<p><strong>Planète:</strong> ' . $defenderPlanet->name . '</p>';
        $html .= '<p><strong>Coordonnées:</strong> [' . $defenderPlanet->galaxy . ':' . $defenderPlanet->system . ':' . $defenderPlanet->position . ']</p>';
        $html .= '<p><strong>Joueur:</strong> ' . $defenderPlanet->user->name . '</p>';
        $html .= '<p><strong>Puissance initiale:</strong> ' . number_format($defenderPower) . '</p>';
        $html .= '</div>';
        $html .= '</div>';

        // Forces initiales
        $html .= '<div class="forces-summary">';
        $html .= '<div class="attacker-forces">';
        $html .= '<h4>Forces de l\'attaquant</h4>';
        $html .= '<ul>';
        foreach (($data['initial_attacker_ships'] ?? []) as $unitId => $quantity) {
            $unit = TemplateBuild::find($unitId);
            if ($unit) {
                $attDisp = $resolveAttackerDisplay($unit);
                $iconPath = $attDisp['icon'] ?? $this->getUnitIconPath($unit);
                $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($attDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($attDisp['name']) . ': ' . number_format($quantity) . '</li>';
            }
        }
        $html .= '</ul>';
        $html .= '</div>';

        $html .= '<div class="defender-forces">';
        $html .= '<h4>Forces du défenseur</h4>';
        $html .= '<ul>';
        $defenderInitial = array_merge(($data['initial_defender_ships'] ?? []), ($data['initial_defender_defenses'] ?? []));
        if (empty($defenderInitial)) {
            $html .= '<li>Aucune unité de défense</li>';
        } else {
            foreach ($defenderInitial as $unitId => $quantity) {
                $unit = TemplateBuild::find($unitId);
                if ($unit) {
                    $defDisp = $resolveDefenderDisplay($unit);
                    $iconPath = $defDisp['icon'] ?? $this->getUnitIconPath($unit);
                    $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($defDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($defDisp['name']) . ': ' . number_format($quantity) . '</li>';
                }
            }
        }
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';

        // Détails des rounds
        if (!empty($combatResult['rounds'])) {
            $html .= '<div class="rounds-details">';
            $html .= '<h4>📊 Détails des Rounds</h4>';
            foreach ($combatResult['rounds'] as $round) {
                $html .= '<div class="round">';
                $html .= '<h5>Round ' . $round['round'] . '</h5>';
                $html .= '<div class="round-powers">';
                $html .= '<span class="attacker-power">Puissance Attaque: ' . number_format($round['attacker_power']) . '</span>';
                $html .= '<span class="defender-power">Puissance Défense: ' . number_format($round['defender_power']) . '</span>';
                $html .= '</div>';

                // Pertes
                if (!empty($round['attacker_losses']) || !empty($round['defender_losses'])) {
                    $html .= '<div class="round-losses">';
                    // Pertes attaquant
                    $html .= '<div class="attacker-losses">';
                    $html .= '<h6>Pertes Attaquant:</h6>';
                    if (empty($round['attacker_losses'])) {
                        $html .= '<p>Aucune perte</p>';
                    } else {
                        $html .= '<ul>';
                        foreach ($round['attacker_losses'] as $unitId => $quantity) {
                            $unit = TemplateBuild::find($unitId);
                            if ($unit) {
                                $attDisp = $resolveAttackerDisplay($unit);
                                $iconPath = $attDisp['icon'] ?? $this->getUnitIconPath($unit);
                                $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($attDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($attDisp['name']) . ': -' . number_format($quantity) . '</li>';
                            }
                        }
                        $html .= '</ul>';
                    }
                    $html .= '</div>';

                    // Pertes défenseur
                    $html .= '<div class="defender-losses">';
                    $html .= '<h6>Pertes Défenseur:</h6>';
                    if (empty($round['defender_losses'])) {
                        $html .= '<p>Aucune perte</p>';
                    } else {
                        $html .= '<ul>';
                        foreach ($round['defender_losses'] as $unitId => $quantity) {
                            $unit = TemplateBuild::find($unitId);
                            if ($unit) {
                                $defDisp = $resolveDefenderDisplay($unit);
                                $iconPath = $defDisp['icon'] ?? $this->getUnitIconPath($unit);
                                $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($defDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($defDisp['name']) . ': -' . number_format($quantity) . '</li>';
                            }
                        }
                        $html .= '</ul>';
                    }
                    $html .= '</div>';
                    $html .= '</div>';
                }

                // Dégâts
                $html .= '<div class="round-damages">';
                $html .= '<span>Dégâts Attaquant: ' . number_format($round['attacker_damage']) . '</span>';
                $html .= '<span>Dégâts Défenseur: ' . number_format($round['defender_damage']) . '</span>';
                $html .= '</div>';

                $html .= '</div>';
            }
            $html .= '</div>';
        }

        // Unités survivantes
        $html .= '<div class="surviving-units">';
        $html .= '<h4>Unités survivantes</h4>';

        $html .= '<div class="attacker-survivors">';
        $html .= '<h5>Attaquant:</h5>';
        if (empty($combatResult['surviving_attacker_units'])) {
            $html .= '<p>Aucune unité survivante</p>';
        } else {
            $html .= '<ul>';
            foreach ($combatResult['surviving_attacker_units'] as $unitId => $quantity) {
                $unit = TemplateBuild::find($unitId);
                if ($unit) {
                    $attDisp = $resolveAttackerDisplay($unit);
                    $iconPath = $attDisp['icon'] ?? $this->getUnitIconPath($unit);
                    $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($attDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($attDisp['name']) . ': ' . number_format($quantity) . '</li>';
                }
            }
            $html .= '</ul>';
        }
        $html .= '</div>';

        $html .= '<div class="defender-survivors">';
        $html .= '<h5>Défenseur:</h5>';
        if (empty($combatResult['surviving_defender_units'])) {
            $html .= '<p>Aucune unité survivante</p>';
        } else {
            $html .= '<ul>';
            foreach ($combatResult['surviving_defender_units'] as $unitId => $quantity) {
                $unit = TemplateBuild::find($unitId);
                if ($unit) {
                    $defDisp = $resolveDefenderDisplay($unit);
                    $iconPath = $defDisp['icon'] ?? $this->getUnitIconPath($unit);
                    $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($defDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($defDisp['name']) . ': ' . number_format($quantity) . '</li>';
                }
            }
            $html .= '</ul>';
        }
        $html .= '</div>';
        $html .= '</div>';

        // Résumé des pertes totales
        $html .= '<div class="destroyed-units-summary">';
        $html .= '<h4>💀 Résumé des Pertes Totales</h4>';

        $totalAttackerLosses = [];
        $totalDefenderLosses = [];
        if (!empty($combatResult['rounds'])) {
            foreach ($combatResult['rounds'] as $round) {
                if (!empty($round['attacker_losses'])) {
                    foreach ($round['attacker_losses'] as $unitId => $quantity) {
                        $totalAttackerLosses[$unitId] = ($totalAttackerLosses[$unitId] ?? 0) + $quantity;
                    }
                }
                if (!empty($round['defender_losses'])) {
                    foreach ($round['defender_losses'] as $unitId => $quantity) {
                        $totalDefenderLosses[$unitId] = ($totalDefenderLosses[$unitId] ?? 0) + $quantity;
                    }
                }
            }
        }

        $html .= '<div class="total-losses">';
        $html .= '<div class="attacker-total-losses">';
        $html .= '<h5>Pertes de l\'attaquant:</h5>';
        if (empty($totalAttackerLosses)) {
            $html .= '<p>Aucune perte</p>';
        } else {
            $html .= '<ul>';
            foreach ($totalAttackerLosses as $unitId => $quantity) {
                $unit = TemplateBuild::find($unitId);
                if ($unit) {
                    $attDisp = $resolveAttackerDisplay($unit);
                    $iconPath = $attDisp['icon'] ?? $this->getUnitIconPath($unit);
                    $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($attDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($attDisp['name']) . ': -' . number_format($quantity) . '</li>';
                }
            }
            $html .= '</ul>';
        }
        $html .= '</div>';

        $html .= '<div class="defender-total-losses">';
        $html .= '<h5>Pertes du défenseur:</h5>';
        if (empty($totalDefenderLosses)) {
            $html .= '<p>Aucune perte</p>';
        } else {
            $html .= '<ul>';
            foreach ($totalDefenderLosses as $unitId => $quantity) {
                $unit = TemplateBuild::find($unitId);
                if ($unit) {
                    $defDisp = $resolveDefenderDisplay($unit);
                    $iconPath = $defDisp['icon'] ?? $this->getUnitIconPath($unit);
                    $html .= '<li><img src="' . $iconPath . '" alt="' . htmlspecialchars($defDisp['name']) . '" class="unit-icon"> ' . htmlspecialchars($defDisp['name']) . ': -' . number_format($quantity) . '</li>';
                }
            }
            $html .= '</ul>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        // Points de combat
        $html .= '<div class="combat-points">';
        $html .= '<h4>⭐ Points de Combat</h4>';

        if ($combatResult['winner'] === 'attacker') {
            $defenderPoints = $this->calculatePointsFromLosses($totalDefenderLosses);
            $html .= '<div class="points-awarded">';
            $html .= '<p><strong>🏆 ' . $attackerPlanet->user->name . ' (Attaquant)</strong> gagne <span style="color: #22c55e; font-weight: bold;">' . number_format($defenderPoints) . ' points</span></p>';
            $html .= '<p>' . $defenderPlanet->user->name . ' (Défenseur) ne gagne aucun point</p>';
            $html .= '</div>';
        } elseif ($combatResult['winner'] === 'defender') {
            $attackerPoints = $this->calculatePointsFromLosses($totalAttackerLosses);
            $html .= '<div class="points-awarded">';
            $html .= '<p><strong>🏆 ' . $defenderPlanet->user->name . ' (Défenseur)</strong> gagne <span style="color: #22c55e; font-weight: bold;">' . number_format($attackerPoints) . ' points</span></p>';
            $html .= '<p>' . $attackerPlanet->user->name . ' (Attaquant) ne gagne aucun point</p>';
            $html .= '</div>';
        } else {
            $html .= '<div class="points-awarded">';
            $html .= '<p>Match nul - Aucun point attribué</p>';
            $html .= '</div>';
        }
        $html .= '</div>';

        // Ressources pillées
        if (!empty($pillagedResources)) {
            $html .= '<div class="pillaged-resources">';
            $html .= '<h4>💰 Ressources Pillées</h4>';
            $html .= '<ul>';
            foreach ($pillagedResources as $resourceId => $quantity) {
                $resource = TemplateResource::find($resourceId);
                $resourceName = $resource ? $resource->display_name : 'Ressource ' . $resourceId;
                $html .= '<li>' . $resourceName . ': ' . number_format($quantity) . '</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Normalize units payload to [template_id => quantity] map.
     */
    protected function normalizeUnitsPayload($units): array
    {
        if (empty($units)) {
            return [];
        }

        $normalized = [];
        foreach ($units as $key => $value) {
            if (is_array($value)) {
                $templateId = $value['id'] ?? (is_numeric($key) ? $key : null);
                $quantity = $value['quantity'] ?? 0;
                if ($templateId) {
                    $normalized[$templateId] = ($normalized[$templateId] ?? 0) + (int) $quantity;
                }
            } else {
                // Value is quantity, key is expected to be template id
                if (is_numeric($key)) {
                    $normalized[(int) $key] = ($normalized[(int) $key] ?? 0) + (int) $value;
                } else {
                    // Fallback: try to resolve template by name key
                    $template = TemplateBuild::where('name', $key)->first();
                    if ($template) {
                        $normalized[$template->id] = ($normalized[$template->id] ?? 0) + (int) $value;
                    }
                }
            }
        }
        return $normalized;
    }

    /**
     * Get ship speed from mission for return time calculation
     */
    protected function getShipSpeedFromMission($mission)
    {
        $ships = $this->normalizeUnitsPayload($mission->ships ?? []);
        if (empty($ships)) {
            return 1; // Default speed
        }

        $minSpeed = null;
        foreach ($ships as $shipId => $quantity) {
            $ship = TemplateBuild::find($shipId);
            if ($ship && $ship->speed) {
                if ($minSpeed === null || $ship->speed < $minSpeed) {
                    $minSpeed = $ship->speed;
                }
            }
        }

        return $minSpeed ?? 1;
    }
}