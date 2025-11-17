<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Server\ServerConfig;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetResource;
use App\Models\Planet\PlanetBuilding;
use App\Models\Planet\PlanetUnit;
use App\Models\Planet\PlanetDefense;
use App\Models\Planet\PlanetShip;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplateResource;
use App\Models\Template\TemplateBuildCost;
use App\Models\Template\TemplateBuildRequired;
use App\Models\Template\TemplateBuildAdvantage;
use App\Models\Other\Queue;
use App\Models\User;
use App\Models\User\UserTechnology;
use App\Services\ResourceService;
use App\Models\BotTickRun;
use Illuminate\Support\Facades\Storage;

class BotTick extends Command
{
    protected $signature = 'bot:tick {--ignore : Ignorer la planification du jour et exécuter immédiatement} {--allow-multiple : Autoriser plusieurs exécutions le même jour} {--batch-size=100 : Nombre de planètes traitées par lot} {--sleep-ms=0 : Pause entre les lots, en millisecondes} {--max-building-levels=10 : Niveaux de bâtiment max par planète} {--max-production-quantity=100 : Quantité max produite par planète} {--max-research-levels=10 : Niveaux de recherche max par exécution}';
    protected $description = 'Exécute le tick quotidien du bot (userid 1) à une heure aléatoire et fait évoluer ses planètes.';

