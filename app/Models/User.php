<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Template\TemplateBadge;
use App\Models\Planet\Planet;
use App\Models\User\UserStat;
use App\Models\Alliance\Alliance;
use App\Models\Alliance\AllianceMember;
use App\Models\Alliance\AllianceApplication;
use App\Models\Forum\ForumTopic;
use App\Models\Forum\ForumPost;
use App\Models\Forum\ForumReport;
use App\Models\Messaging\PrivateConversation;
use App\Models\Messaging\PrivateMessage;
use App\Models\Server\ServerConfig;
use App\Models\User\UserSanction;
use App\Models\User\UserLog;
use App\Models\User\UserBookmark;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'experience',
        'faction_id',
        'discord_id',
        'research_points',
        'background',
        'last_username_change',
        'main_planet_id',
        'actual_planet_id',
        'alliance_id',
        'is_active',
        'vacation_mode',
        'vacation_mode_until',
        'last_login_at',
        'hide_points_breakdown',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Default attribute values.
     * Ensures new users have a valid JSON structure for experience.
     */
    protected $attributes = [
        'experience' => '{"level":1,"actual":0}',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'experience' => 'array',
            'is_active' => 'boolean',
            'vacation_mode' => 'boolean',
            'vacation_mode_until' => 'datetime',
            'last_login_at' => 'datetime',
            'vip_active' => 'boolean',
            'vip_badge_enabled' => 'boolean',
            'vip_until' => 'datetime',
            'gold_balance' => 'integer',
            'hide_points_breakdown' => 'boolean',
            'research_points' => 'integer',
        ];
    }

    /**
     * Accessor: convert stored Unix timestamp to Carbon instance for last_activity.
     */
    public function getLastActivityAttribute($value)
    {
        return $value ? Carbon::createFromTimestamp((int) $value) : null;
    }

    /**
     * Mutator: ensure last_activity is stored as Unix timestamp integer.
     */
    public function setLastActivityAttribute($value): void
    {
        if ($value instanceof Carbon) {
            $this->attributes['last_activity'] = $value->getTimestamp();
        } elseif (is_int($value)) {
            $this->attributes['last_activity'] = $value;
        } else {
            $this->attributes['last_activity'] = Carbon::parse($value)->getTimestamp();
        }
    }

    /**
     * Max concurrent building constructions based on VIP status.
     */
    public function getMaxConcurrentBuildingCount(): int
    {
        // Non-VIP: 1, VIP: 3
        return $this->vip_active ? 3 : 1;
    }

    /**
     * Max concurrent production (units/defense/ships) based on VIP status.
     */
    public function getMaxConcurrentProductionCount(): int
    {
        // Aligné sur les bâtiments: Non-VIP 1, VIP 3
        return $this->vip_active ? 3 : 1;
    }

    /**
     * Get the current level of the user
     */
    public function getLevel(): int
    {
        return $this->experience['level'] ?? 1;
    }

    /**
     * Get the current experience points
     */
    public function getCurrentExperience(): int
    {
        $experience = $this->normalizeExperience();
        return $experience['actual'];
    }
    
    /**
     * Get the faction that the user belongs to.
     */
    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    /**
     * Inventaire d'objets possédés par l'utilisateur.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(\App\Models\User\UserInventory::class);
    }

    /**
     * Donner un item au joueur via sa clé de template.
     */
    public function giveInventoryItem(string $templateKey, int $quantity = 1, ?string $source = null): ?\App\Models\User\UserInventory
    {
        $template = \App\Models\Template\TemplateInventory::where('key', $templateKey)->first();
        if (!$template) {
            return null;
        }

        $inv = $this->inventories()->firstOrCreate([
            'template_inventory_id' => $template->id,
        ], [
            'quantity' => 0,
            'acquired_at' => now(),
        ]);

        $inv->quantity += max(1, $quantity);
        $inv->save();

        // Créer un message privé système pour informer de la réception
        try {
            $pm = new \App\Services\PrivateMessageService();
            $title = "Objet reçu";
            $origin = $source ? $source : 'Provenance inconnue';
            $msg = "<div class='system-message-content'>";
            $msg .= "<p>🎁 <strong>Vous avez reçu un objet</strong></p>";
            $msg .= "<p>📦 <strong>Objet:</strong> " . e(ucfirst($template->name)) . "</p>";
            $msg .= "<p>🔢 <strong>Quantité:</strong> " . number_format(max(1, $quantity)) . "</p>";
            $msg .= "<p>🧭 <strong>Provenance:</strong> " . e($origin) . "</p>";
            $msg .= "<p>📅 <strong>Date:</strong> " . \Carbon\Carbon::now()->format('d/m/Y H:i:s') . "</p>";
            $msg .= "</div>";
            $pm->createSystemMessage($this, 'system', $title, $msg);
        } catch (\Throwable $e) {
            // Ne pas bloquer la logique d'inventaire en cas d'erreur de message
            \Log::warning('Échec création message de réception item', [
                'user_id' => $this->id,
                'template_key' => $templateKey,
                'error' => $e->getMessage(),
            ]);
        }

        return $inv;
    }

    /**
     * Vérifie si l'utilisateur possède au moins un item du template.
     */
    public function hasInventoryItem(string $templateKey): bool
    {
        $template = \App\Models\Template\TemplateInventory::where('key', $templateKey)->first();
        if (!$template) {
            return false;
        }
        return $this->inventories()
            ->where('template_inventory_id', $template->id)
            ->where('quantity', '>', 0)
            ->exists();
    }

    /**
     * Remove experience points from the user
     */
    public function removeExperience(int $points): void
    {
        $experience = $this->normalizeExperience();
        $experience['actual'] = max(0, $experience['actual'] - $points);
        
        $this->experience = $experience;
        $this->save();
    }

    /**
     * Get the display name for the user role
     */
    public function getRoleName(): string
    {
        return match($this->role) {
            'owner' => 'Fondateur',
            'admin' => 'Administrateur',
            'modo' => 'Modérateur',
            'helper' => 'Helpeur',
            'player' => 'Joueur',
            default => 'Membre'
        };
    }

    /**
     * Get the icon for the user role
     */
    public function getRoleIcon(): string
    {
        return match($this->role) {
            'owner' => 'fas fa-crown',
            'admin' => 'fas fa-shield-alt',
            'modo' => 'fas fa-hammer',
            'helper' => 'fas fa-hands-helping',
            'player' => 'fas fa-user',
            default => 'fas fa-user'
        };
    }

    /**
     * Set the user level directly
     */
    public function setLevel(int $level): void
    {
        $experience = $this->normalizeExperience();
        $experience['level'] = max(1, $level);
        $experience['actual'] = 0; // Reset current XP when setting level
        
        $this->experience = $experience;
        $this->save();
    }

    /**
     * Get required experience for a specific level
     * Uses an exponential formula that scales progressively
     * Formula: base_xp * (level^1.5) + (level * multiplier)
     */
    public function getRequiredExperienceForLevel(int $level): int
    {
        // Base configuration
        $baseXp = 100;        // Base XP for level 1
        $multiplier = 50;     // Linear multiplier
        $exponential = 1.5;   // Exponential factor
        
        // For level 1, return base XP
        if ($level <= 1) {
            return $baseXp;
        }
        
        // Progressive formula: base * (level^1.5) + (level * multiplier)
        // This creates a curve that starts manageable but becomes increasingly challenging
        $requiredXp = $baseXp * pow($level, $exponential) + ($level * $multiplier);
        
        // Round to nearest integer and ensure minimum
        return max($baseXp, (int) round($requiredXp));
    }
    
    /**
     * Get total experience required to reach a specific level
     */
    public function getTotalExperienceForLevel(int $targetLevel): int
    {
        $totalXp = 0;
        
        for ($level = 1; $level < $targetLevel; $level++) {
            $totalXp += $this->getRequiredExperienceForLevel($level);
        }
        
        return $totalXp;
    }
    
    /**
     * Get experience needed to reach next level
     */
    public function getExperienceToNextLevel(): int
    {
        $currentLevel = $this->getLevel();
        $requiredForNext = $this->getRequiredExperienceForLevel($currentLevel);
        $currentXp = $this->getCurrentExperience();
        
        return max(0, $requiredForNext - $currentXp);
    }
    
    /**
     * Get experience progress percentage for current level
     */
    public function getLevelProgress(): float
    {
        $currentLevel = $this->getLevel();
        $requiredForLevel = $this->getRequiredExperienceForLevel($currentLevel);
        $currentXp = $this->getCurrentExperience();
        
        if ($requiredForLevel <= 0) {
            return 100.0;
        }
        
        return min(100.0, ($currentXp / $requiredForLevel) * 100);
    }

    /**
     * Add research points
     */
    public function addResearchPoints(int $points): void
    {
        $this->research_points += $points;
        $this->save();
    }

    /**
     * Remove research points
     */
    public function removeResearchPoints(int $points): void
    {
        $this->research_points = max(0, $this->research_points - $points);
        $this->save();
    }

    /**
     * Check if user has enough research points
     */
    public function hasEnoughResearchPoints(int $points): bool
    {
        return $this->research_points >= $points;
    }

    /**
     * Get research points
     */
    public function getResearchPoints(): int
    {
        return (int) ($this->research_points ?? 0);
    }

    /**
     * Planets owned by this user
     */
    public function planets(): HasMany
    {
        return $this->hasMany(Planet::class);
    }

    /**
     * Bookmarks saved by this user
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(UserBookmark::class);
    }

    /**
    * Technology owned by this user
    */
    public function technologies(): HasMany
    {
        return $this->hasMany(User\UserTechnology::class);
    }

    /**
     * User statistics
     */
    public function userStat(): HasOne
    {
        return $this->hasOne(UserStat::class);
    }

    /**
     * Event statistics (reset chaque fin d'event)
     */
    public function userStatEvent(): HasOne
    {
        return $this->hasOne(User\UserStatEvent::class);
    }

    /**
     * Get the main planet of the user
     */
    public function getMainPlanet(): ?Planet
    {
        if (!$this->main_planet_id) {
            return null;
        }
        
        return Planet::find($this->main_planet_id);
    }

    /**
     * Get the actual planet of the user
     */
    public function getActualPlanet(): ?Planet
    {
        if (!$this->actual_planet_id) {
            return $this->getMainPlanet();
        }
        
        return Planet::find($this->actual_planet_id);
    }

    /**
     * Badges earned by this user
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(TemplateBadge::class, 'user_badges', 'user_id', 'badge_id')
                    ->withTimestamps()
                    ->withPivot('earned_at')
                    ->orderBy('user_badges.earned_at', 'desc');
    }

    /**
     * Check and award new badges to the user
     */
    public function checkAndAwardBadges(): array
    {
        $newBadges = [];
        $availableBadges = TemplateBadge::where('is_active', true)->get();
        $earnedBadgeIds = $this->badges()->pluck('badges.id')->toArray();

        foreach ($availableBadges as $badge) {
            // Skip if already earned
            if (in_array($badge->id, $earnedBadgeIds)) {
                continue;
            }

            // Check if requirements are met
            if ($badge->checkRequirement($this)) {
                $this->awardBadge($badge);
                $newBadges[] = $badge;
            }
        }

        return $newBadges;
    }

    /**
     * Award a specific badge to the user
     */
    public function awardBadge(TemplateBadge $badge): void
    {
        // Check if user already has this badge
        if ($this->badges()->where('badge_id', $badge->id)->exists()) {
            return;
        }

        // Award the badge
        $this->badges()->attach($badge->id, [
            'earned_at' => now()
        ]);

        // Award experience points if any
        if ($badge->points_reward > 0) {
            $this->addExperience($badge->points_reward);
        }
    }

    /**
     * Remove a badge from the user
     */
    public function removeBadge(TemplateBadge $badge): void
    {
        $this->badges()->detach($badge->id);
    }

    /**
     * Check if user has a specific badge
     */
    public function hasBadge(TemplateBadge $badge): bool
    {
        return $this->badges()->where('badge_id', $badge->id)->exists();
    }

    /**
     * Get badges by type
     */
    public function getBadgesByType(string $type): BelongsToMany
    {
        return $this->badges()->where('type', $type);
    }

    /**
     * Get badges by rarity
     */
    public function getBadgesByRarity(string $rarity): BelongsToMany
    {
        return $this->badges()->where('rarity', $rarity);
    }

    /**
     * Get total badge count
     */
    public function getBadgeCount(): int
    {
        return $this->badges()->count();
    }

    /**
     * Get badge count by rarity
     */
    public function getBadgeCountByRarity(): array
    {
        return $this->badges()
                    ->selectRaw('rarity, COUNT(*) as count')
                    ->groupBy('rarity')
                    ->orderBy('rarity')
                    ->pluck('count', 'rarity')
                    ->toArray();
    }

    /**
     * Override addExperience to check for new badges
     */
    public function addExperience(int $points): array
    {
        $oldLevel = $this->getLevel();
        
        $experience = $this->normalizeExperience();
        $experience['actual'] += max(0, $points);
        
        // Check for level up
        $requiredXp = $this->getRequiredExperienceForLevel($experience['level']);
        
        while ($experience['actual'] >= $requiredXp) {
            $experience['actual'] -= $requiredXp;
            $experience['level']++;
            $requiredXp = $this->getRequiredExperienceForLevel($experience['level']);
        }
        
        $this->experience = $experience;
        $this->save();
        
        // Check for new badges after experience/level change
        $newBadges = $this->checkAndAwardBadges();
        
        return [
            'old_level' => $oldLevel,
            'new_level' => $this->getLevel(),
            'level_up' => $this->getLevel() > $oldLevel,
            'new_badges' => $newBadges
        ];
    }

    /**
     * Normalize experience structure to ensure required keys exist.
     */
    protected function normalizeExperience(): array
    {
        $exp = $this->experience;
        if (!is_array($exp)) {
            $exp = [];
        }
        $level = $exp['level'] ?? 1;
        $actual = $exp['actual'] ?? 0;
        $exp['level'] = is_numeric($level) ? max(1, (int) $level) : 1;
        $exp['actual'] = is_numeric($actual) ? max(0, (int) $actual) : 0;
        return $exp;
    }

    /**
     * Relation avec l'alliance
     */
    public function alliance(): BelongsTo
    {
        return $this->belongsTo(Alliance::class);
    }

    /**
     * Relation avec les informations de membre d'alliance
     */
    public function allianceMember(): HasOne
    {
        return $this->hasOne(AllianceMember::class);
    }

    /**
     * Relation avec les candidatures d'alliance
     */
    public function allianceApplications(): HasMany
    {
        return $this->hasMany(AllianceApplication::class);
    }

    /**
     * Vérifier si l'utilisateur est dans une alliance
     */
    public function isInAlliance(): bool
    {
        return $this->alliance_id !== null;
    }

    /**
     * Vérifier si l'utilisateur est leader d'une alliance
     */
    public function isAllianceLeader(): bool
    {
        return $this->alliance && $this->alliance->leader_id === $this->id;
    }

    /**
     * Obtenir le rang dans l'alliance
     */
    public function getAllianceRank()
    {
        return $this->allianceMember?->rank;
    }

    /**
     * Vérifier si l'utilisateur a une permission d'alliance
     */
    public function hasAlliancePermission(string $permission): bool
    {
        if (!$this->isInAlliance()) {
            return false;
        }

        if ($this->isAllianceLeader()) {
            return true;
        }

        return $this->allianceMember?->hasPermission($permission) ?? false;
    }

    /**
     * Quitter l'alliance
     */
    public function leaveAlliance(): bool
    {
        if (!$this->isInAlliance()) {
            return false;
        }

        if ($this->isAllianceLeader()) {
            return false; // Le leader ne peut pas quitter sans transférer le leadership
        }

        $this->allianceMember?->delete();
        $this->update(['alliance_id' => null]);

        return true;
    }

    /**
     * Relations pour les sanctions
     */
    public function sanctions(): HasMany
    {
        return $this->hasMany(UserSanction::class);
    }

    public function activeSanctions(): HasMany
    {
        return $this->hasMany(UserSanction::class)->active();
    }

    public function sanctionsGiven(): HasMany
    {
        return $this->hasMany(UserSanction::class, 'sanctioned_by');
    }

    /**
     * Méthodes pour les rôles
     */
    public function isPlayer(): bool
    {
        return $this->role === 'player';
    }

    public function isHelper(): bool
    {
        return $this->role === 'helper';
    }

    public function isModo(): bool
    {
        return $this->role === 'modo';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function hasModeratorRights(): bool
    {
        return in_array($this->role, ['modo', 'admin', 'owner']);
    }

    public function hasAdminRights(): bool
    {
        return in_array($this->role, ['admin', 'owner']);
    }

    public function hasAdmin(): bool
    {
        return $this->hasModeratorRights() || $this->hasAdminRights();
    }

    public function canBanUsers(): bool
    {
        return $this->hasModeratorRights();
    }

    public function canMuteUsers(): bool
    {
        return $this->hasModeratorRights();
    }

    public function canSendAnnouncements(): bool
    {
        return $this->hasModeratorRights();
    }

    public function canManageBadges(): bool
    {
        return $this->hasModeratorRights();
    }

    /**
     * Méthodes pour les sanctions
     */
    public function isBanned(): bool
    {
        return $this->activeSanctions()->bans()->exists();
    }

    public function isMuted(): bool
    {
        return $this->activeSanctions()->mutes()->exists();
    }

    public function getActiveBan()
    {
        return $this->activeSanctions()->bans()->first();
    }

    public function getActiveMute()
    {
        return $this->activeSanctions()->mutes()->first();
    }

    public function canSendMessages(): bool
    {
        return !$this->isMuted() && !$this->isBanned();
    }

    public function posts_count()
    {
        return $this->hasMany(ForumPost::class)->count();
    }

    public function topic_count()
    {
        return $this->hasMany(ForumTopic::class)->count();
    }

    /**
     * Relations pour le forum
     */
    public function forumPosts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    public function forumTopics(): HasMany
    {
        return $this->hasMany(ForumTopic::class);
    }

    public function forumReports(): HasMany
    {
        return $this->hasMany(ForumReport::class, 'reported_by');
    }

    /**
     * Get unread private messages count for this user
     */
    public function getUnreadMessagesCount(): int
    {
        $conversationIds = PrivateConversation::forUser($this)->pluck('id');
        return PrivateMessage::whereIn('conversation_id', $conversationIds)
            ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('conversation_participants')
                    ->whereColumn('conversation_participants.conversation_id', 'private_messages.conversation_id')
                    ->where('conversation_participants.user_id', $this->id)
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('conversation_participants.last_read_at')
                            ->orWhereColumn('private_messages.created_at', '>', 'conversation_participants.last_read_at');
                    });
            })
            ->count();
    }

    /**
     * Relation avec les logs d'actions de l'utilisateur
     */
    public function userLogs(): HasMany
    {
        return $this->hasMany(UserLog::class);
    }

    /**
     * Relation avec les logs où cet utilisateur est la cible
     */
    public function targetLogs(): HasMany
    {
        return $this->hasMany(UserLog::class, 'target_user_id');
    }

    /**
     * Check if user is in vacation mode
     */
    public function isInVacationMode(): bool
    {
        return $this->vacation_mode && ($this->vacation_mode_until === null || $this->vacation_mode_until > now());
    }

    /**
     * Enable vacation mode for the user
     */
    public function enableVacationMode(int $days): bool
    {
        // Vérifier si le mode vacances est activé sur le serveur
        if (!ServerConfig::isVacationModeEnabled()) {
            return false;
        }
        
        $minDays = ServerConfig::getVacationModeMinDays();
        $maxDays = ServerConfig::getVacationModeMaxDays();
        
        // Validate days range
        if ($days < $minDays || $days > $maxDays) {
            return false;
        }
        
        $this->vacation_mode = true;
        $this->vacation_mode_until = now()->addDays($days);
        return $this->save();
    }

    /**
     * Disable vacation mode for the user
     */
    public function disableVacationMode(): bool
    {
        $this->vacation_mode = false;
        $this->vacation_mode_until = null;
        return $this->save();
    }

    /**
     * Get vacation mode end date
     */
    public function getVacationModeEndDate(): ?Carbon
    {
        return $this->vacation_mode_until;
    }

    /**
     * Get remaining vacation mode days
     */
    public function getRemainingVacationModeDays(): int
    {
        if (!$this->isInVacationMode() || $this->vacation_mode_until === null) {
            return 0;
        }
        
        return now()->diffInDays($this->vacation_mode_until, false);
    }

    /**
     * Retourne l'URL de l'avatar utilisateur.
     * Priorité: avatar personnalisé dans public/avatars ou public/avatar, sinon Gravatar.
     */
    public function getUserAvatarUrl(int $size = 150): string
    {
        // public/avatars/{id}/avatar.{ext}
        $baseDir = public_path('avatars/' . $this->id);
        foreach (['jpg','jpeg','png','webp'] as $ext) {
            $full = $baseDir . DIRECTORY_SEPARATOR . 'avatar.' . $ext;
            if (file_exists($full)) {
                return asset('avatars/' . $this->id . '/avatar.' . $ext);
            }
        }

        // public/avatar/{id}.{ext}
        $flatDir = public_path('avatar');
        foreach (['jpg','jpeg','png','webp'] as $ext) {
            $full = $flatDir . DIRECTORY_SEPARATOR . $this->id . '.' . $ext;
            if (file_exists($full)) {
                return asset('avatar/' . $this->id . '.' . $ext);
            }
        }

        // Fallback Gravatar
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=identicon";
    }
}
