<?php

namespace App\Models\User;

use App\Models\User;
use App\Models\Planet\Planet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action_type',
        'action_category',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'planet_id',
        'target_user_id',
        'severity',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur qui a effectué l'action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec la planète concernée (si applicable)
     */
    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planet::class);
    }

    /**
     * Relation avec l'utilisateur cible (si applicable)
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * Scope pour filtrer par type d'action
     */
    public function scopeByActionType($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope pour filtrer par catégorie d'action
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('action_category', $category);
    }

    /**
     * Scope pour filtrer par utilisateur
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopeByDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope pour filtrer par sévérité
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope pour les logs récents
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', Carbon::now()->subHours($hours));
    }

    /**
     * Accessor pour formater la description avec les métadonnées
     */
    public function getFormattedDescriptionAttribute(): string
    {
        $description = $this->description;
        
        if ($this->metadata && is_array($this->metadata)) {
            foreach ($this->metadata as $key => $value) {
                // Convertir explicitement la valeur en string pour éviter l'erreur str_replace()
                $stringValue = is_array($value) ? json_encode($value) : (string)$value;
                $description = str_replace('{' . $key . '}', $stringValue, $description);
            }
        }
        
        return $description;
    }

    /**
     * Constantes pour les types d'actions
     */
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_RESOURCE_SPEND = 'resource_spend';
    const ACTION_RESOURCE_GAIN = 'resource_gain';
    const ACTION_BUILDING_PURCHASE = 'building_purchase';
    const ACTION_BUILDING_UPGRADE = 'building_upgrade';
    const ACTION_PRIVATE_MESSAGE_SENT = 'private_message_sent';
    const ACTION_PRIVATE_MESSAGE_RECEIVED = 'private_message_received';
    const ACTION_FORUM_POST = 'forum_post';
    const ACTION_ALLIANCE_JOIN = 'alliance_join';
    const ACTION_ALLIANCE_LEAVE = 'alliance_leave';
    const ACTION_ATTACK_LAUNCHED = 'attack_launched';
    const ACTION_ATTACK_RECEIVED = 'attack_received';
    const ACTION_TRADE_CREATED = 'trade_created';
    const ACTION_TRADE_ACCEPTED = 'trade_accepted';
    const ACTION_TECHNOLOGY_RESEARCH = 'technology_research';
    const ACTION_MISSION_LAUNCHED = 'mission_launched';
    const ACTION_PLANET_CREATED = 'planet_created';
    const ACTION_SETTINGS_CHANGED = 'settings_changed';
    // Événements serveur
    const ACTION_SERVER_EVENT_REWARD = 'server_event_reward';

    /**
     * Constantes pour les catégories d'actions
     */
    const CATEGORY_AUTH = 'auth';
    const CATEGORY_RESOURCE = 'resource';
    const CATEGORY_BUILDING = 'building';
    const CATEGORY_MESSAGE = 'message';
    const CATEGORY_FORUM = 'forum';
    const CATEGORY_ALLIANCE = 'alliance';
    const CATEGORY_COMBAT = 'combat';
    const CATEGORY_TRADE = 'trade';
    const CATEGORY_TECHNOLOGY = 'technology';
    const CATEGORY_MISSION = 'mission';
    const CATEGORY_PLANET = 'planet';
    const CATEGORY_SETTINGS = 'settings';
    // Catégorie pour les événements serveur
    const CATEGORY_EVENT = 'event';

    /**
     * Constantes pour les niveaux de sévérité
     */
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_ERROR = 'error';
    const SEVERITY_CRITICAL = 'critical';

    /**
     * Retourne tous les types d'actions disponibles
     */
    public static function getActionTypes(): array
    {
        return [
            self::ACTION_LOGIN => 'Connexion',
            self::ACTION_LOGOUT => 'Déconnexion',
            self::ACTION_RESOURCE_SPEND => 'Dépense de ressources',
            self::ACTION_RESOURCE_GAIN => 'Gain de ressources',
            self::ACTION_BUILDING_PURCHASE => 'Achat de bâtiment',
            self::ACTION_BUILDING_UPGRADE => 'Amélioration de bâtiment',
            self::ACTION_PRIVATE_MESSAGE_SENT => 'Message privé envoyé',
            self::ACTION_PRIVATE_MESSAGE_RECEIVED => 'Message privé reçu',
            self::ACTION_FORUM_POST => 'Publication sur le forum',
            self::ACTION_ALLIANCE_JOIN => 'Adhésion à une alliance',
            self::ACTION_ALLIANCE_LEAVE => 'Départ d\'une alliance',
            self::ACTION_ATTACK_LAUNCHED => 'Attaque lancée',
            self::ACTION_ATTACK_RECEIVED => 'Attaque reçue',
            self::ACTION_TRADE_CREATED => 'Échange créé',
            self::ACTION_TRADE_ACCEPTED => 'Échange accepté',
            self::ACTION_TECHNOLOGY_RESEARCH => 'Recherche technologique',
            self::ACTION_MISSION_LAUNCHED => 'Mission lancée',
            self::ACTION_PLANET_CREATED => 'Planète créée',
            self::ACTION_SETTINGS_CHANGED => 'Paramètres modifiés',
            self::ACTION_SERVER_EVENT_REWARD => 'Récompense d\'événement serveur',
        ];
    }

    /**
     * Retourne toutes les catégories d'actions disponibles
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_AUTH => 'Authentification',
            self::CATEGORY_RESOURCE => 'Ressources',
            self::CATEGORY_BUILDING => 'Bâtiments',
            self::CATEGORY_MESSAGE => 'Messages',
            self::CATEGORY_FORUM => 'Forum',
            self::CATEGORY_ALLIANCE => 'Alliance',
            self::CATEGORY_COMBAT => 'Combat',
            self::CATEGORY_TRADE => 'Commerce',
            self::CATEGORY_TECHNOLOGY => 'Technologie',
            self::CATEGORY_MISSION => 'Missions',
            self::CATEGORY_PLANET => 'Planètes',
            self::CATEGORY_SETTINGS => 'Paramètres',
            self::CATEGORY_EVENT => 'Événements',
        ];
    }

    /**
     * Retourne tous les niveaux de sévérité disponibles
     */
    public static function getSeverities(): array
    {
        return [
            self::SEVERITY_INFO => 'Information',
            self::SEVERITY_WARNING => 'Avertissement',
            self::SEVERITY_ERROR => 'Erreur',
            self::SEVERITY_CRITICAL => 'Critique',
        ];
    }
}