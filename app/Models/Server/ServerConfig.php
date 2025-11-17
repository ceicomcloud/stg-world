<?php

namespace App\Models\Server;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ServerConfig extends Model
{
    use HasFactory;

    protected $table = 'server_configs';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'category',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Configuration categories
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_PRODUCTION = 'production';
    const CATEGORY_STORAGE = 'storage';
    const CATEGORY_RESEARCH = 'research';
    const CATEGORY_BUILDING = 'building';
    const CATEGORY_COMBAT = 'combat';
    const CATEGORY_FLEET = 'fleet';
    const CATEGORY_PLANET = 'planet';
    const CATEGORY_USER = 'user';
    const CATEGORY_ECONOMY = 'economy';
    const CATEGORY_SHOP = 'shop';

    // Configuration types
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_JSON = 'json';

    /**
     * Get the typed value based on configuration type
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            // Use raw attributes to avoid referencing $this in closures
            get: function ($value, $attributes) {
                $type = $attributes['type'] ?? self::TYPE_STRING;
                return match($type) {
                    self::TYPE_INTEGER => (int) $value,
                    self::TYPE_FLOAT => (float) $value,
                    self::TYPE_BOOLEAN => (bool) $value,
                    self::TYPE_JSON => json_decode($value, true),
                    default => $value
                };
            },
            // Storage conversion is handled in ServerConfig::set()
            set: fn ($value) => $value
        );
    }

    /**
     * Get configuration value by key
     */
    public static function get(string $key, $default = null)
    {
        $config = self::where('key', $key)
            ->where('is_active', true)
            ->first();

        return $config ? $config->value : $default;
    }

