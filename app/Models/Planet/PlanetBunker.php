<?php

namespace App\Models\Planet;

use App\Models\Template\TemplateResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanetBunker extends Model
{
    use HasFactory;

    protected $table = 'planet_bunkers';

    protected $fillable = [
        'planet_id',
        'resource_id',
        'stored_amount',
        'max_storage',
        'last_update',
        'is_active'
    ];

    protected $casts = [
        'stored_amount' => 'integer',
        'max_storage' => 'integer',
        'last_update' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the planet this bunker belongs to
     */
    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planet::class);
    }

    /**
     * Get the template resource this bunker stores
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(TemplateResource::class, 'resource_id');
    }

    /**
     * Get storage usage percentage
     */
    public function getStorageUsagePercentage(): float
    {
        if ($this->max_storage <= 0) return 0;
        
        return min(100, ($this->stored_amount / $this->max_storage) * 100);
    }

    /**
     * Check if storage is full
     */
    public function isStorageFull(): bool
    {
        return $this->stored_amount >= $this->max_storage;
    }

    /**
     * Get available storage space
     */
    public function getAvailableStorage(): int
    {
        return max(0, $this->max_storage - $this->stored_amount);
    }

    /**
     * Store resources in the bunker
     */
    public function storeResource(int $amount): bool
    {
        $availableSpace = $this->getAvailableStorage();
        
        if ($availableSpace <= 0) {
            return false;
        }
        
        $amountToStore = min($amount, $availableSpace);
        $this->stored_amount += $amountToStore;
        $this->save();
        
        return true;
    }

    /**
     * Retrieve resources from the bunker
     */
    public function retrieveResource(int $amount): int
    {
        $amountToRetrieve = min($amount, $this->stored_amount);
        
        if ($amountToRetrieve <= 0) {
            return 0;
        }
        
        $this->stored_amount -= $amountToRetrieve;
        $this->save();
        
        return $amountToRetrieve;
    }
    
    /**
     * Get the transactions for this bunker
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PlanetBunkerTransaction::class, 'bunker_id');
    }
}