    public function handle(): int
    {
        $startTime = microtime(true);
        // Allow long-running execution for large batches (CLI)
        @set_time_limit(0);
        // Fuseau horaire serveur
        $tz = ServerConfig::getServerTimezone();
        $now = Carbon::now($tz);
        $ignoreSchedule = (bool) $this->option('ignore');
        $allowMultiple = (bool) $this->option('allow-multiple');

        // Verrou journalier via cache pour empêcher multiples exécutions le même jour
        $todayStr = $now->toDateString();
        $doneTodayKey = 'bot_tick_done:' . $todayStr;
        $secondsLeftInDay = $now->diffInSeconds($now->copy()->endOfDay()) + 2; // marge
        if (!$allowMultiple) {
            // add() n'écrit que si la clé n'existe pas; sinon, on sort
            $added = Cache::add($doneTodayKey, 1, $secondsLeftInDay);
            if (!$added) {
                $this->line('BotTick: déjà exécuté aujourd\'hui (verrou cache).');
                return Command::SUCCESS;
            }
        }

        // Clés de configuration pour la planification
        $dayKey = 'bot_tick_day';
        $nextRunKey = 'bot_tick_next_run_at';
        $lastRunKey = 'bot_tick_last_run_at';
        $botUserKey = 'bot_user_id';

        $botUserId = (int) ServerConfig::get($botUserKey, 1);

        $storedDay = ServerConfig::get($dayKey);
        $nextRunAtStr = ServerConfig::get($nextRunKey);
        $lastRunAtStr = ServerConfig::get($lastRunKey);

        $todayStr = $now->toDateString();
        $nextRunAt = $nextRunAtStr ? Carbon::parse($nextRunAtStr, $tz) : null;
        $lastRunAt = $lastRunAtStr ? Carbon::parse($lastRunAtStr, $tz) : null;

        // Initialiser l'heure aléatoire du jour si manquante ou si changement de jour
        if (!$storedDay || $storedDay !== $todayStr || !$nextRunAt) {
            $randSeconds = random_int(0, 86399); // 0h-23h59
            $newNextRun = $now->copy()->startOfDay()->addSeconds($randSeconds);
            ServerConfig::set($dayKey, $todayStr, ServerConfig::TYPE_STRING, ServerConfig::CATEGORY_GENERAL, 'Jour du prochain BotTick');
            ServerConfig::set($nextRunKey, $newNextRun->toIso8601String(), ServerConfig::TYPE_STRING, ServerConfig::CATEGORY_GENERAL, 'Exécution aléatoire quotidienne du BotTick');
            $nextRunAt = $newNextRun;
            Log::info('BotTick: nouvelle plage quotidienne planifiée', [
                'next_run_at' => $nextRunAt->toIso8601String(),
                'timezone' => $tz
            ]);
        }

        // Si déjà exécuté aujourd'hui, ne rien faire sauf si --allow-multiple
        if ($lastRunAt && $lastRunAt->isSameDay($now) && !$allowMultiple) {
            $this->line('BotTick: déjà exécuté aujourd\'hui.');
            return Command::SUCCESS;
        }

        // Attendre l'heure aléatoire (sauf si --ignore)
        if (!$ignoreSchedule && $nextRunAt && $now->lt($nextRunAt)) {
            $this->line('BotTick: en attente de l\'heure planifiée (' . $nextRunAt->toDateTimeString() . ').');
            return Command::SUCCESS;
        }

        if ($ignoreSchedule) {
            $this->line('BotTick: option --ignore détectée, exécution immédiate malgré la planification.');
        }
        if ($allowMultiple && $lastRunAt && $lastRunAt->isSameDay($now)) {
            $this->line('BotTick: option --allow-multiple détectée, exécution à nouveau aujourd\'hui.');
        }

        $this->line('BotTick: démarrage du traitement des planètes du bot.');

        // Journalisation d'exécution
        $run = BotTickRun::create([
            'status' => 'running',
            'started_at' => Carbon::now($tz),
            'planets_processed' => 0,
        ]);
        $executionDetails = [
            'started_at' => Carbon::now($tz)->toIso8601String(),
            'user_id' => $botUserId,
            'options' => [
                'ignore' => $ignoreSchedule,
                'allow_multiple' => $allowMultiple,
                'batch_size' => (int) $this->option('batch-size'),
                'sleep_ms' => (int) $this->option('sleep-ms'),
                'max_building_levels' => (int) $this->option('max-building-levels'),
                'max_production_quantity' => (int) $this->option('max-production-quantity'),
                'max_research_levels' => (int) $this->option('max-research-levels'),
            ],
            'planets' => [],
        ];

        // Exécuter le traitement
        try {
            Log::info('BotTick: transaction ouverte');

            $user = User::find($botUserId);
            if (!$user) {
                $this->error('BotTick: utilisateur bot introuvable (id=' . $botUserId . ').');
                DB::rollBack();
                return Command::FAILURE;
            }
            Log::info('BotTick: utilisateur bot chargé', ['user_id' => $user->id]);

            // Traiter toutes les planètes du bot par lots pour supporter 2000+ planètes
            $batchSize = (int) $this->option('batch-size');
            $sleepMs = (int) $this->option('sleep-ms');
            Log::info('BotTick: récupération des planètes du bot (batch)', ['user_id' => $botUserId, 'batch_size' => $batchSize]);
            $resourceService = app(ResourceService::class);

            $processedPlanets = 0;
            $totalBuildingsBuilt = 0;
            $totalBuildingsUpgraded = 0;
            $totalUnitsBuilt = 0;
            $totalDefensesBuilt = 0;
            $totalShipsBuilt = 0;
            $totalResearchUpgraded = 0;
            $totalQueuesCompleted = 0;
            $totalResourcesSpent = [];
            $totalResourcesGenerated = [];
            Planet::where('user_id', $botUserId)
                ->orderBy('id')
                ->chunkById($batchSize, function ($planets) use (&$processedPlanets, &$totalBuildingsBuilt, &$totalBuildingsUpgraded, &$totalUnitsBuilt, &$totalDefensesBuilt, &$totalShipsBuilt, &$totalResearchUpgraded, &$totalQueuesCompleted, &$totalResourcesGenerated, &$totalResourcesSpent, $user, $resourceService, $sleepMs, &$executionDetails) {
                    foreach ($planets as $planet) {
                        try {
                            Log::info('BotTick: début traitement planète', ['planet_id' => $planet->id]);
                            $stats = $this->processPlanet($planet, $user, $resourceService);
                            Log::info('BotTick: fin traitement planète', ['planet_id' => $planet->id]);
                            $processedPlanets++;

                            // Agréger les métriques
                            $totalBuildingsBuilt += (int)($stats['buildings_built'] ?? 0);
                            $totalBuildingsUpgraded += (int)($stats['buildings_upgraded'] ?? 0);
                            $totalUnitsBuilt += (int)($stats['units_built'] ?? 0);
                            $totalDefensesBuilt += (int)($stats['defenses_built'] ?? 0);
                            $totalShipsBuilt += (int)($stats['ships_built'] ?? 0);
                            $totalResearchUpgraded += (int)($stats['research_upgraded'] ?? 0);
                            $totalQueuesCompleted += (int)($stats['queues_completed'] ?? 0);
                            foreach (($stats['resources_generated'] ?? []) as $rName => $delta) {
                                $totalResourcesGenerated[$rName] = ($totalResourcesGenerated[$rName] ?? 0) + (int)$delta;
                            }
                            foreach (($stats['resources_spent'] ?? []) as $rName => $delta) {
                                $totalResourcesSpent[$rName] = ($totalResourcesSpent[$rName] ?? 0) + (int)$delta;
                            }

                            // Détails par planète
                            $executionDetails['planets'][] = [
                                'planet_id' => $planet->id,
                                'buildings_built' => (int)($stats['buildings_built'] ?? 0),
                                'buildings_upgraded' => (int)($stats['buildings_upgraded'] ?? 0),
                                'units_built' => (int)($stats['units_built'] ?? 0),
                                'defenses_built' => (int)($stats['defenses_built'] ?? 0),
                                'ships_built' => (int)($stats['ships_built'] ?? 0),
                                'research_upgraded' => (int)($stats['research_upgraded'] ?? 0),
                                'resources_generated' => ($stats['resources_generated'] ?? []),
                                'resources_spent' => ($stats['resources_spent'] ?? []),
                            ];
                        } catch (\Throwable $e) {
                            Log::error('BotTick: erreur traitement planète', ['planet_id' => $planet->id, 'error' => $e->getMessage()]);
                        }
                    }

                    if ($sleepMs > 0) {
                        usleep($sleepMs * 1000);
                    }
                }, 'id');

            // Aligner les points de recherche du bot sur 24h en une seule fois
            $cacheKeyLast = 'research:last_tick:user:' . $user->id;
            $cacheKeyBuffer = 'research:buffer:user:' . $user->id;
            Cache::put($cacheKeyLast, Carbon::now()->subDay(), 86400);
            Cache::put($cacheKeyBuffer, 0.0, 86400);
            $firstPlanet = Planet::where('user_id', $user->id)->orderBy('id')->first();
            if ($firstPlanet) {
                $resourceService->updateUserResearchPoints($firstPlanet);
            }

            // Marquer l\'exécution du jour
            ServerConfig::set($lastRunKey, $now->toIso8601String(), ServerConfig::TYPE_STRING, ServerConfig::CATEGORY_GENERAL, 'Dernière exécution BotTick');
            // Effacer le next_run pour éviter ré-exécution le même jour
            ServerConfig::set($nextRunKey, '', ServerConfig::TYPE_STRING, ServerConfig::CATEGORY_GENERAL, 'Next run reset après exécution');
            // Confirmer le verrou cache jusqu'à fin de journée
            if (!$allowMultiple) {
                Cache::put($doneTodayKey, 1, $secondsLeftInDay);
            }

            // Enregistrer les métriques
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);
            ServerConfig::set('bot_tick_duration_ms', $durationMs, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Durée du BotTick en ms');
            ServerConfig::set('bot_tick_processed_count', $processedPlanets, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Nombre de planètes traitées par le bot');

            // Enregistrer les métriques détaillées
            ServerConfig::set('bot_tick_buildings_built_count', $totalBuildingsBuilt, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Bâtiments construits pendant le BotTick');
            ServerConfig::set('bot_tick_buildings_upgraded_count', $totalBuildingsUpgraded, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Bâtiments améliorés pendant le BotTick');
            ServerConfig::set('bot_tick_units_built_count', $totalUnitsBuilt, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Unités produites pendant le BotTick');
            ServerConfig::set('bot_tick_defenses_built_count', $totalDefensesBuilt, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Défenses produites pendant le BotTick');
            ServerConfig::set('bot_tick_ships_built_count', $totalShipsBuilt, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Vaisseaux produits pendant le BotTick');
            ServerConfig::set('bot_tick_research_upgraded_count', $totalResearchUpgraded, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Technologies améliorées pendant le BotTick');
            ServerConfig::set('bot_tick_queues_completed_count', $totalQueuesCompleted, ServerConfig::TYPE_INTEGER, ServerConfig::CATEGORY_GENERAL, 'Files terminées pendant le BotTick');

            // Ressources générées/dépensées (stockage JSON pour flexibilité UI)
            $resourcesJson = json_encode($totalResourcesGenerated);
            ServerConfig::set('bot_tick_resources_generated_json', $resourcesJson, ServerConfig::TYPE_STRING, ServerConfig::CATEGORY_GENERAL, 'Ressources générées agrégées (JSON) pendant le BotTick');
            $resourcesSpentJson = json_encode($totalResourcesSpent);
            ServerConfig::set('bot_tick_resources_spent_json', $resourcesSpentJson, ServerConfig::TYPE_STRING, ServerConfig::CATEGORY_GENERAL, 'Ressources dépensées agrégées (JSON) pendant le BotTick');

            // Affichage résumé CLI
            $this->info('BotTick: traitement terminé pour ' . $processedPlanets . ' planète(s).');
            $this->line(' - Bâtiments: construits ' . $totalBuildingsBuilt . ', améliorés ' . $totalBuildingsUpgraded);
            $this->line(' - Production: unités ' . $totalUnitsBuilt . ', défenses ' . $totalDefensesBuilt . ', vaisseaux ' . $totalShipsBuilt);
            $this->line(' - Recherches améliorées: ' . $totalResearchUpgraded);
            $this->line(' - Files terminées: ' . $totalQueuesCompleted);
            if (!empty($totalResourcesGenerated)) {
                $this->line(' - Ressources générées:');
                foreach ($totalResourcesGenerated as $name => $amount) {
                    $this->line('   * ' . $name . ': ' . (int)$amount);
                }
            }
            if (!empty($totalResourcesSpent)) {
                $this->line(' - Ressources dépensées:');
                foreach ($totalResourcesSpent as $name => $amount) {
                    $this->line('   * ' . $name . ': ' . (int)$amount);
                }
            }
            // Écrire fichier JSON détaillé
            $executionDetails['totals'] = [
                'resources_generated' => $totalResourcesGenerated,
                'resources_spent' => $totalResourcesSpent,
                'buildings_built' => $totalBuildingsBuilt,
                'buildings_upgraded' => $totalBuildingsUpgraded,
                'units_built' => $totalUnitsBuilt,
                'defenses_built' => $totalDefensesBuilt,
                'ships_built' => $totalShipsBuilt,
                'research_upgraded' => $totalResearchUpgraded,
                'queues_completed' => $totalQueuesCompleted,
                'planets_processed' => $processedPlanets,
            ];
            $detailsPath = 'bot-tick/run_' . $run->id . '.json';
            Storage::disk('public')->put($detailsPath, json_encode($executionDetails, JSON_PRETTY_PRINT));

            // Mettre à jour le run
            $run->update([
                'status' => 'completed',
                'finished_at' => Carbon::now($tz),
                'planets_processed' => $processedPlanets,
                'resources_generated_json' => json_encode($totalResourcesGenerated),
                'resources_spent_json' => json_encode($totalResourcesSpent),
                'details_path' => $detailsPath,
            ]);

            Log::info('BotTick: terminé', ['planet_count' => $processedPlanets, 'user_id' => $botUserId, 'run_id' => $run->id]);
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            Log::error('BotTick: erreur lors du traitement', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            if (isset($run)) {
                $run->update([
                    'status' => 'failed',
                    'finished_at' => Carbon::now($tz),
                ]);
            }
            $this->error('BotTick: erreur - ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function processPlanet(Planet $planet, User $user, ResourceService $resourceService): array
    {
        try {
            // S'assurer que toutes les ressources existent pour la planète
            $this->ensurePlanetResources($planet);
            Log::info('BotTick: ressources initialisées', ['planet_id' => $planet->id]);

            // Mise à jour des ressources (production et stockage, + points de recherche)
            // Mesurer la génération avant/après pour cette planète
            $before = $planet->resources()->with('resource')->get()->mapWithKeys(function (PlanetResource $pr) {
                return [$pr->resource->name => (int)($pr->current_amount ?? 0)];
            });
            // Forcer une production de 24h en mettant last_update à -24h
            PlanetResource::where('planet_id', $planet->id)->update([
                'last_update' => Carbon::now()->subDay(),
            ]);
            // Mettre à jour les ressources (production sur 24h) en sautant la mise à jour des points de recherche par planète
            $resourceService->updatePlanetResources($planet, true);
            Log::info('BotTick: ressources mises à jour', ['planet_id' => $planet->id]);
            $after = $planet->resources()->with('resource')->get()->mapWithKeys(function (PlanetResource $pr) {
                return [$pr->resource->name => (int)($pr->current_amount ?? 0)];
            });
            $generated = [];
            foreach ($after as $name => $amount) {
                $delta = ($amount - ($before[$name] ?? 0));
                if ($delta > 0) {
                    $generated[$name] = $delta;
                }
            }

            // Ne pas utiliser de système de file pour le bot: construction/production instantanées
            $completed = [];
        } catch (\Throwable $e) {
            Log::error('BotTick: erreur update/complete', ['planet_id' => $planet->id, 'error' => $e->getMessage()]);
            return [
                'buildings_built' => 0,
                'buildings_upgraded' => 0,
                'units_built' => 0,
                'defenses_built' => 0,
                'ships_built' => 0,
                'research_upgraded' => 0,
                'queues_completed' => 0,
                'resources_generated' => [],
            ]; // ne pas bloquer le tick entier
        }

        // Construire une vue rapide des ressources de la planète
        $resourcesMap = $planet->resources()->with('resource')->get()
            ->mapWithKeys(function (PlanetResource $pr) {
                return [$pr->resource->name => $pr];
            });

        // Pour le bot: construction/production instantanée (pas de file)

        // 1) Tentative d'amélioration d'un bâtiment (instantanée)
        $stats = [
            'buildings_built' => 0,
            'buildings_upgraded' => 0,
            'units_built' => 0,
            'defenses_built' => 0,
            'ships_built' => 0,
            'research_upgraded' => 0,
            'queues_completed' => (int)count($completed ?? []),
            'resources_generated' => $generated,
        ];

        $resourcesSpentLocal = [];
        try {
            $maxLevels = (int) $this->option('max-building-levels');
            $bStats = $this->tryInstantBuildingUpgrade($planet, $resourcesMap, $maxLevels, $resourcesSpentLocal);
            $stats['buildings_built'] += (int)($bStats['built'] ?? 0);
            $stats['buildings_upgraded'] += (int)($bStats['upgraded'] ?? 0);
        } catch (\Throwable $e) {
            Log::error('BotTick: erreur file bâtiment', ['planet_id' => $planet->id, 'error' => $e->getMessage()]);
        }

        // 2) Tentative de production (unité/défense/vaisseau) instantanée
        foreach ([Queue::TYPE_UNIT, Queue::TYPE_DEFENSE, Queue::TYPE_SHIP] as $type) {
            try {
                $maxQty = (int) $this->option('max-production-quantity');
                $pStats = $this->tryInstantProduction($planet, $resourcesMap, $type, $maxQty, $resourcesSpentLocal);
                if (!empty($pStats)) {
                    if (($pStats['type'] ?? null) === Queue::TYPE_UNIT) {
                        $stats['units_built'] += (int)($pStats['quantity'] ?? 0);
                    } elseif (($pStats['type'] ?? null) === Queue::TYPE_DEFENSE) {
                        $stats['defenses_built'] += (int)($pStats['quantity'] ?? 0);
                    } elseif (($pStats['type'] ?? null) === Queue::TYPE_SHIP) {
                        $stats['ships_built'] += (int)($pStats['quantity'] ?? 0);
                    }
                }
                break; // un seul type produit par tick
            } catch (\Throwable $e) {
                Log::error('BotTick: erreur file production', ['planet_id' => $planet->id, 'type' => $type, 'error' => $e->getMessage()]);
            }
        }

        // 3) Recherche technologique instantanée si possible
        try {
            $maxResearchLevels = (int) $this->option('max-research-levels');
            $levelsUpgraded = $this->tryResearchTechnology($planet->user, $maxResearchLevels);
            $stats['research_upgraded'] += (int) $levelsUpgraded;
        } catch (\Throwable $e) {
            Log::error('BotTick: erreur recherche', ['planet_id' => $planet->id, 'error' => $e->getMessage()]);
        }

        // Ajouter ressources dépensées locales au résultat
        if (!empty($resourcesSpentLocal)) {
            $stats['resources_spent'] = $resourcesSpentLocal;
        }

        return $stats;
    }

    private function tryInstantBuildingUpgrade(Planet $planet, $resourcesMap, int $maxLevels, array &$resourcesSpent): array
    {
        // Stratégie simple: prioriser ressources et énergie, sinon un bâtiment aléatoire
        $candidates = TemplateBuild::active()->byType(TemplateBuild::TYPE_BUILDING)
            ->orderBy('category')->orderBy('id')->get();

        // Mélanger légèrement pour varier
        $candidates = $candidates->shuffle();

        foreach ($candidates as $build) {
            // Requête pour l\'élément de planète
            $planetItem = $planet->buildings()->where('building_id', $build->id)->first();
            $isQuantityBased = $build->max_level == 0;
            $nextLevel = $isQuantityBased ? 1 : (($planetItem?->level ?? 0) + 1);

            // Champs disponibles pour un nouveau bâtiment
            if (!$planetItem && !$isQuantityBased && !$planet->hasAvailableFields(1)) {
                continue;
            }

            // Vérifier prérequis
            if (!$this->requirementsMetForPlanet($planet->id, $build->id)) {
                continue;
            }

            // Tenter multi-niveaux jusqu'à maxLevels et ressources disponibles
            $built = 0; $upgraded = 0;
            $currentLevel = $planetItem?->level ?? 0;
            $targetLevel = $currentLevel;
            $steps = 0;
            while ($steps < max(1, $maxLevels)) {
                $nextLevel = ($planetItem ? $targetLevel + 1 : 1);
                // Respecter max_level des templates (>0) si défini
                if ($build->max_level > 0 && $nextLevel > $build->max_level) {
                    break;
                }

                $costs = $this->computeCosts($build, $nextLevel);
                if (!$this->hasResourcesForCosts($resourcesMap, $costs)) {
                    break;
                }
                // Déduire les ressources et tracer dépenses
                $this->consumeResources($resourcesMap, $costs, $resourcesSpent);

                if ($planetItem) {
                    $targetLevel = $nextLevel;
                    $upgraded += 1;
                } else {
                    // Création immédiate au niveau 1
                    PlanetBuilding::firstOrCreate(
                        [
                            'planet_id' => $planet->id,
                            'building_id' => $build->id,
                        ],
                        [
                            'level' => 1,
                            'is_active' => true,
                        ]
                    );
                    $planetItem = $planet->buildings()->where('building_id', $build->id)->first();
                    $built += 1;
                    $targetLevel = 1;
                }
                $steps++;
            }

            if ($planetItem && $targetLevel > ($planetItem->level ?? 0)) {
                $planetItem->upgradeToLevel($targetLevel);
            }

            Log::info('BotTick: bâtiment multi-niveaux appliqué', [
                'planet_id' => $planet->id,
                'build_id' => $build->id,
                'built' => $built,
                'levels_upgraded' => $upgraded,
                'final_level' => $planetItem?->level ?? $targetLevel
            ]);
            return ['built' => $built, 'upgraded' => $upgraded]; // Un bâtiment max par planète
        }
        return ['built' => 0, 'upgraded' => 0];
    }

    private function tryInstantProduction(Planet $planet, $resourcesMap, string $type, int $maxQuantity, array &$resourcesSpent): array
    {
        $candidates = TemplateBuild::active()->byType($type)->orderBy('id')->get()->shuffle();

        foreach ($candidates as $build) {
            // Unités/défenses/vaisseaux: quantité basée
            $perUnitCosts = $this->computeCosts($build, 1);

            // Calculer quantité maximale possible en fonction des ressources
            $maxQty = 0;
            foreach ($perUnitCosts as $name => $data) {
                $available = (int) ($resourcesMap[$name]?->current_amount ?? 0);
                $cost = max(1, (int) $data['amount']);
                $qtyForRes = intdiv($available, $cost);
                $maxQty = $maxQty === 0 ? $qtyForRes : min($maxQty, $qtyForRes);
            }

            // Limiter la production pour éviter des spikes et respecter option
            $quantity = max(0, min($maxQuantity, $maxQty));
            if ($quantity < 1) {
                continue;
            }

            // Vérifier prérequis
            if (!$this->requirementsMetForPlanet($planet->id, $build->id)) {
                continue;
            }

            // Coût total pour la quantité
            $totalCosts = [];
            foreach ($perUnitCosts as $name => $data) {
                $totalCosts[$name] = [
                    'amount' => (int) ($data['amount'] * $quantity),
                    'icon' => $data['icon'] ?? null,
                    'color' => $data['color'] ?? null,
                ];
            }

            if (!$this->hasResourcesForCosts($resourcesMap, $totalCosts)) {
                continue;
            }

            // Déduire ressources et tracer dépenses
            $this->consumeResources($resourcesMap, $totalCosts, $resourcesSpent);

            // Création instantanée via firstOrCreate
            [$modelClass, $idField] = $this->mapProductionModel($type);
            if (!$modelClass || !$idField) {
                continue;
            }

            /** @var \Illuminate\Database\Eloquent\Model $modelClass */
            $existing = $modelClass::where('planet_id', $planet->id)
                ->where($idField, $build->id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $quantity);
                $existing->update([
                    'is_building' => false,
                    'build_queue' => 0,
                    'build_start_time' => null,
                    'build_end_time' => null,
                    'is_active' => true,
                ]);
            } else {
                $modelClass::firstOrCreate(
                    [
                        'planet_id' => $planet->id,
                        $idField => $build->id,
                    ],
                    [
                        'quantity' => $quantity,
                        'is_building' => false,
                        'build_queue' => 0,
                        'build_start_time' => null,
                        'build_end_time' => null,
                        'is_active' => true,
                    ]
                );
            }

            Log::info('BotTick: production instantanée appliquée', [
                'planet_id' => $planet->id,
                'type' => $type,
                'item_id' => $build->id,
                'quantity' => $quantity
            ]);
            return ['type' => $type, 'item_id' => $build->id, 'quantity' => $quantity]; // Un seul type par tick
        }
        return [];
    }

    private function tryResearchTechnology(User $user, int $maxLevels): int
    {
        $technologies = TemplateBuild::active()->byType(TemplateBuild::TYPE_RESEARCH)->orderBy('sort_order')->get()->shuffle();
        foreach ($technologies as $tech) {
            $userTech = $user->technologies()->where('technology_id', $tech->id)->first();
            $currentLevel = $userTech?->level ?? 0;
            $levelsUpgraded = 0;

            // Vérifier prérequis (bâtiments côté planète actuelle de l\'utilisateur)
            $planet = $user->getActualPlanet();
            if (!$planet || !$this->requirementsMetForTechnology($user->id, $tech->id, $planet->id)) {
                continue;
            }
            while ($levelsUpgraded < max(1, $maxLevels)) {
                $nextLevel = $currentLevel + 1;
                // Respecter max_level
                if ($tech->max_level > 0 && $nextLevel > $tech->max_level) {
                    break;
                }
                // Calcul du coût: base_cost (niveau 1) * niveau
                $baseCost = TemplateBuildCost::where('build_id', $tech->id)
                    ->where('level', 1)->where('is_active', true)->first();
                $cost = $baseCost ? (int) ($baseCost->base_cost * $nextLevel) : 100;
                if ($user->research_points < $cost) {
                    break;
                }
                // Déduire les points et appliquer le niveau
                $user->decrement('research_points', $cost);
                $userTech = UserTechnology::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'technology_id' => $tech->id
                    ],
                    [
                        'level' => $nextLevel,
                        'is_active' => true
                    ]
                );
                $currentLevel = $nextLevel;
                $levelsUpgraded++;
            }

            if ($levelsUpgraded > 0) {
                Log::info('BotTick: technologie améliorée (multi-niveaux)', [
                    'user_id' => $user->id,
                    'tech_id' => $tech->id,
                    'levels_upgraded' => $levelsUpgraded,
                    'final_level' => $currentLevel
                ]);
                return $levelsUpgraded; // Une technologie par tick
            }
        }
        return 0;
    }

    private function computeCosts(TemplateBuild $build, int $level): array
    {
        $costs = [];
        foreach ($build->costs as $cost) {
            $baseCost = $cost->calculateCostForLevel($level);
            $costs[$cost->resource->name] = [
                'amount' => (int) $baseCost,
                'icon' => $cost->resource->icon,
                'color' => $cost->resource->color
            ];
        }
        return $costs;
    }

    private function hasResourcesForCosts($resourcesMap, array $costs): bool
    {
        foreach ($costs as $name => $data) {
            $available = (int) ($resourcesMap[$name]?->current_amount ?? 0);
            if ($available < (int) $data['amount']) {
                return false;
            }
        }
        return true;
    }

    private function consumeResources($resourcesMap, array $costs, array &$resourcesSpent = []): void
    {
        foreach ($costs as $name => $data) {
            if (isset($resourcesMap[$name])) {
                $resourcesMap[$name]->decrement('current_amount', (int) $data['amount']);
                $resourcesSpent[$name] = ($resourcesSpent[$name] ?? 0) + (int) $data['amount'];
            }
        }
    }

    private function requirementsMetForPlanet(int $planetId, int $buildId): bool
    {
        // Vérifier manuellement les prérequis pour éviter les inconsistances de colonnes
        $requirements = TemplateBuildRequired::active()->forBuild($buildId)->get();
        foreach ($requirements as $req) {
            $required = $req->requiredBuild;
            if (!$required) {
                continue;
            }
            if ($required->isBuilding()) {
                $planetBuild = \App\Models\Planet\PlanetBuilding::where('planet_id', $planetId)
                    ->where('building_id', $req->required_build_id)
                    ->where('is_active', true)
                    ->first();
                if (!$planetBuild || $planetBuild->level < $req->required_level) {
                    return false;
                }
            } elseif ($required->isResearch()) {
                $planet = \App\Models\Planet\Planet::find($planetId);
                if (!$planet) {
                    return false;
                }
                $userTech = \App\Models\User\UserTechnology::where('user_id', $planet->user_id)
                    ->where('technology_id', $req->required_build_id)
                    ->first();
                if (!$userTech || $userTech->level < $req->required_level) {
                    return false;
                }
            }
        }
        return true;
    }

    private function requirementsMetForTechnology(int $userId, int $techId, int $planetId): bool
    {
        $requirements = TemplateBuildRequired::active()->forBuild($techId)->get();
        foreach ($requirements as $req) {
            $required = $req->requiredBuild;
            if (!$required) {
                continue;
            }
            if ($required->isBuilding()) {
                $planetBuild = \App\Models\Planet\PlanetBuilding::where('planet_id', $planetId)
                    ->where('building_id', $req->required_build_id)
                    ->where('is_active', true)
                    ->first();
                if (!$planetBuild || $planetBuild->level < $req->required_level) {
                    return false;
                }
            } elseif ($required->isResearch()) {
                $userTech = \App\Models\User\UserTechnology::where('user_id', $userId)
                    ->where('technology_id', $req->required_build_id)
                    ->first();
                if (!$userTech || $userTech->level < $req->required_level) {
                    return false;
                }
            }
        }
        return true;
    }

    private function computeBuildTime(Planet $planet, TemplateBuild $build, int $level, string $targetType): int
    {
        // Même formule que l\'IU: base_time * 1.2^(level-1), puis appliquer bonus
        $baseTime = (int) $build->base_build_time;
        $calculatedTime = (int) round($baseTime * pow(1.2, max(0, $level - 1)));

        // Bonus de vitesse via bâtiments dédiés
        $bonus = TemplateBuildAdvantage::getBuildSpeedBonus($planet->id, $targetType);
        if ($bonus > 0) {
            $calculatedTime = max(1, (int) ($calculatedTime - $bonus));
        }
        return $calculatedTime;
    }

    private function mapTargetTypeForProduction(string $queueType): string
    {
        return match ($queueType) {
            Queue::TYPE_UNIT => TemplateBuildAdvantage::TARGET_UNIT,
            Queue::TYPE_DEFENSE => TemplateBuildAdvantage::TARGET_DEFENSE,
            Queue::TYPE_SHIP => TemplateBuildAdvantage::TARGET_SHIP,
            default => TemplateBuildAdvantage::TARGET_BUILD,
        };
    }

    private function mapProductionModel(string $queueType): array
    {
        return match ($queueType) {
            Queue::TYPE_UNIT => [\App\Models\Planet\PlanetUnit::class, 'unit_id'],
            Queue::TYPE_DEFENSE => [\App\Models\Planet\PlanetDefense::class, 'defense_id'],
            Queue::TYPE_SHIP => [\App\Models\Planet\PlanetShip::class, 'ship_id'],
            default => [null, null],
        };
    }

    private function ensurePlanetResources(Planet $planet): void
    {
        // Créer les PlanetResource manquantes pour chaque TemplateResource actif
        $templates = TemplateResource::active()->get();
        foreach ($templates as $tpl) {
            PlanetResource::firstOrCreate(
                [
                    'planet_id' => $planet->id,
                    'resource_id' => $tpl->id,
                ],
                [
                    'current_amount' => 25000,
                    'production_rate' => 100,
                    'last_update' => Carbon::now(),
                    'is_active' => true,
                ]
            );
        }
    }
}