    /**
     * Set configuration value
     */
    public static function set(string $key, $value, string $type = self::TYPE_STRING, string $category = self::CATEGORY_GENERAL, string $description = ''): self
    {
        // Normalize value for storage based on provided type
        $storedValue = match($type) {
            self::TYPE_INTEGER => (string) (int) $value,
            self::TYPE_FLOAT => (string) (float) $value,
            self::TYPE_BOOLEAN => $value ? '1' : '0',
            self::TYPE_JSON => json_encode($value),
            default => (string) $value,
        };

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'type' => $type,
                'category' => $category,
                'description' => $description,
                'is_active' => true
            ]
        );
    }

    /**
     * Remove configuration entry by key (avoid conflict with Model::delete)
     */
    public static function forget(string $key): bool
    {
        $config = self::where('key', $key)->first();
        if (!$config) {
            return false;
        }
        return (bool) $config->delete();
    }

    /**
     * Get all configurations by category
     */
    public static function getByCategory(string $category): array
    {
        return self::where('category', $category)
            ->where('is_active', true)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Get production rate multiplier
     */
    public static function getProductionRate(): float
    {
        return (float) self::get('production_rate', 1.0);
    }

    /**
     * Get storage rate multiplier
     */
    public static function getStorageRate(): float
    {
        return (float) self::get('storage_rate', 1.0);
    }

    /**
     * Get global shop reward multiplier (applies to purchased gold)
     */
    public static function getShopRewardRate(): float
    {
        return (float) self::get('shop_reward_rate', 1.0);
    }

    /**
     * Check if shop purchases are enabled
     */
    public static function isShopEnabled(): bool
    {
        return (bool) self::get('shop_enabled', true);
    }

    /**
     * Get research speed multiplier
     */
    public static function getResearchSpeed(): float
    {
        return (float) self::get('research_speed', 1.0);
    }

    /**
     * Get building speed multiplier
     */
    public static function getBuildingSpeed(): float
    {
        return (float) self::get('building_speed', 1.0);
    }

    /**
     * Get fleet speed multiplier
     */
    public static function getFleetSpeed(): float
    {
        return (float) self::get('fleet_speed', 1.0);
    }

    /**
     * Get maximum planets per user
     */
    public static function getMaxPlanetsPerUser(): int
    {
        return (int) self::get('max_planets_per_user', 9);
    }

    /**
     * Get total number of planets in server
     */
    public static function getTotalPlanets(): int
    {
        return (int) self::get('total_planets', 1000);
    }

    /**
     * Get number of galaxies
     */
    public static function getGalaxies(): int
    {
        return (int) self::get('galaxies', 5);
    }

    /**
     * Get systems per galaxy
     */
    public static function getSystemsPerGalaxy(): int
    {
        return (int) self::get('systems_per_galaxy', 200);
    }

    /**
     * Get planets per system
     */
    public static function getPlanetsPerSystem(): int
    {
        return (int) self::get('planets_per_system', 15);
    }

    /**
     * Get max teams per planet (normal)
     */
    public static function getMaxPlanetEquipsNormal(): int
    {
        return (int) self::get('max_planet_equips_normal', 2);
    }

    /**
     * Get max teams per planet (VIP)
     */
    public static function getMaxPlanetEquipsVip(): int
    {
        return (int) self::get('max_planet_equips_vip', 4);
    }

    /**
     * Get starting resources for new players
     */
    public static function getStartingResources(): array
    {
        return self::get('starting_resources', [
            'metal' => 500,
            'crystal' => 300,
            'deuterium' => 100
        ]);
    }

    /**
     * Get max bookmarks per user (normal)
     */
    public static function getMaxBookmarksNormal(): int
    {
        return (int) self::get('max_bookmarks_normal', 10);
    }

    /**
     * Get max bookmarks per user (VIP)
     */
    public static function getMaxBookmarksVip(): int
    {
        return (int) self::get('max_bookmarks_vip', 25);
    }

    /**
     * Get starting buildings for new players
     */
    public static function getStartingBuildings(): array
    {
        return self::get('starting_buildings', [
            'mine_fer' => 1,
            'extracteur_cristal' => 1,
            'raffinerie_deuterium' => 1,
            'centrale_solaire' => 1
        ]);
    }

    /**
     * Get starting research points for new players
     */
    public static function getStartingResearchPoints(): int
    {
        return (int) self::get('starting_research_points', 0);
    }

    /**
     * Get debris field percentage
     */
    public static function getDebrisFieldPercentage(): float
    {
        return (float) self::get('debris_field_percentage', 0.3);
    }

    /**
     * Get combat report retention days
     */
    public static function getCombatReportRetentionDays(): int
    {
        return (int) self::get('combat_report_retention_days', 30);
    }

    /**
     * Get fleet save time limit in hours
     */
    public static function getFleetSaveTimeLimit(): int
    {
        return (int) self::get('fleet_save_time_limit', 24);
    }

    /**
     * Check if newbie protection is enabled
     */
    public static function isNewbieProtectionEnabled(): bool
    {
        return (bool) self::get('newbie_protection_enabled', true);
    }

    /**
     * Get newbie protection points limit
     */
    public static function getNewbieProtectionLimit(): int
    {
        return (int) self::get('newbie_protection_limit', 50000);
    }
    
    /**
     * Check if vacation mode is enabled
     */
    public static function isVacationModeEnabled(): bool
    {
        return (bool) self::get('vacation_mode_enabled', true);
    }

    /**
     * Get vacation mode minimum duration in days
     */
    public static function getVacationModeMinDays(): int
    {
        return (int) self::get('vacation_mode_min_days', 2);
    }

    /**
     * Get vacation mode maximum duration in days
     */
    public static function getVacationModeMaxDays(): int
    {
        return (int) self::get('vacation_mode_max_days', 30);
    }

    /**
     * Get energy efficiency factor
     */
    public static function getEnergyEfficiency(): float
    {
        return (float) self::get('energy_efficiency', 1.0);
    }

    /**
     * Get resource production efficiency when energy is low
     */
    public static function getLowEnergyProductionPenalty(): float
    {
        return (float) self::get('low_energy_production_penalty', 0.5);
    }

    /**
     * Get daily attack limit per player
     */
    public static function getDailyAttackLimitPerPlayer(): int
    {
        return (int) self::get('daily_attack_limit_per_player', 5);
    }

    /**
     * Check if daily attack limit is enabled
     */
    public static function isDailyAttackLimitEnabled(): bool
    {
        return (bool) self::get('daily_attack_limit_enabled', true);
    }

    /**
     * Get maximum storage overflow percentage
     */
    public static function getMaxStorageOverflow(): float
    {
        return (float) self::get('max_storage_overflow', 1.1); // 110%
    }

    /**
     * Get trade ratio between resources
     */
    public static function getTradeRatio(): array
    {
        return self::get('trade_ratio', [
            'metal_crystal' => 2.0,
            'metal_deuterium' => 4.0,
            'crystal_deuterium' => 2.0
        ]);
    }

    /**
     * Get server timezone
     */
    public static function getServerTimezone(): string
    {
        return self::get('server_timezone', 'UTC');
    }

    /**
     * Get server language
     */
    public static function getServerLanguage(): string
    {
        return self::get('server_language', 'en');
    }

    /**
     * Check if server is in maintenance mode
     */
    public static function isMaintenanceMode(): bool
    {
        return (bool) self::get('maintenance_mode', false);
    }

    /**
     * Get maintenance message
     */
    public static function getMaintenanceMessage(): string
    {
        return self::get('maintenance_message', 'Server is under maintenance. Please try again later.');
    }

    /**
     * Initialize default server configurations
     */
    public static function initializeDefaults(): void
    {
        $defaults = [
            // General
            ['key' => 'server_name', 'value' => 'Stargate Universe', 'type' => self::TYPE_STRING, 'category' => self::CATEGORY_GENERAL, 'description' => 'Server name'],
            ['key' => 'server_timezone', 'value' => 'UTC', 'type' => self::TYPE_STRING, 'category' => self::CATEGORY_GENERAL, 'description' => 'Server timezone'],
            ['key' => 'server_language', 'value' => 'en', 'type' => self::TYPE_STRING, 'category' => self::CATEGORY_GENERAL, 'description' => 'Default server language'],
            ['key' => 'maintenance_mode', 'value' => false, 'type' => self::TYPE_BOOLEAN, 'category' => self::CATEGORY_GENERAL, 'description' => 'Server maintenance mode'],
            
            // Shop
            ['key' => 'shop_enabled', 'value' => true, 'type' => self::TYPE_BOOLEAN, 'category' => self::CATEGORY_SHOP, 'description' => 'Enable shop purchases'],
            ['key' => 'shop_reward_rate', 'value' => 1.0, 'type' => self::TYPE_FLOAT, 'category' => self::CATEGORY_SHOP, 'description' => 'Global multiplier for purchased gold (happy hours)'],
            
            // Production
            ['key' => 'production_rate', 'value' => 1.0, 'type' => self::TYPE_FLOAT, 'category' => self::CATEGORY_PRODUCTION, 'description' => 'Global production rate multiplier'],
            ['key' => 'energy_efficiency', 'value' => 1.0, 'type' => self::TYPE_FLOAT, 'category' => self::CATEGORY_PRODUCTION, 'description' => 'Energy efficiency factor'],
            ['key' => 'low_energy_production_penalty', 'value' => 0.5, 'type' => self::TYPE_FLOAT, 'category' => self::CATEGORY_PRODUCTION, 'description' => 'Production penalty when energy is insufficient'],
            
            // Storage
            ['key' => 'storage_rate', 'value' => 1.0, 'type' => self::TYPE_FLOAT, 'category' => self::CATEGORY_STORAGE, 'description' => 'Global storage capacity multiplier'],
            ['key' => 'max_storage_overflow', 'value' => 1.1, 'type' => self::TYPE_FLOAT, 'category' => self::CATEGORY_STORAGE, 'description' => 'Maximum storage overflow percentage'],
            
            // Research
            ['key' => 'research_speed', 'value' => 1.0, 'type' => self::TYPE_FLOAT, 'category' => self::CATEGORY_RESEARCH, 'description' => 'Global research speed multiplier'],
            ['key' => 'starting_research_points', 'value' => 0, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_RESEARCH, 'description' => 'Starting research points for new players'],
            
            // Building
            ['key' => 'building_speed', 'value' => 1.0, 'type' => self::TYPE_FLOAT, 'category' => self::CATEGORY_BUILDING, 'description' => 'Global building speed multiplier'],
            
            // Planet
            ['key' => 'total_planets', 'value' => 1000, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_PLANET, 'description' => 'Total number of planets in server'],
            ['key' => 'galaxies', 'value' => 5, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_PLANET, 'description' => 'Number of galaxies'],
            ['key' => 'systems_per_galaxy', 'value' => 200, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_PLANET, 'description' => 'Systems per galaxy'],
            ['key' => 'planets_per_system', 'value' => 15, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_PLANET, 'description' => 'Planets per system'],
            ['key' => 'max_planet_equips_normal', 'value' => 2, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_PLANET, 'description' => 'Max équipes (teams) par planète pour comptes normaux'],
            ['key' => 'max_planet_equips_vip', 'value' => 4, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_PLANET, 'description' => 'Max équipes (teams) par planète pour comptes VIP'],
            
            // User
            ['key' => 'max_planets_per_user', 'value' => 9, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_USER, 'description' => 'Maximum planets per user'],
            ['key' => 'newbie_protection_enabled', 'value' => true, 'type' => self::TYPE_BOOLEAN, 'category' => self::CATEGORY_USER, 'description' => 'Enable newbie protection'],
            ['key' => 'newbie_protection_limit', 'value' => 50000, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_USER, 'description' => 'Newbie protection points limit'],
            ['key' => 'vacation_mode_enabled', 'value' => true, 'type' => self::TYPE_BOOLEAN, 'category' => self::CATEGORY_USER, 'description' => 'Enable vacation mode feature'],
            ['key' => 'vacation_mode_min_days', 'value' => 2, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_USER, 'description' => 'Minimum vacation mode duration in days'],
            ['key' => 'vacation_mode_max_days', 'value' => 30, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_USER, 'description' => 'Maximum vacation mode duration in days'],
            ['key' => 'max_bookmarks_normal', 'value' => 10, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_USER, 'description' => 'Max bookmarks par utilisateur (comptes normaux)'],
            ['key' => 'max_bookmarks_vip', 'value' => 25, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_USER, 'description' => 'Max bookmarks par utilisateur (comptes VIP)'],
            
            // Fleet
            ['key' => 'fleet_speed', 'value' => 1.0, 'type' => self::TYPE_FLOAT, 'category' => self::CATEGORY_FLEET, 'description' => 'Global fleet speed multiplier'],
            ['key' => 'fleet_save_time_limit', 'value' => 24, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_FLEET, 'description' => 'Fleet save time limit in hours'],
            
            // Economy
            ['key' => 'starting_resources', 'value' => json_encode(['metal' => 500, 'crystal' => 300, 'deuterium' => 100]), 'type' => self::TYPE_JSON, 'category' => self::CATEGORY_ECONOMY, 'description' => 'Starting resources for new players'],
            ['key' => 'trade_ratio', 'value' => json_encode(['metal_crystal' => 2.0, 'metal_deuterium' => 4.0, 'crystal_deuterium' => 2.0]), 'type' => self::TYPE_JSON, 'category' => self::CATEGORY_ECONOMY, 'description' => 'Trade ratios between resources'],
            
            // Combat
            ['key' => 'debris_field_percentage', 'value' => 0.3, 'type' => self::TYPE_FLOAT, 'category' => self::CATEGORY_COMBAT, 'description' => 'Debris field percentage after combat'],
            ['key' => 'combat_report_retention_days', 'value' => 30, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_COMBAT, 'description' => 'Combat report retention in days']
        ];

        foreach ($defaults as $config) {
            self::firstOrCreate(
                ['key' => $config['key']],
                $config + ['is_active' => true]
            );
        }
    }

    /**
     * Scope for active configurations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
    

}