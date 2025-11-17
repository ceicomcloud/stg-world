<?php

namespace App\Models\Planet;

use App\Models\Template\TemplateResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanetResource extends Model
{
    use HasFactory;

    protected $table = 'planet_resources';

    protected $fillable = [
        'planet_id',
        'resource_id',
        'current_amount',
        'production_rate',
        'last_update',
        'is_active'
    ];

    protected $casts = [
        'current_amount' => 'integer',
        'base_production' => 'integer',
        'production_rate' => 'float',
        'last_update' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the planet this resource belongs to
     */
    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planet::class);
    }

    /**
     * Get the template resource this is based on
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(TemplateResource::class, 'resource_id');
    }

    /**
     * Get current production per hour
     */
    public function getCurrentProductionPerHour(): float
    {
        return $this->planet->getResourceProductionRate($this->resource_id);
    }

    /**
     * Get current production per second
     */
    public function getCurrentProductionPerSecond(): float
    {
        return $this->getCurrentProductionPerHour() / 3600;
    }

    /**
     * Get storage capacity for this resource
     */
    public function getStorageCapacity(): int
    {
        return $this->planet->getStorageCapacity($this->resource_id);
    }

    /**
     * Get storage usage percentage
     */
    public function getStorageUsagePercentage(): float
    {
        $capacity = $this->getStorageCapacity();
        if ($capacity <= 0) return 0;
        
        return min(100, ($this->current_amount / $capacity) * 100);
    }

    /**
     * Check if storage is full
     */
    public function isStorageFull(): bool
    {
        return $this->current_amount >= $this->getStorageCapacity();
    }

    /**
     * Get available storage space
     */
    public function getAvailableStorage(): int
    {
        return max(0, $this->getStorageCapacity() - $this->current_amount);
    }

    /**
     * Update resource amount based on production
     */
    public function updateProduction(): void
    {
        if (!$this->last_update) {
            $this->update(['last_update' => now()]);
            return;
        }

        $secondsSinceLastUpdate = now()->diffInSeconds($this->last_update);
        $productionPerSecond = $this->getCurrentProductionPerSecond();
        $producedAmount = $productionPerSecond * $secondsSinceLastUpdate;

        // Add produced amount but don't exceed storage capacity
        $newAmount = min(
            $this->getStorageCapacity(),
            $this->current_amount + $producedAmount
        );

        $this->update([
            'current_amount' => $newAmount,
            'last_update' => now()
        ]);
    }

    /**
     * Add resources to current amount
     */
    public function addResources(int $amount): int
    {
        $availableSpace = $this->getAvailableStorage();
        $actualAmount = min($amount, $availableSpace);
        
        $this->increment('current_amount', $actualAmount);
        
        return $actualAmount; // Return how much was actually added
    }

    /**
     * Remove resources from current amount
     */
    public function removeResources(int $amount): bool
    {
        if ($this->current_amount < $amount) {
            return false;
        }

        $this->decrement('current_amount', $amount);
        return true;
    }

    /**
     * Check if planet has enough of this resource
     */
    public function hasEnough(int $amount): bool
    {
        return $this->current_amount >= $amount;
    }

    /**
     * Set production rate (percentage)
     */
    public function setProductionRate(float $rate): void
    {
        // Ensure rate is between 0% and 200% (allowing overproduction)
        $rate = max(0, min(200, $rate));
        $this->update(['production_rate' => $rate]);
    }

    /**
     * Increase production rate
     */
    public function increaseProductionRate(float $increase = 10): void
    {
        $newRate = min(200, $this->production_rate + $increase);
        $this->setProductionRate($newRate);
    }

    /**
     * Decrease production rate
     */
    public function decreaseProductionRate(float $decrease = 10): void
    {
        $newRate = max(0, $this->production_rate - $decrease);
        $this->setProductionRate($newRate);
    }

    /**
     * Get time until storage is full (in seconds)
     */
    public function getTimeUntilStorageFull(): ?int
    {
        $productionPerSecond = $this->getCurrentProductionPerSecond();
        
        if ($productionPerSecond <= 0) {
            return null; // Never fills up
        }

        $availableSpace = $this->getAvailableStorage();
        
        if ($availableSpace <= 0) {
            return 0; // Already full
        }

        return (int) ($availableSpace / $productionPerSecond);
    }

    /**
     * Get estimated amount after specific time
     */
    public function getEstimatedAmountAfter(int $seconds): int
    {
        $productionPerSecond = $this->getCurrentProductionPerSecond();
        $producedAmount = $productionPerSecond * $seconds;
        
        return min(
            $this->getStorageCapacity(),
            $this->current_amount + $producedAmount
        );
    }

    /**
     * Get production efficiency based on energy
     */
    public function getProductionEfficiency(): float
    {
        if (!$this->planet->hasEnoughEnergy()) {
            $energyRatio = max(0, $this->planet->getNetEnergy() / abs($this->planet->getEnergyConsumption()));
            return $energyRatio;
        }
        
        return 1.0; // 100% efficiency
    }

    /**
     * Update all planet resources production
     */
    public static function updatePlanetProduction($planetId): void
    {
        $resources = self::where('planet_id', $planetId)
            ->where('is_active', true)
            ->get();
            
        foreach ($resources as $resource) {
            $resource->updateProduction();
        }
    }

    /**
     * Get planet's total resource value (for ranking)
     */
    public static function getPlanetResourceValue($planetId): int
    {
        return self::where('planet_id', $planetId)
            ->where('is_active', true)
            ->sum('current_amount');
    }

    /**
     * Scope for active resources
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by planet
     */
    public function scopeByPlanet($query, $planetId)
    {
        return $query->where('planet_id', $planetId);
    }

    /**
     * Scope by resource type
     */
    public function scopeByResourceType($query, $resourceId)
    {
        return $query->where('resource_id', $resourceId);
    }

    /**
     * Scope for resources with production
     */
    public function scopeWithProduction($query)
    {
        return $query->where('base_production', '>', 0);
    }

    /**
     * Scope for resources near storage limit
     */
    public function scopeNearStorageLimit($query, $percentage = 90)
    {
        return $query->whereRaw('(current_amount / ?) >= ?', [
            // This would need to be calculated properly with storage capacity
            // For now, just a placeholder
            1000000, // placeholder storage capacity
            $percentage / 100
        ]);
    }

    /**
     * Scope for resources with low amounts
     */
    public function scopeLowAmount($query, $threshold = 1000)
    {
        return $query->where('current_amount', '<', $threshold);
    }
}