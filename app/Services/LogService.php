<?php

namespace App\Services;

use App\Models\User;
use App\Models\User\UserLog;
use App\Models\Planet\Planet;
use App\Models\Planet\PlanetBunkerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LogService
{
    /**
     * Créer un log d'action utilisateur
     */
    public function log(
        int $userId,
        string $actionType,
        string $actionCategory,
        string $description,
        array $metadata = [],
        ?int $planetId = null,
        ?int $targetUserId = null,
        string $severity = UserLog::SEVERITY_INFO,
        ?Request $request = null
    ): UserLog {
        $logData = [
            'user_id' => $userId,
            'action_type' => $actionType,
            'action_category' => $actionCategory,
            'description' => $description,
            'metadata' => $metadata,
            'planet_id' => $planetId,
            'target_user_id' => $targetUserId,
            'severity' => $severity,
        ];

        // Ajouter les informations de la requête si disponible
        if ($request) {
            $logData['ip_address'] = $request->ip();
            $logData['user_agent'] = $request->userAgent();
        }

        return UserLog::create($logData);
    }

    /**
     * Log automatique avec l'utilisateur connecté
     */
    public function logForCurrentUser(
        string $actionType,
        string $actionCategory,
        string $description,
        array $metadata = [],
        ?int $planetId = null,
        ?int $targetUserId = null,
        string $severity = UserLog::SEVERITY_INFO,
        ?Request $request = null
    ): ?UserLog {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        return $this->log(
            $user->id,
            $actionType,
            $actionCategory,
            $description,
            $metadata,
            $planetId,
            $targetUserId,
            $severity,
            $request
        );
    }

    // ==================== ALIAS POUR AUTHENTIFICATION ====================

    /**
     * Log de connexion
     */
    public function logLogin(int $userId, ?Request $request = null): UserLog
    {
        return $this->log(
            $userId,
            UserLog::ACTION_LOGIN,
            UserLog::CATEGORY_AUTH,
            'Connexion de l\'utilisateur',
            ['login_time' => Carbon::now()->toISOString()],
            null,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    /**
     * Log d'inscription
     */
    public function logRegister(int $userId, ?Request $request = null): UserLog
    {
        return $this->log(
            $userId,
            'register',
            UserLog::CATEGORY_AUTH,
            'Inscription de l\'utilisateur',
            ['register_time' => Carbon::now()->toISOString()],
            null,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    /**
     * Log de transaction de bunker (stockage)
     */
    public function logBunkerStore(
        int $userId,
        int $planetId,
        int $bunkerId,
        int $resourceId,
        int $amount,
        int $bunkerAmountBefore,
        int $bunkerAmountAfter,
        int $planetAmountBefore,
        int $planetAmountAfter,
        ?Request $request = null
    ): PlanetBunkerTransaction {
        // Créer l'enregistrement de transaction
        $transaction = PlanetBunkerTransaction::create([
            'planet_id' => $planetId,
            'bunker_id' => $bunkerId,
            'resource_id' => $resourceId,
            'user_id' => $userId,
            'transaction_type' => PlanetBunkerTransaction::TYPE_STORE,
            'amount' => $amount,
            'bunker_amount_before' => $bunkerAmountBefore,
            'bunker_amount_after' => $bunkerAmountAfter,
            'planet_amount_before' => $planetAmountBefore,
            'planet_amount_after' => $planetAmountAfter,
        ]);
        
        // Créer également un log utilisateur
        $this->log(
            $userId,
            'bunker_store',
            'resource',
            'Stockage de ressources dans le bunker',
            [
                'resource_id' => $resourceId,
                'amount' => $amount,
                'transaction_id' => $transaction->id
            ],
            $planetId,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
        
        return $transaction;
    }
    
    /**
     * Log de transaction de bunker (récupération)
     */
    public function logBunkerRetrieve(
        int $userId,
        int $planetId,
        int $bunkerId,
        int $resourceId,
        int $amount,
        int $bunkerAmountBefore,
        int $bunkerAmountAfter,
        int $planetAmountBefore,
        int $planetAmountAfter,
        ?Request $request = null
    ): PlanetBunkerTransaction {
        // Créer l'enregistrement de transaction
        $transaction = PlanetBunkerTransaction::create([
            'planet_id' => $planetId,
            'bunker_id' => $bunkerId,
            'resource_id' => $resourceId,
            'user_id' => $userId,
            'transaction_type' => PlanetBunkerTransaction::TYPE_RETRIEVE,
            'amount' => $amount,
            'bunker_amount_before' => $bunkerAmountBefore,
            'bunker_amount_after' => $bunkerAmountAfter,
            'planet_amount_before' => $planetAmountBefore,
            'planet_amount_after' => $planetAmountAfter,
        ]);
        
        // Créer également un log utilisateur
        $this->log(
            $userId,
            'bunker_retrieve',
            'resource',
            'Récupération de ressources du bunker',
            [
                'resource_id' => $resourceId,
                'amount' => $amount,
                'transaction_id' => $transaction->id
            ],
            $planetId,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
        
        return $transaction;
    }
    
    /**
     * Log de déconnexion
     */
    public function logLogout(int $userId, ?Request $request = null): UserLog
    {
        return $this->log(
            $userId,
            UserLog::ACTION_LOGOUT,
            UserLog::CATEGORY_AUTH,
            'Déconnexion de l\'utilisateur',
            ['logout_time' => Carbon::now()->toISOString()],
            null,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    // ==================== ALIAS POUR RESSOURCES ====================

    /**
     * Log de dépense de ressources
     */
    public function logResourceSpend(
        int $userId,
        array $resources,
        string $reason,
        ?int $planetId = null,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_RESOURCE_SPEND,
            UserLog::CATEGORY_RESOURCE,
            'Dépense de ressources: {reason}',
            [
                'resources' => $resources,
                'reason' => $reason,
                'total_value' => array_sum($resources)
            ],
            $planetId,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    /**
     * Log de gain de ressources
     */
    public function logResourceGain(
        int $userId,
        array $resources,
        string $source,
        ?int $planetId = null,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_RESOURCE_GAIN,
            UserLog::CATEGORY_RESOURCE,
            'Gain de ressources: {source}',
            [
                'resources' => $resources,
                'source' => $source,
                'total_value' => array_sum($resources)
            ],
            $planetId,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    // ==================== ALIAS POUR BÂTIMENTS ====================

    /**
     * Log d'achat de bâtiment
     */
    public function logBuildingPurchase(
        int $userId,
        string $buildingName,
        int $buildingId,
        array $cost,
        ?int $planetId = null,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_BUILDING_PURCHASE,
            UserLog::CATEGORY_BUILDING,
            'Achat du bâtiment: {building_name}',
            [
                'building_name' => $buildingName,
                'building_id' => $buildingId,
                'cost' => $cost,
                'total_cost' => array_sum($cost)
            ],
            $planetId,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    /**
     * Log d'amélioration de bâtiment
     */
    public function logBuildingUpgrade(
        int $userId,
        string $buildingName,
        int $buildingId,
        int $fromLevel,
        int $toLevel,
        array $cost,
        ?int $planetId = null,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_BUILDING_UPGRADE,
            UserLog::CATEGORY_BUILDING,
            'Amélioration du bâtiment: {building_name} (niveau {from_level} → {to_level})',
            [
                'building_name' => $buildingName,
                'building_id' => $buildingId,
                'from_level' => $fromLevel,
                'to_level' => $toLevel,
                'cost' => $cost,
                'total_cost' => array_sum($cost)
            ],
            $planetId,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    // ==================== ALIAS POUR MESSAGES PRIVÉS ====================

    /**
     * Log d'envoi de message privé
     */
    public function logPrivateMessageSent(
        int $userId,
        int $recipientId,
        string $subject,
        int $messageId,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_PRIVATE_MESSAGE_SENT,
            UserLog::CATEGORY_MESSAGE,
            'Message privé envoyé à {recipient_name}: {subject}',
            [
                'recipient_id' => $recipientId,
                'recipient_name' => User::find($recipientId)?->name ?? 'Utilisateur inconnu',
                'subject' => $subject,
                'message_id' => $messageId
            ],
            null,
            $recipientId,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    /**
     * Log de réception de message privé
     */
    public function logPrivateMessageReceived(
        int $userId,
        int $senderId,
        string $subject,
        int $messageId,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_PRIVATE_MESSAGE_RECEIVED,
            UserLog::CATEGORY_MESSAGE,
            'Message privé reçu de {sender_name}: {subject}',
            [
                'sender_id' => $senderId,
                'sender_name' => User::find($senderId)?->name ?? 'Utilisateur inconnu',
                'subject' => $subject,
                'message_id' => $messageId
            ],
            null,
            $senderId,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    // ==================== ALIAS POUR FORUM ====================

    /**
     * Log de post sur le forum
     */
    public function logForumPost(
        int $userId,
        string $topicTitle,
        int $topicId,
        int $postId,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_FORUM_POST,
            UserLog::CATEGORY_FORUM,
            'Post sur le forum dans le sujet: {topic_title}',
            [
                'topic_title' => $topicTitle,
                'topic_id' => $topicId,
                'post_id' => $postId
            ],
            null,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    // ==================== ALIAS POUR ALLIANCE ====================

    /**
     * Log d'adhésion à une alliance
     */
    public function logAllianceJoin(
        int $userId,
        string $allianceName,
        int $allianceId,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_ALLIANCE_JOIN,
            UserLog::CATEGORY_ALLIANCE,
            'Adhésion à l\'alliance: {alliance_name}',
            [
                'alliance_name' => $allianceName,
                'alliance_id' => $allianceId
            ],
            null,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    /**
     * Log de départ d'une alliance
     */
    public function logAllianceLeave(
        int $userId,
        string $allianceName,
        int $allianceId,
        string $reason = 'Départ volontaire',
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_ALLIANCE_LEAVE,
            UserLog::CATEGORY_ALLIANCE,
            'Départ de l\'alliance: {alliance_name} ({reason})',
            [
                'alliance_name' => $allianceName,
                'alliance_id' => $allianceId,
                'reason' => $reason
            ],
            null,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    // ==================== ALIAS POUR COMBAT ====================

    /**
     * Log d'attaque lancée
     */
    public function logAttackLaunched(
        int $userId,
        int $targetUserId,
        int $attackerPlanetId,
        int $defenderPlanetId,
        string $attackType,
        array $units,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_ATTACK_LAUNCHED,
            UserLog::CATEGORY_COMBAT,
            'Attaque {attack_type} lancée contre {target_name}',
            [
                'target_user_id' => $targetUserId,
                'target_name' => User::find($targetUserId)?->name ?? 'Utilisateur inconnu',
                'attacker_planet_id' => $attackerPlanetId,
                'defender_planet_id' => $defenderPlanetId,
                'attack_type' => $attackType,
                'units' => $units
            ],
            $attackerPlanetId,
            $targetUserId,
            UserLog::SEVERITY_WARNING,
            $request
        );
    }

    /**
     * Log d'attaque reçue
     */
    public function logAttackReceived(
        int $userId,
        int $attackerUserId,
        int $attackerPlanetId,
        int $defenderPlanetId,
        string $attackType,
        bool $attackerWon,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_ATTACK_RECEIVED,
            UserLog::CATEGORY_COMBAT,
            'Attaque {attack_type} reçue de {attacker_name} - {result}',
            [
                'attacker_user_id' => $attackerUserId,
                'attacker_name' => User::find($attackerUserId)?->name ?? 'Utilisateur inconnu',
                'attacker_planet_id' => $attackerPlanetId,
                'defender_planet_id' => $defenderPlanetId,
                'attack_type' => $attackType,
                'result' => $attackerWon ? 'Défaite' : 'Victoire'
            ],
            $defenderPlanetId,
            $attackerUserId,
            $attackerWon ? UserLog::SEVERITY_ERROR : UserLog::SEVERITY_WARNING,
            $request
        );
    }

    // ==================== ALIAS POUR COMMERCE ====================

    /**
     * Log de création d'échange
     */
    public function logTradeCreated(
        int $userId,
        array $offeredResources,
        array $requestedResources,
        int $tradeId,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_TRADE_CREATED,
            UserLog::CATEGORY_TRADE,
            'Échange créé: {offered} contre {requested}',
            [
                'offered_resources' => $offeredResources,
                'requested_resources' => $requestedResources,
                'trade_id' => $tradeId,
                'offered' => $this->formatResources($offeredResources),
                'requested' => $this->formatResources($requestedResources)
            ],
            null,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    /**
     * Log d'acceptation d'échange
     */
    public function logTradeAccepted(
        int $userId,
        int $tradeOwnerId,
        int $tradeId,
        array $receivedResources,
        array $givenResources,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_TRADE_ACCEPTED,
            UserLog::CATEGORY_TRADE,
            'Échange accepté avec {trade_owner_name}: {received} reçu contre {given} donné',
            [
                'trade_owner_id' => $tradeOwnerId,
                'trade_owner_name' => User::find($tradeOwnerId)?->name ?? 'Utilisateur inconnu',
                'trade_id' => $tradeId,
                'received_resources' => $receivedResources,
                'given_resources' => $givenResources,
                'received' => $this->formatResources($receivedResources),
                'given' => $this->formatResources($givenResources)
            ],
            null,
            $tradeOwnerId,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    // ==================== ALIAS POUR TECHNOLOGIES ====================

    /**
     * Log de recherche de technologie
     */
    public function logTechnologyResearch(
        int $userId,
        string $technologyName,
        int $technologyId,
        int $level,
        array $cost,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_TECHNOLOGY_RESEARCH,
            UserLog::CATEGORY_TECHNOLOGY,
            'Recherche de technologie: {technology_name} niveau {level}',
            [
                'technology_name' => $technologyName,
                'technology_id' => $technologyId,
                'level' => $level,
                'cost' => $cost,
                'total_cost' => array_sum($cost)
            ],
            null,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    // ==================== ALIAS POUR MISSIONS ====================

    /**
     * Log de lancement de mission
     */
    public function logMissionLaunched(
        int $userId,
        string $missionType,
        int $sourcePlanetId,
        ?int $targetPlanetId,
        array $ships,
        ?Request $request = null
    ): UserLog {
        // Construire une description adaptée selon la disponibilité de la planète cible
        $description = $targetPlanetId !== null
            ? 'Mission {mission_type} lancée vers la planète {target_planet_id}'
            : 'Mission {mission_type} lancée vers les coordonnées {target_coordinates}';

        return $this->log(
            $userId,
            UserLog::ACTION_MISSION_LAUNCHED,
            UserLog::CATEGORY_MISSION,
            $description,
            [
                'mission_type' => $missionType,
                'source_planet_id' => $sourcePlanetId,
                'target_planet_id' => $targetPlanetId,
                // Permet d'afficher des coordonnées si la planète n'existe pas encore (colonisation)
                'target_coordinates' => $ships['target_coordinates'] ?? null,
                'ships' => $ships,
                // Calcul robuste du nombre total de vaisseaux en fonction du payload fourni
                'total_ships' => $this->calculateTotalShipsFromPayload($ships)
            ],
            $sourcePlanetId,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    // ==================== ALIAS POUR PLANÈTES ====================

    /**
     * Log de création de planète
     */
    public function logPlanetCreated(
        int $userId,
        string $planetName,
        int $planetId,
        array $coordinates,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_PLANET_CREATED,
            UserLog::CATEGORY_PLANET,
            'Nouvelle planète créée: {planet_name} aux coordonnées {coordinates}',
            [
                'planet_name' => $planetName,
                'planet_id' => $planetId,
                'coordinates' => $coordinates
            ],
            $planetId,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    // ==================== ALIAS POUR PARAMÈTRES ====================

    /**
     * Log de modification des paramètres
     */
    public function logSettingsChanged(
        int $userId,
        array $changedSettings,
        ?Request $request = null
    ): UserLog {
        return $this->log(
            $userId,
            UserLog::ACTION_SETTINGS_CHANGED,
            UserLog::CATEGORY_SETTINGS,
            'Paramètres modifiés: {changed_fields}',
            [
                'changed_settings' => $changedSettings,
                'changed_fields' => implode(', ', array_keys($changedSettings))
            ],
            null,
            null,
            UserLog::SEVERITY_INFO,
            $request
        );
    }

    // ==================== MÉTHODES UTILITAIRES ====================

    /**
     * Formater les ressources pour l'affichage
     */
    private function formatResources(array $resources): string
    {
        $formatted = [];
        foreach ($resources as $resourceName => $amount) {
            $formatted[] = "{$amount} {$resourceName}";
        }
        return implode(', ', $formatted);
    }

    /**
     * Calculer de manière robuste le total de vaisseaux depuis un payload hétérogène.
     * Ce payload peut contenir soit:
     * - un champ explicite 'ships_sent' ou 'scout_ships'
     * - un tableau 'ships' avec des quantités, ou une liste d'objets avec 'quantity'
     * - des métadonnées diverses (coordonnées, carburant, durée, etc.)
     */
    private function calculateTotalShipsFromPayload(array $payload): int
    {
        // Champs explicites fréquemment utilisés
        if (isset($payload['ships_sent']) && is_numeric($payload['ships_sent'])) {
            return (int) $payload['ships_sent'];
        }
        if (isset($payload['scout_ships']) && is_numeric($payload['scout_ships'])) {
            return (int) $payload['scout_ships'];
        }

        // Si un sous-tableau 'ships' est fourni, tenter de sommer ses quantités
        if (isset($payload['ships']) && is_array($payload['ships'])) {
            $sum = 0;
            foreach ($payload['ships'] as $key => $value) {
                if (is_array($value)) {
                    if (isset($value['quantity']) && is_numeric($value['quantity'])) {
                        $sum += (int) $value['quantity'];
                    }
                } elseif (is_numeric($value)) {
                    $sum += (int) $value;
                }
            }
            if ($sum > 0) {
                return $sum;
            }
        }

        // Fallback: ne pas sommer les métadonnées non pertinentes; retourner 0
        return 0;
    }

    /**
     * Récupérer les logs d'un utilisateur
     */
    public function getUserLogs(
        int $userId,
        ?string $actionType = null,
        ?string $category = null,
        int $limit = 50,
        int $offset = 0
    ) {
        $query = UserLog::byUser($userId)
            ->with(['planet', 'targetUser'])
            ->orderBy('created_at', 'desc');

        if ($actionType) {
            $query->byActionType($actionType);
        }

        if ($category) {
            $query->byCategory($category);
        }

        return $query->limit($limit)->offset($offset)->get();
    }

    /**
     * Récupérer les statistiques de logs
     */
    public function getLogStats(int $userId, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();

        $logs = UserLog::byUser($userId)
            ->byDateRange($startDate, $endDate)
            ->get();

        $stats = [
            'total' => $logs->count(),
            'by_category' => $logs->groupBy('action_category')->map->count(),
            'by_severity' => $logs->groupBy('severity')->map->count(),
            'by_day' => $logs->groupBy(function ($log) {
                return $log->created_at->format('Y-m-d');
            })->map->count()
        ];

        return $stats;
    }

    /**
     * Nettoyer les anciens logs
     */
    public function cleanOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        return UserLog::where('created_at', '<', $cutoffDate)->delete();
    }
}