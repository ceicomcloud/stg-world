<?php

namespace App\Jobs;

use App\Models\Planet\Planet;
use App\Models\Planet\PlanetMission;
use App\Models\Planet\PlanetResource;
use App\Models\Planet\PlanetShip;
use App\Models\Server\ServerConfig;
use App\Models\Template\TemplateBuild;
use App\Models\Template\TemplatePlanet;
use App\Models\Template\TemplateResource;
use App\Models\User\UserTechnology;
use App\Services\PrivateMessageService;
use App\Services\AttackService;
use App\Services\GalacticEventService;
use App\Traits\LogsUserActions;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ProcessPlanetMissionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LogsUserActions;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Private message service instance
     *
     * @var PrivateMessageService
     */
    private $messageService;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->messageService = new PrivateMessageService();
        // Traiter les missions prêtes en chunks pour éviter la surcharge mémoire
        $query = PlanetMission::whereIn('status', ['traveling', 'returning', 'collecting', 'exploring'])
            ->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->where('status', 'traveling')
                       ->where('arrival_time', '<=', Carbon::now());
                })->orWhere(function ($qq) {
                    $qq->where('status', 'returning')
                       ->where('return_time', '<=', Carbon::now());
                })->orWhere(function ($qq) {
                    $qq->where('status', 'collecting')
                       ->where('return_time', '<=', Carbon::now());
                })->orWhere(function ($qq) {
                    $qq->where('status', 'exploring')
                       ->where('return_time', '<=', Carbon::now());
                });
            })
            ->orderBy('id');

        $query->chunkById(500, function ($missions) {
            foreach ($missions as $mission) {
                try {
                // Vérifier si l'utilisateur est en mode vacances
                $user = $mission->user;
                if ($user && $user->isInVacationMode()) {
                    // Ignorer les missions des utilisateurs en mode vacances
                    continue;
                }
                
                // Vérifier si la planète de destination appartient à un utilisateur en mode vacances
                $targetPlanet = $mission->toPlanet;
                if ($targetPlanet && $targetPlanet->user && $targetPlanet->user->isInVacationMode() && 
                    in_array($mission->mission_type, ['attack', 'spy'])) {
                    
                    // Créer un message pour informer le joueur que sa mission a échoué
                    $this->messageService->createSystemMessage(
                        $mission->user,
                        'mission_failed',
                        'Mission échouée - Joueur en mode vacances',
                        "<div class='system-message-content'>
                            <p>🛡️ <strong>Mission échouée</strong></p>
                            <p>Votre mission de type {$mission->mission_type} vers la planète {$targetPlanet->name} ({$targetPlanet->coordinates}) a échoué.</p>
                            <p>Le joueur cible est en mode vacances et ne peut pas être attaqué.</p>
                            <p>Vos vaisseaux/unités sont en route de retour vers votre planète.</p>
                            <p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p>
                        </div>"
                    );
                    
                    // Mettre à jour la mission pour qu'elle retourne immédiatement
                    $mission->update([
                        'status' => 'returning',
                        'arrival_time' => now()->addSeconds(
                            PlanetMission::calculateMissionDuration(
                                $mission->to_system,
                                $mission->fromPlanet->system,
                                $this->getShipSpeedFromMission($mission),
                                $mission->user_id,
                                $mission->fromPlanet->galaxy,
                                $mission->to_galaxy
                            )
                        )
                    ]);
                    
                    continue;
                }
                
                if ($mission->isReadyToArrive() && $mission->status === 'traveling') {
                    $this->processMissionArrival($mission);
                } elseif ($mission->status === 'collecting' && $mission->return_time && Carbon::now()->gte($mission->return_time)) {
                    // Fin de la collecte: passer en retour avec gain calculé
                    $this->processExtract($mission);
                } elseif ($mission->status === 'exploring' && $mission->return_time && Carbon::now()->gte($mission->return_time)) {
                    // Fin de l'exploration: calcul des récompenses et retour
                    $this->finishExplore($mission);
                } elseif ($mission->isReadyToReturn() && $mission->status === 'returning') {
                    $this->processMissionReturn($mission);
                }
            } catch (\Exception $e) {
                // Journaliser l'erreur mais continuer le traitement des autres missions
                \Log::error('Erreur lors du traitement d\'une mission', [
                    'mission_id' => $mission->id ?? null,
                    'type' => $mission->mission_type ?? null,
                    'status' => $mission->status ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
            }
        }, 'id');
    }

    /**
     * Process mission arrival
     */
    private function processMissionArrival(PlanetMission $mission): void
    {
        // Vérifier si la planète de destination est protégée par un bouclier
        $targetPlanet = $mission->toPlanet;
        if ($targetPlanet && $targetPlanet->isShieldProtectionActive() && 
            in_array($mission->mission_type, ['attack', 'spy'])) {
            
            // Créer un message pour informer le joueur que sa mission a échoué
            $this->messageService->createSystemMessage(
                $mission->user,
                'mission_failed',
                'Mission échouée - Protection planétaire',
                "<div class='system-message-content'>
                    <p>🛡️ <strong>Mission échouée</strong></p>
                    <p>Votre mission de type {$mission->mission_type} vers la planète {$targetPlanet->name} ({$targetPlanet->coordinates}) a échoué.</p>
                    <p>La planète cible est protégée par un bouclier planétaire actif.</p>
                    <p>Vos vaisseaux/unités sont en route de retour vers votre planète.</p>
                    <p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p>
                </div>"
            );
            
            // Mettre à jour la mission pour qu'elle retourne immédiatement
            $mission->update([
                'status' => 'returning',
                'arrival_time' => now()->addSeconds(
                    PlanetMission::calculateMissionDuration(
                        $mission->to_system,
                        $mission->fromPlanet->system,
                        $this->getShipSpeedFromMission($mission),
                        $mission->user_id,
                        $mission->fromPlanet->galaxy,
                        $mission->to_galaxy
                    )
                )
            ]);
            
            return;
        }
        
        // Vérifier si la planète d'origine est protégée par un bouclier
        $fromPlanet = $mission->fromPlanet;
        if ($fromPlanet && $fromPlanet->isShieldProtectionActive() && 
            in_array($mission->mission_type, ['attack', 'spy'])) {
            
            // Créer un message pour informer le joueur que sa mission a échoué
            $this->messageService->createSystemMessage(
                $mission->user,
                'mission_failed',
                'Mission échouée - Protection planétaire',
                "<div class='system-message-content'>
                    <p>🛡️ <strong>Mission échouée</strong></p>
                    <p>Votre mission de type {$mission->mission_type} depuis la planète {$fromPlanet->name} ({$fromPlanet->coordinates}) a échoué.</p>
                    <p>Votre planète d'origine est protégée par un bouclier planétaire actif et ne peut pas lancer d'attaques.</p>
                    <p>Vos vaisseaux/unités sont en route de retour vers votre planète.</p>
                    <p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p>
                </div>"
            );
            
            // Mettre à jour la mission pour qu'elle retourne immédiatement
            $mission->update([
                'status' => 'returning',
                'arrival_time' => now()->addSeconds(
                    PlanetMission::calculateMissionDuration(
                        $mission->to_system,
                        $mission->fromPlanet->system,
                        $this->getShipSpeedFromMission($mission),
                        $mission->user_id,
                        $mission->fromPlanet->galaxy,
                        $mission->to_galaxy
                    )
                )
            ]);
            
            return;
        }
        
        switch ($mission->mission_type) {
            case 'colonize':
                $this->processColonization($mission);
                break;
            case 'transport':
                $this->processTransport($mission);
                break;
            case 'attack':
                $this->processAttack($mission);
                break;
            case 'spy':
                $this->processSpy($mission);
                break;
            case 'basement':
                $this->processBasement($mission);
                break;
            case 'extract':
                $this->startExtraction($mission);
                break;
            case 'explore':
                $this->startExploration($mission);
                break;
        }
    }

    /**
     * Process colonization mission
     */
    private function processColonization(PlanetMission $mission): void
    {
        // First, get the template planet for these coordinates
        $templatePlanet = TemplatePlanet::atCoordinates(
            $mission->to_galaxy,
            $mission->to_system,
            $mission->to_position
        )->first();

        if (!$templatePlanet) {
            // No template found for these coordinates, mission fails
            $result = ['success' => false, 'message' => 'Coordonnées invalides pour la colonisation'];
            $mission->update([
                'status' => 'returning',
                'return_time' => Carbon::now()->addMinutes(
                    PlanetMission::calculateMissionDuration(
                        $mission->to_system,
                        $mission->fromPlanet->system,
                        $this->getShipSpeedFromMission($mission),
                        $mission->user_id,
                        $mission->fromPlanet->galaxy,
                        $mission->to_galaxy
                    )
                ),
                'result' => $result
            ]);
            
            // Create colonization failure message
            $this->messageService->createColonizationMessage($mission, $result);
            return;
        }

        // Check if template planet is already occupied or if a user planet exists at these coordinates
        if ($templatePlanet->is_occupied || Planet::where('template_planet_id', $templatePlanet->id)->exists()) {
            // Position is occupied, mission fails
            $result = ['success' => false, 'message' => 'Position déjà occupée'];
            $mission->update([
                'status' => 'returning',
                'return_time' => Carbon::now()->addMinutes(
                    PlanetMission::calculateMissionDuration(
                        $mission->to_system,
                        $mission->fromPlanet->system,
                        $this->getShipSpeedFromMission($mission),
                        $mission->user_id,
                        $mission->fromPlanet->galaxy,
                        $mission->to_galaxy
                    )
                ),
                'result' => $result
            ]);
            
            // Create colonization failure message
            $this->messageService->createColonizationMessage($mission, $result);
            return;
        }

        // Check user planet limit
        $maxPlanets = ServerConfig::where('key', 'max_planets_per_user')->first()->value ?? 9;
        $userPlanetCount = Planet::where('user_id', $mission->user_id)->count();

        if ($userPlanetCount >= $maxPlanets) {
            // User has reached planet limit
            $result = ['success' => false, 'message' => 'Limite maximale de planètes atteinte'];
            $mission->update([
                'status' => 'returning',
                'return_time' => Carbon::now()->addMinutes(
                    PlanetMission::calculateMissionDuration(
                        $mission->to_system,
                        $mission->fromPlanet->system,
                        $this->getShipSpeedFromMission($mission),
                        $mission->user_id,
                        $mission->fromPlanet->galaxy,
                        $mission->to_galaxy
                    )
                ),
                'result' => $result
            ]);
            
            // Create colonization failure message
            $this->messageService->createColonizationMessage($mission, $result);
            return;
        }

        // Template planet already retrieved above, no need to fetch again

        // Mark template planet as occupied
        $templatePlanet->markAsOccupied();
        
        $newPlanet = Planet::create([
            'user_id' => $mission->user_id,
            'template_planet_id' => $templatePlanet->id,
            'name' => 'Colonie ' . $mission->to_galaxy . ':' . $mission->to_system . ':' . $mission->to_position,
            'description' => $this->generateRandomPlanetDescription(),
            'used_fields' => 0,
            'is_main_planet' => false,
            'is_active' => true
        ]);

        // Initialize planet resources, buildings, etc.
        $this->initializePlanet($newPlanet);

        // Mission successful, ships return
        $result = ['success' => true, 'message' => 'Planète colonisée avec succès', 'planet_id' => $newPlanet->id];
        $mission->update([
            'status' => 'returning',
            'to_planet_id' => $newPlanet->id,
            'return_time' => Carbon::now()->addMinutes(
                PlanetMission::calculateMissionDuration(
                    $mission->to_system,
                    $mission->fromPlanet->system,
                    $this->getShipSpeedFromMission($mission),
                    $mission->user_id,
                    $mission->fromPlanet->galaxy,
                    $mission->to_galaxy
                )
            ),
            'result' => $result
        ]);

        // Create colonization message
        $this->messageService->createColonizationMessage($mission, $result);
    }

    /**
     * Initialize a new planet with basic resources and buildings
     */
    private function initializePlanet(Planet $planet): void
    {
        $startingResources = ServerConfig::getStartingResources();
        
        // Récupérer les templates de ressources
        $resources = TemplateResource::whereIn('name', ['metal', 'crystal', 'deuterium'])->get()->keyBy('name');
        
        foreach ($startingResources as $resourceName => $amount) {
            if (isset($resources[$resourceName])) {
                PlanetResource::create([
                    'planet_id' => $planet->id,
                    'resource_id' => $resources[$resourceName]->id,
                    'current_amount' => $amount,
                    'max_storage' => $resources[$resourceName]->base_storage ?? 10000,
                    'production_rate' => $resources[$resourceName]->base_production ?? 0,
                    'last_update' => now(),
                    'is_active' => true
                ]);
            }
        }
    }

    /**
     * Process transport mission
     */
    private function processTransport(PlanetMission $mission): void
    {
        // Transport resources to target planet
        if ($mission->to_planet_id && $mission->resources) {
            $targetPlanet = Planet::find($mission->to_planet_id);
            if ($targetPlanet && $targetPlanet->user_id === $mission->user_id) {
                // Get all resources for the target planet
                $targetResources = $targetPlanet->resources;
                // Track surplus that cannot be stored and must be returned
                $surplus = [];
                
                // Process each resource type individually
                foreach ($targetResources as $resource) {
                    // Match resource_id with the mission resources array key
                    if (isset($mission->resources[$resource->resource_id])) {
                        $incoming = (int) $mission->resources[$resource->resource_id];
                        // Utiliser la capacité réelle via PlanetResource::getStorageCapacity()
                        $availableStorage = (int) $resource->getAvailableStorage();
                        $toStore = min($incoming, $availableStorage);
                        $overflow = max(0, $incoming - $toStore);

                        // Store only up to max storage
                        if ($toStore > 0) {
                            $resource->update([
                                'current_amount' => $resource->current_amount + $toStore
                            ]);
                        }

                        // Record any overflow to be returned to origin
                        if ($overflow > 0) {
                            $surplus[$resource->resource_id] = ($surplus[$resource->resource_id] ?? 0) + $overflow;
                        }
                    }
                }
                
                // If there is surplus, keep it on the mission to return to origin
                if (!empty($surplus)) {
                    $mission->resources = $surplus;
                } else {
                    // Otherwise, clear resources to avoid double-adding on return
                    $mission->resources = null;
                }
                $mission->save();
            }
        }

        // Ships return
        $resultMessage = 'Ressources transportées avec succès';
        if (!empty($mission->resources)) {
            $resultMessage .= ' (surplus renvoyé vers la planète d\'origine)';
        }
        $result = ['success' => true, 'message' => $resultMessage];
        $mission->update([
            'status' => 'returning',
            'return_time' => Carbon::now()->addMinutes(
                PlanetMission::calculateMissionDuration(
                    $mission->to_system,
                    $mission->fromPlanet->system,
                    $this->getShipSpeedFromMission($mission),
                    $mission->user_id,
                    $mission->fromPlanet->galaxy,
                    $mission->to_galaxy
                )
            ),
            // Ne pas mettre les ressources à null si surplus à renvoyer
            'resources' => $mission->resources ?? null,
            'result' => $result
        ]);

        // Create transport message
        $this->messageService->createTransportMessage($mission, $result);
    }

    /**
     * Process attack mission
     */
    private function processAttack(PlanetMission $mission): void
    {
        try {
            // Check if attacker can attack defender based on daily limit
            $attackService = new AttackService($this->messageService);
            $canAttack = $attackService->canAttackPlayer(
                $mission->user_id,
                $mission->toPlanet->user_id
            );

            if (!$canAttack['can_attack']) {
                // Attack blocked by daily limit
                $result = [
                    'success' => false,
                    'message' => $canAttack['message'],
                    'blocked_by_limit' => true
                ];
                
                $mission->update([
                    'status' => 'returning',
                    'return_time' => Carbon::now()->addMinutes(
                        PlanetMission::calculateMissionDuration(
                            $mission->to_system,
                            $mission->fromPlanet->system,
                            $this->getShipSpeedFromMission($mission),
                            $mission->user_id,
                            $mission->fromPlanet->galaxy,
                            $mission->to_galaxy
                        )
                    ),
                    'result' => $result
                ]);
                
                // Create attack message
                $this->messageService->createAttackMessage($mission, $result);
                return;
            }

            // Execute spatial combat
            $combatResult = $attackService->executeSpatialCombat($mission);
            
            // Create attack message with combat result
            $this->messageService->createAttackMessage($mission, $mission->result);
            
        } catch (\Exception $e) {
            // Handle combat errors
            $result = [
                'success' => false,
                'message' => 'Erreur lors du combat: ' . $e->getMessage(),
                'error' => true
            ];
            
            $mission->update([
                'status' => 'returning',
                'return_time' => Carbon::now()->addMinutes(
                    PlanetMission::calculateMissionDuration(
                        $mission->to_system,
                        $mission->fromPlanet->system,
                        $this->getShipSpeedFromMission($mission),
                        $mission->user_id,
                        $mission->fromPlanet->galaxy,
                        $mission->to_galaxy
                    )
                ),
                'result' => $result
            ]);
            
            // Create error message
            $this->messageService->createAttackMessage($mission, $result);
        }
    }

    /**
     * Process spy mission (placeholder)
     */
    private function processSpy(PlanetMission $mission): void
    {
        // Trouver la planète cible
        $targetPlanet = Planet::find($mission->to_planet_id);

        // Initialiser le résultat
        $result = [
            'success' => true,
            'message' => 'Mission d\'espionnage terminée '. $targetPlanet->name,
            'data' => []
        ];

        // Si la planète existe et est colonisée
        if ($targetPlanet) {
            // Vérifier si le joueur a la technologie d'espionnage tactique
            $espionnageTactique = UserTechnology::where('user_id', $mission->user_id)
                ->whereHas('technology', function($query) {
                    $query->where('name', 'espionnage_tactique');
                })
                ->first();

            $espionnageLevel = $espionnageTactique ? $espionnageTactique->level : 0;
            
            // Vérifier le niveau d'espionnage du défenseur
            $defenderEspionnageLevel = 0;
            if ($targetPlanet->user) {
                $defenderEspionnage = UserTechnology::where('user_id', $targetPlanet->user_id)
                    ->whereHas('technology', function($query) {
                        $query->where('name', 'espionnage_tactique');
                    })
                    ->first();
                $defenderEspionnageLevel = $defenderEspionnage ? $defenderEspionnage->level : 0;
            }

            // Récupérer les informations de base de la planète
            $result['data']['planet'] = [
                'name' => $targetPlanet->name,
                'coordinates' => $targetPlanet->coordinates,
                'owner' => $targetPlanet->user ? $targetPlanet->user->name : 'Planète non colonisée',
                'size' => $targetPlanet->size,
                'used_fields' => $targetPlanet->used_fields,
            ];

            // Récupérer les ressources (niveau 1+)
            if ($espionnageLevel >= 1 && $espionnageLevel > $defenderEspionnageLevel) {
                $resources = [];
                foreach ($targetPlanet->resources as $resource) {
                    $resourceName = $resource->resource->name;
                    $displayName = $resource->resource->display_name ?? ucfirst($resourceName);
                    $resources[$resourceName] = [
                        'amount' => $resource->current_amount,
                        'display_name' => $displayName
                    ];
                }
                $result['data']['resources'] = $resources;
            }

            // Récupérer les bâtiments (niveau 2+)
            if ($espionnageLevel >= 2 && $espionnageLevel > $defenderEspionnageLevel) {
                $buildings = [];
                foreach ($targetPlanet->buildings as $building) {
                    $buildings[$building->building->name] = $building->level;
                }
                $result['data']['buildings'] = $buildings;
            }

            // Récupérer les défenses (niveau 3+)
            if ($espionnageLevel >= 3 && $espionnageLevel > $defenderEspionnageLevel) {
                $defenses = [];
                foreach ($targetPlanet->defenses as $defense) {
                    if ($defense->quantity > 0) {
                        $defenses[$defense->defense->name] = $defense->quantity;
                    }
                }
                $result['data']['defenses'] = $defenses;
            }

            // Récupérer les vaisseaux (niveau 4+)
            if ($espionnageLevel >= 4 && $espionnageLevel > $defenderEspionnageLevel) {
                $ships = [];
                foreach ($targetPlanet->ships as $ship) {
                    if ($ship->quantity > 0) {
                        $ships[$ship->ship->name] = $ship->quantity;
                    }
                }
                $result['data']['ships'] = $ships;
            }

            // Récupérer les technologies (niveau 5+)
            if ($espionnageLevel >= 5 && $espionnageLevel > $defenderEspionnageLevel && $targetPlanet->user) {
                $technologies = [];
                $userTechnologies = UserTechnology::where('user_id', $targetPlanet->user_id)
                    ->where('level', '>', 0)
                    ->get();
                
                foreach ($userTechnologies as $tech) {
                    $technologies[$tech->technology->name] = $tech->level;
                }
                $result['data']['technologies'] = $technologies;
            }

            // Risque de détection basé sur le niveau d'espionnage et le nombre de vaisseaux espions
            $scoutQuantity = isset($mission->ships['scout_quantique']) ? $mission->ships['scout_quantique'] : 0;
            $detectionRisk = max(5, 40 - ($espionnageLevel * 5) - ($scoutQuantity * 2));
            
            // Déterminer si l'espionnage est détecté
            $isDetected = rand(1, 100) <= $detectionRisk;
            $result['data']['detection_risk'] = $detectionRisk . '%';
            
            if ($isDetected && $targetPlanet->user) {
                // Créer un message pour le propriétaire de la planète
                $this->messageService->createSystemNotification(
                    $targetPlanet->user,
                    "Activité d'espionnage détectée",
                    "<div class='system-message-content'>
                        <p>🚨 <strong>Alerte de Sécurité</strong></p>
                        <p>Une activité d'espionnage a été détectée sur votre planète {$targetPlanet->name} ({$targetPlanet->coordinates}).</p>
                        <p>L'espionnage provient des coordonnées {$mission->fromPlanet->coordinates}.</p>
                        <p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p>
                    </div>"
                );
                
                $result['message'] = 'Mission d\'espionnage terminée, mais votre flotte a été détectée!';
            }
        } else {
            $result['message'] = 'Aucune planète trouvée à ces coordonnées.';
        }

        // Mettre à jour la mission
        $mission->update([
            'status' => 'returning',
            'return_time' => Carbon::now()->addMinutes(
                PlanetMission::calculateMissionDuration(
                    $mission->to_system,
                    $mission->fromPlanet->system,
                    $this->getShipSpeedFromMission($mission),
                    $mission->user_id,
                    $mission->fromPlanet->galaxy,
                    $mission->to_galaxy
                )
            ),
            'result' => $result
        ]);

        // Créer le message d'espionnage
        $this->messageService->createSpyMessage($mission, $result);
    }

    /**
     * Generate a random planet description
     */
    private function generateRandomPlanetDescription(): string
    {
        $descriptions = [
            'Une planète rocheuse aux vastes plaines désertiques',
            'Un monde océanique aux eaux cristallines',
            'Une planète volcanique aux paysages spectaculaires',
            'Un monde glacé aux aurores boréales magnifiques',
            'Une planète forestière aux écosystèmes luxuriants',
            'Un monde aride aux formations rocheuses uniques',
            'Une planète tropicale aux jungles denses',
            'Un monde montagneux aux pics enneigés',
            'Une planète marécageuse aux brumes mystérieuses',
            'Un monde steppique aux horizons infinis',
            'Une planète aux canyons profonds et colorés',
            'Un monde aux geysers et sources chaudes',
            'Une planète aux cratères météoritiques anciens',
            'Un monde aux aurores polaires permanentes',
            'Une planète aux formations cristallines naturelles',
            'Un monde aux vents violents et tempêtes de sable',
            'Une planète aux archipels et îles flottantes',
            'Un monde aux cavernes souterraines étendues',
            'Une planète aux champs magnétiques intenses',
            'Un monde aux saisons extrêmes et contrastées'
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Process mission return
     */
    private function processMissionReturn(PlanetMission $mission): void
    {
        // Récupérer la planète d'origine
        $originPlanet = Planet::find($mission->from_planet_id);
        
        if ($originPlanet) {
            // Return ships to origin planet if the mission has ships
            if (isset($mission->ships) && !empty($mission->ships)) {
                foreach ($mission->ships as $shipId => $shipData) {
                    if (!is_numeric($shipId)) {
                        $templateBuild = \App\Models\Template\TemplateBuild::where('name', $shipId)->first();
                        $shipId = $templateBuild->id;
                        
                    }
                    
                    // Récupérer l'entrée de vaisseau existante ou en créer une nouvelle
                    $planetShip = PlanetShip::firstOrNew([
                        'planet_id' => $originPlanet->id,
                        'ship_id' => $shipId
                    ]);
                    
                    // Déterminer la quantité à ajouter en fonction du format des données
                    $quantityToAdd = is_array($shipData) ? ($shipData['quantity'] ?? 0) : $shipData;
                    
                    // Ajouter les vaisseaux retournés à la flotte existante
                    $planetShip->quantity = ($planetShip->quantity ?? 0) + $quantityToAdd;
                    $planetShip->is_active = true;
                    $planetShip->save();
                }
            }
            
            // Return resources to origin planet if the mission has resources
            // This applies to missions that were cancelled or failed
            if (isset($mission->resources) && !empty($mission->resources)) {
                
                // Get all resources for the origin planet
                $originResources = $originPlanet->resources;
                
                // Process each resource type individually
                foreach ($originResources as $resource) {
                    // Match resource_id with the mission resources array key
                    if (isset($mission->resources[$resource->resource_id])) {
                        // Update the specific resource with the returned amount
                        $resource->update([
                            'current_amount' => $resource->current_amount + $mission->resources[$resource->resource_id]
                        ]);
                    }
                }
            }
        }
        
        // Create return message
        $this->messageService->createReturnMessage($mission);
        
        $mission->update([
            'status' => 'completed'
        ]);
    }

    /**
     * Get ship speed from mission
     * 
     * @param PlanetMission $mission
     * @return int
     */
    private function getShipSpeedFromMission(PlanetMission $mission): int
    {
        // Valeur par défaut si aucune information n'est disponible
        $defaultSpeed = 100;
        
        // Si la mission contient des informations sur les vaisseaux, utiliser leur vitesse
        if (isset($mission->ships) && !empty($mission->ships)) {
            $slowestSpeed = PHP_INT_MAX;
            
            foreach ($mission->ships as $shipId => $shipData) {
                $speed = null;
                
                // Nouveau format détaillé avec informations complètes
                if (is_array($shipData) && isset($shipData['speed'])) {
                    $speed = $shipData['speed'];
                }
                // Ancien format simple - récupérer depuis la base de données
                else {
                    $ship = \App\Models\Template\TemplateBuild::find($shipId);
                    if ($ship) {
                        $speed = $ship->speed;
                    }
                }
                
                // Garder la vitesse la plus lente
                if ($speed && $speed < $slowestSpeed) {
                    $slowestSpeed = $speed;
                }
            }
            
            // Retourner la vitesse la plus lente trouvée, ou la valeur par défaut
            return $slowestSpeed !== PHP_INT_MAX ? $slowestSpeed : $defaultSpeed;
        }
        
        return $defaultSpeed;
    }

    /**
     * Process basement mission
     */
    private function processBasement(PlanetMission $mission): void
    {
        // Trouver la planète cible
        $targetPlanet = Planet::find($mission->to_planet_id);

        // Initialiser le résultat
        $result = [
            'success' => true,
            'message' => 'Mission de transfert terminée sur ' . ($targetPlanet ? $targetPlanet->name : 'planète inconnue'),
            'data' => [],
            'transferred_units' => [],
            'transferred_ships' => []
        ];

        // Si la planète existe et appartient au même joueur
        if ($targetPlanet && $targetPlanet->user_id === $mission->user_id) {
            // Transférer les unités et vaisseaux vers la planète cible
            // Note: mission->ships contient à la fois les unités et les vaisseaux avec leurs détails
            if (isset($mission->ships) && !empty($mission->ships)) {
                foreach ($mission->ships as $itemId => $itemData) {
                    // Gérer les différents formats de données (ancien format simple ou nouveau format détaillé)
                    if (is_array($itemData)) {
                        $quantity = $itemData['quantity'] ?? 0;
                        $type = $itemData['type'] ?? null;
                        $templateId = $itemId;
                        $name = $itemData['name'] ?? 'Inconnu';
                    } else {
                        // Format ancien (rétrocompatibilité)
                        $quantity = $itemData;
                        $type = null;
                        $templateId = null;
                        $name = 'Inconnu';
                    }
                    
                    if ($quantity > 0) {
                        if ($type === 'unit' || (!$type && \App\Models\Planet\PlanetUnit::find($itemId))) {
                            // C'est une unité
                            if ($templateId) {
                                // Nouveau format avec template_id
                                $targetPlanetUnit = \App\Models\Planet\PlanetUnit::firstOrNew([
                                    'planet_id' => $targetPlanet->id,
                                    'unit_id' => $templateId
                                ]);
                            } else {
                                // Format ancien - récupérer le template_id depuis PlanetUnit
                                $planetUnit = \App\Models\Planet\PlanetUnit::find($itemId);
                                if (!$planetUnit) continue;
                                
                                $targetPlanetUnit = \App\Models\Planet\PlanetUnit::firstOrNew([
                                    'planet_id' => $targetPlanet->id,
                                    'unit_id' => $planetUnit->unit_id
                                ]);
                                $name = $planetUnit->unit->label ?? 'Unité inconnue';
                            }
                            
                            // Ajouter les unités transférées
                            $targetPlanetUnit->quantity = ($targetPlanetUnit->quantity ?? 0) + $quantity;
                            $targetPlanetUnit->is_active = true;
                            $targetPlanetUnit->save();
                            
                            $result['transferred_units'][$itemId] = [
                                'quantity' => $quantity,
                                'name' => $name
                            ];
                        } elseif ($type === 'ship' || (!$type && \App\Models\Planet\PlanetShip::find($itemId))) {
                            // C'est un vaisseau
                            if ($templateId) {
                                // Nouveau format avec template_id
                                $targetPlanetShip = \App\Models\Planet\PlanetShip::firstOrNew([
                                    'planet_id' => $targetPlanet->id,
                                    'ship_id' => $templateId
                                ]);
                            } else {
                                // Format ancien - récupérer le template_id depuis PlanetShip
                                $planetShip = \App\Models\Planet\PlanetShip::find($itemId);
                                if (!$planetShip) continue;
                                
                                $targetPlanetShip = \App\Models\Planet\PlanetShip::firstOrNew([
                                    'planet_id' => $targetPlanet->id,
                                    'ship_id' => $planetShip->ship_id
                                ]);
                                $name = $planetShip->ship->label ?? 'Vaisseau inconnu';
                            }
                            
                            // Ajouter les vaisseaux transférés
                            $targetPlanetShip->quantity = ($targetPlanetShip->quantity ?? 0) + $quantity;
                            $targetPlanetShip->is_active = true;
                            $targetPlanetShip->save();
                            
                            $result['transferred_ships'][$itemId] = [
                                'quantity' => $quantity,
                                'name' => $name
                            ];
                        }
                    }
                }
            }
            
            // Logique spécifique pour la mission basement
            $result['data']['planet'] = [
                'name' => $targetPlanet->name,
                'coordinates' => $targetPlanet->coordinates,
                'basement_explored' => true,
                'units_transferred' => !empty($result['transferred_units']),
                'ships_transferred' => !empty($result['transferred_ships'])
            ];

            // Ajouter des ressources trouvées ou autres découvertes
            $result['data']['discoveries'] = [
                'underground_resources' => true,
                'hidden_structures' => false
            ];
            
            // Mettre à jour le message de succès
            $transferredItems = [];
            if (!empty($result['transferred_units'])) {
                $unitCount = array_sum(array_column($result['transferred_units'], 'quantity'));
                $transferredItems[] = $unitCount . ' unité(s)';
            }
            if (!empty($result['transferred_ships'])) {
                $shipCount = array_sum(array_column($result['transferred_ships'], 'quantity'));
                $transferredItems[] = $shipCount . ' vaisseau(x)';
            }
            
            if (!empty($transferredItems)) {
                $result['message'] = 'Mission de terminer terminée avec transfert de ' . implode(' et ', $transferredItems) . ' vers ' . $targetPlanet->name;
            }
        } else {
            $result['success'] = false;
            $result['message'] = $targetPlanet ? 'Planète cible ne vous appartient pas' : 'Planète cible introuvable';
        }

        // Mettre à jour la mission pour le retour (sans les unités/vaisseaux transférés)
        $mission->update([
            'status' => 'returning',
            'return_time' => Carbon::now()->addMinutes(
                PlanetMission::calculateMissionDuration(
                    $mission->to_system,
                    $mission->fromPlanet->system,
                    $this->getShipSpeedFromMission($mission),
                    $mission->user_id,
                    $mission->fromPlanet->galaxy,
                    $mission->to_galaxy
                )
            ),
            'ships' => null, // Mettre les vaisseaux à null car ils ont été transférés
            'result' => $result
        ]);

        // Créer un message pour informer le joueur
        $this->messageService->createBasementMessage($mission, $result);
    }

    /**
     * Process extract mission
     */
    private function processExtract(PlanetMission $mission): void
    {
        // Cette méthode termine la collecte et prépare le retour avec ressources
        // Passage à un système à taux/minute influencé par la technologie "commandement_strategique"
        $targetTemplatePlanet = \App\Models\Template\TemplatePlanet::where([
            'galaxy' => $mission->to_galaxy,
            'system' => $mission->to_system,
            'position' => $mission->to_position
        ])->first();

        $planetName = $targetTemplatePlanet ? $targetTemplatePlanet->name : 'planète inconnue';

        $result = [
            'success' => true,
            'message' => 'Mission d\'extraction terminée sur ' . $planetName,
            'data' => [],
            'extracted_resources' => []
        ];

        // Calcul de la capacité totale de cargo
        $normalizedShips = $this->normalizeUnitsPayload($mission->ships ?? []);
        $totalCapacity = 0;
        foreach ($normalizedShips as $templateId => $quantity) {
            $totalCapacity += $this->getCargoCapacityForTemplate((int) $templateId, $mission->from_planet_id) * (int) $quantity;
        }

        // Récupérer les taux/min stockés au démarrage, sinon calculer un taux par défaut
        $rates = (array) data_get($mission->result, 'extraction_rates_per_min', []);
        $minutes = (int) data_get($mission->result, 'extraction_duration_minutes', 0);

        if (empty($rates)) {
            // Fallback: définir des taux basés sur la capacité si non présents
            $baseRate = max(1, (int) floor($totalCapacity * 0.004)); // 0.4% de la capacité par minute
            $effMultiplier = $this->getMissionResourceEfficiencyMultiplier($mission->user_id);
            $variance = random_int(90, 110) / 100.0;
            $rates = [
                1 => (int) floor($baseRate * 0.5 * $effMultiplier * $variance),
                2 => (int) floor($baseRate * 0.3 * $effMultiplier * $variance),
                3 => (int) floor($baseRate * 0.2 * $effMultiplier * $variance),
            ];
            $minutes = $minutes > 0 ? $minutes : 60; // défaut 1h si inconnu
        }

        // Calculer les ressources extraites: taux/minute * minutes, puis cap par capacité totale
        $extracted = [];
        $sum = 0;
        foreach ([1,2,3] as $rid) {
            $amount = (int) floor(($rates[$rid] ?? 0) * max(0, $minutes));
            if ($amount > 0) {
                $extracted[$rid] = $amount;
                $sum += $amount;
            }
        }

        if ($sum > $totalCapacity && $sum > 0) {
            $scale = $totalCapacity / $sum;
            foreach ($extracted as $rid => $amount) {
                $extracted[$rid] = (int) floor($amount * $scale);
            }
        }

        // Décrire la planète cible
        if ($targetTemplatePlanet) {
            $result['data']['planet'] = [
                'name' => $targetTemplatePlanet->name,
                'coordinates' => $targetTemplatePlanet->coordinates,
                'extraction_completed' => true
            ];
        } else {
            $result['success'] = false;
            $result['message'] = 'TemplatePlanet cible introuvable';
        }

        // Appliquer l’évènement pirate_ambush (réduction et possible retard)
        $evt = app(\App\Services\GalacticEventService::class)->applyExtractionEvent($mission, $extracted);
        $extracted = (array) ($evt['resources'] ?? $extracted);
        $eventDelay = (int) ($evt['delay_minutes'] ?? 0);
        if (!empty($evt['notes'])) {
            $result['event_notes'] = $evt['notes'];
        }

        $result['extracted_resources'] = $extracted;

        // Mettre à jour la mission pour le retour avec les ressources extraites
        $mission->update([
            'status' => 'returning',
            'return_time' => Carbon::now()->addMinutes(
                PlanetMission::calculateMissionDuration(
                    $mission->to_system,
                    $mission->fromPlanet->system,
                    $this->getShipSpeedFromMission($mission),
                    $mission->user_id,
                    $mission->fromPlanet->galaxy,
                    $mission->to_galaxy
                )
            )->addMinutes($eventDelay),
            'resources' => $extracted,
            'result' => $result
        ]);

        // Statistiques & message
        app(\App\Services\EventService::class)->recordExtraction($mission->user_id);
        $this->messageService->createExtractMessage($mission, $result);
    }

    /**
     * Démarrer la collecte après l'arrivée: fixer la durée et le statut
     */
    private function startExtraction(PlanetMission $mission): void
    {
        // Durée d'extraction aléatoire entre 1h et 12h (en minutes)
        $hours = random_int(1, 12);
        $extractionMinutes = $hours * 60;

        // Calcul de la capacité totale de cargo
        $normalizedShips = $this->normalizeUnitsPayload($mission->ships ?? []);
        $totalCapacity = 0;
        foreach ($normalizedShips as $templateId => $quantity) {
            $totalCapacity += $this->getCargoCapacityForTemplate((int) $templateId, $mission->from_planet_id) * (int) $quantity;
        }

        // Déterminer les taux/minute influencés par la technologie (commandement_strategique)
        $baseRate = max(1, (int) floor($totalCapacity * 0.004)); // 0.4% de la capacité par minute
        $effMultiplier = $this->getMissionResourceEfficiencyMultiplier($mission->user_id);
        $variance = random_int(90, 110) / 100.0;
        $extractionRates = [
            1 => (int) floor($baseRate * 0.5 * $effMultiplier * $variance), // Métal
            2 => (int) floor($baseRate * 0.3 * $effMultiplier * $variance), // Cristal
            3 => (int) floor($baseRate * 0.2 * $effMultiplier * $variance), // Deutérium
        ];

        // Passer la mission en statut 'collecting' et planifier la fin de collecte via return_time
        $mission->update([
            'status' => 'collecting',
            'return_time' => Carbon::now()->addMinutes($extractionMinutes),
            'result' => array_merge((array) ($mission->result ?? []), [
                'message' => "Collecte d'extraction en cours",
                'extraction_duration_minutes' => $extractionMinutes,
                'extraction_rates_per_min' => $extractionRates,
                'extraction_total_cargo' => $totalCapacity,
                'extraction_rate_multiplier' => $effMultiplier,
                'extraction_rate_variance' => $variance,
            ])
        ]);

        // Message d'information simple
        $coords = "{$mission->to_galaxy}:{$mission->to_system}:{$mission->to_position}";
        $this->messageService->createSystemMessage(
            $mission->user,
            'extract',
            "Collecte commencée",
            "<div class='system-message-content'><p>⛏️ <strong>Collecte d'extraction démarrée</strong></p><p>📍 <strong>Coordonnées:</strong> {$coords}</p><p>⏱️ <strong>Durée:</strong> " . gmdate('H:i:s', $extractionMinutes * 60) . "</p><p>📦 <strong>Taux/min:</strong> Métal {$extractionRates[1]} | Cristal {$extractionRates[2]} | Deutérium {$extractionRates[3]}</p><p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p></div>"
        );
    }

    /**
     * Démarrer l'exploration après l'arrivée: fixer durée et statut
     */
    private function startExploration(PlanetMission $mission): void
    {
        // Durée aléatoire entre 1h et 6h
        $hours = random_int(1, 6);
        $exploreMinutes = $hours * 60;

        // Capacité totale de cargo
        $normalizedShips = $this->normalizeUnitsPayload($mission->ships ?? []);
        $totalCapacity = 0;
        foreach ($normalizedShips as $templateId => $quantity) {
            $totalCapacity += $this->getCargoCapacityForTemplate((int) $templateId, $mission->from_planet_id) * (int) $quantity;
        }

        // Taux/minute pour exploration (un peu plus lent que l'extraction)
        $baseRate = max(1, (int) floor($totalCapacity * 0.003)); // 0.3% de la capacité par minute
        $effMultiplier = $this->getMissionResourceEfficiencyMultiplier($mission->user_id);
        $variance = random_int(85, 115) / 100.0;
        // Exclure les points de recherche: ne garder que métal, cristal, deutérium
        $resources = TemplateResource::active()
            ->whereIn('name', ['metal', 'crystal', 'deuterium'])
            ->get();
        $splits = $this->randomSplits(max(1, $resources->count()), $baseRate);
        $exploreRates = [];
        foreach ($resources as $idx => $res) {
            $exploreRates[$res->id] = (int) floor(($splits[$idx] ?? 0) * $effMultiplier * $variance);
        }

        $mission->update([
            'status' => 'exploring',
            'return_time' => Carbon::now()->addMinutes($exploreMinutes),
            'result' => array_merge((array) ($mission->result ?? []), [
                'message' => "Exploration en cours",
                'exploration_duration_minutes' => $exploreMinutes,
                'exploration_rates_per_min' => $exploreRates,
                'exploration_total_cargo' => $totalCapacity,
                'exploration_rate_multiplier' => $effMultiplier,
                'exploration_rate_variance' => $variance,
            ])
        ]);

        $coords = "{$mission->to_galaxy}:{$mission->to_system}:{$mission->to_position}";
        $this->messageService->createSystemMessage(
            $mission->user,
            'explore',
            'Exploration commencée',
            "<div class='system-message-content'><p>🧭 <strong>Exploration démarrée</strong></p><p>📍 <strong>Coordonnées:</strong> {$coords}</p><p>⏱️ <strong>Durée:</strong> " . gmdate('H:i:s', $exploreMinutes * 60) . "</p><p>📦 <strong>Taux/min:</strong> " . implode(', ', array_map(function($rid, $rate) { return $rid . ' ' . $rate; }, array_keys($exploreRates), array_values($exploreRates))) . "</p><p>📅 <strong>Date:</strong> " . Carbon::now()->format('d/m/Y H:i:s') . "</p></div>"
        );
    }

    /**
     * Terminer l'exploration: calculer les récompenses et passer en retour
     */
    private function finishExplore(PlanetMission $mission): void
    {
        // Target is a template planet (uncolonized allowed). Use coordinates.
        $targetTemplatePlanet = \App\Models\Template\TemplatePlanet::where([
            'galaxy' => $mission->to_galaxy,
            'system' => $mission->to_system,
            'position' => $mission->to_position
        ])->first();

        $planetName = $targetTemplatePlanet ? $targetTemplatePlanet->name : 'planète inconnue';
        $result = [
            'success' => true,
            'message' => "Mission d'exploration terminée sur " . $planetName,
            'data' => [],
            'awarded_items' => []
        ];

        if ($targetTemplatePlanet) {
            $result['data']['planet'] = [
                'name' => $targetTemplatePlanet->name,
                'coordinates' => $targetTemplatePlanet->coordinates,
                'exploration_completed' => true
            ];

            // Award a random inventory pack
            $reward = \App\Models\Template\TemplateInventory::where('type', 'pack')
                ->inRandomOrder()
                ->first();

            if ($reward) {
                $coords = "{$mission->to_galaxy}:{$mission->to_system}:{$mission->to_position}";
                $mission->user->giveInventoryItem($reward->key, 1, "Exploration sur {$planetName} ({$coords})");
                $result['awarded_items'][] = [
                    'key' => $reward->key,
                    'name' => $reward->name,
                    'quantity' => 1,
                ];
            }

            // Récompenses en ressources basées sur un taux/minute
            $normalizedShips = $this->normalizeUnitsPayload($mission->ships ?? []);
            $totalCapacity = 0;
            foreach ($normalizedShips as $templateId => $quantity) {
                $totalCapacity += $this->getCargoCapacityForTemplate((int) $templateId, $mission->from_planet_id) * (int) $quantity;
            }

            $exploreRates = (array) data_get($mission->result, 'exploration_rates_per_min', []);
            $exploreMinutes = (int) data_get($mission->result, 'exploration_duration_minutes', 0);

            // Si pas de taux stockés, définir par défaut
            if (empty($exploreRates)) {
                // Exclure les points de recherche: ne garder que métal, cristal, deutérium
                $resources = TemplateResource::active()
                    ->whereIn('name', ['metal', 'crystal', 'deuterium'])
                    ->get();
                $baseRate = max(1, (int) floor($totalCapacity * 0.003));
                $effMultiplier = $this->getMissionResourceEfficiencyMultiplier($mission->user_id);
                $variance = random_int(85, 115) / 100.0;
                $splits = $this->randomSplits(max(1, $resources->count()), $baseRate);
                foreach ($resources as $idx => $res) {
                    $exploreRates[$res->id] = (int) floor(($splits[$idx] ?? 0) * $effMultiplier * $variance);
                }
                $exploreMinutes = $exploreMinutes > 0 ? $exploreMinutes : 60;
            } else {
                // S'assurer que seules les ressources autorisées sont utilisées
                $resources = TemplateResource::active()
                    ->whereIn('name', ['metal', 'crystal', 'deuterium'])
                    ->get();
                // Filtrer les taux existants pour ne garder que les IDs autorisés
                $allowedIds = collect($resources)->pluck('id')->all();
                $exploreRates = array_filter(
                    $exploreRates,
                    function ($rate, $resId) use ($allowedIds) {
                        return in_array((int)$resId, $allowedIds, true);
                    },
                    ARRAY_FILTER_USE_BOTH
                );
            }

            // Calculer le loot: taux/min * minutes, puis cap par capacité
            $loot = [];
            $sum = 0;
            foreach ($exploreRates as $resId => $rate) {
                $amount = (int) floor($rate * max(0, $exploreMinutes));
                if ($amount > 0) {
                    $loot[$resId] = $amount;
                    $sum += $amount;
                }
            }
            if ($sum > $totalCapacity && $sum > 0) {
                $scale = $totalCapacity / $sum;
                foreach ($loot as $resId => $amount) {
                    $loot[$resId] = (int) floor($amount * $scale);
                }
            }

            // Appliquer l’évènement pirate_ambush (réduction et possible retard)
            $evt = app(\App\Services\GalacticEventService::class)->applyExplorationEvent($mission, $loot);
            $loot = (array) ($evt['resources'] ?? $loot);
            $eventDelay = (int) ($evt['delay_minutes'] ?? 0);
            if (!empty($evt['notes'])) {
                $result['event_notes'] = $evt['notes'];
            }

            // Attach resources to mission for return processing
            if (!empty($loot)) {
                $result['explored_resources'] = [];
                foreach ($loot as $resId => $amount) {
                    $tpl = $resources->firstWhere('id', $resId);
                    $result['explored_resources'][] = [
                        'resource_id' => $resId,
                        'name' => $tpl?->display_name ?? $tpl?->name ?? 'ressource',
                        'amount' => $amount,
                    ];
                }
            }
        } else {
            $result['success'] = false;
            $result['message'] = 'TemplatePlanet cible introuvable';
        }

        // Update mission to returning (carry resources found during exploration)
        $mission->update([
            'status' => 'returning',
            'return_time' => Carbon::now()->addMinutes(
                PlanetMission::calculateMissionDuration(
                    $mission->to_system,
                    $mission->fromPlanet->system,
                    $this->getShipSpeedFromMission($mission),
                    $mission->user_id,
                    $mission->fromPlanet->galaxy,
                    $mission->to_galaxy
                )
            )->addMinutes($eventDelay ?? 0),
            'resources' => $loot ?? [],
            'result' => $result
        ]);

        // Incrémenter la statistique d'événement pour exploration
        app(\App\Services\EventService::class)->recordExploration($mission->user_id);

        // Notify user
        $this->messageService->createExploreMessage($mission, $result);
    }

    /**
     * Calculer le multiplicateur d'efficacité des ressources pour les missions
     * basé sur la technologie "commandement_strategique" et ses avantages.
     */
    protected function getMissionResourceEfficiencyMultiplier(int $userId): float
    {
        try {
            $advantages = \App\Models\Template\TemplateBuildAdvantage::join('user_technologies', 'template_build_advantages.build_id', '=', 'user_technologies.technology_id')
                ->where('user_technologies.user_id', $userId)
                ->where('template_build_advantages.advantage_type', \App\Models\Template\TemplateBuildAdvantage::TYPE_RESOURCE_EFFICIENCY)
                ->where('template_build_advantages.target_type', \App\Models\Template\TemplateBuildAdvantage::TARGET_MISSION)
                ->where('template_build_advantages.is_active', true)
                ->where('user_technologies.is_active', true)
                ->where('user_technologies.level', '>', 0)
                ->get(['template_build_advantages.*', 'user_technologies.level']);

            $bonus = 0.0;
            foreach ($advantages as $adv) {
                // Retourne une valeur pour le niveau, supposée additive au multiplicateur
                $bonus += (float) $adv->calculateValueForLevel($adv->level);
            }
            return 1.0 + max(0.0, $bonus);
        } catch (\Throwable $e) {
            // En cas d'erreur, pas de bonus
            return 1.0;
        }
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
                if (is_numeric($key)) {
                    $normalized[(int) $key] = ($normalized[(int) $key] ?? 0) + (int) $value;
                } else {
                    $template = \App\Models\Template\TemplateBuild::where('name', $key)->first();
                    if ($template) {
                        $normalized[$template->id] = ($normalized[$template->id] ?? 0) + (int) $value;
                    }
                }
            }
        }
        return $normalized;
    }

    /**
     * Generate random positive integer splits that sum to total.
     */
    protected function randomSplits(int $parts, int $total): array
    {
        if ($parts <= 0 || $total <= 0) return array_fill(0, max(0, $parts), 0);
        // Generate random weights
        $weights = [];
        for ($i = 0; $i < $parts; $i++) {
            $weights[$i] = random_int(1, 100);
        }
        $sum = array_sum($weights);
        $splits = [];
        $allocated = 0;
        for ($i = 0; $i < $parts; $i++) {
            // Floor to keep integers, adjust last to match total
            if ($i === $parts - 1) {
                $splits[$i] = max(0, $total - $allocated);
            } else {
                $portion = (int) floor($total * ($weights[$i] / $sum));
                $splits[$i] = $portion;
                $allocated += $portion;
            }
        }
        return $splits;
    }

    /**
     * Get cargo capacity for a template unit/ship with faction bonus applied
     */
    protected function getCargoCapacityForTemplate(int $templateId, int $planetId): int
    {
        $template = \App\Models\Template\TemplateBuild::find($templateId);
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
}