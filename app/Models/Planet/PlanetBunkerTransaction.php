<?php

namespace App\Models\Planet;

use App\Models\Template\TemplateResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanetBunkerTransaction extends Model
{
    use HasFactory;

    protected $table = 'planet_bunker_transactions';

    protected $fillable = [
        'planet_id',
        'bunker_id',
        'resource_id',
        'user_id',
        'transaction_type',
        'amount',
        'bunker_amount_before',
        'bunker_amount_after',
        'planet_amount_before',
        'planet_amount_after',
    ];

    protected $casts = [
        'amount' => 'integer',
        'bunker_amount_before' => 'integer',
        'bunker_amount_after' => 'integer',
        'planet_amount_before' => 'integer',
        'planet_amount_after' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Types de transactions
     */
    const TYPE_STORE = 'store';
    const TYPE_RETRIEVE = 'retrieve';

    /**
     * Get the planet this transaction belongs to
     */
    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planet::class);
    }

    /**
     * Get the bunker this transaction belongs to
     */
    public function bunker(): BelongsTo
    {
        return $this->belongsTo(PlanetBunker::class, 'bunker_id');
    }

    /**
     * Get the resource this transaction is for
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(TemplateResource::class, 'resource_id');
    }

    /**
     * Get the user who performed this transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour filtrer par type de transaction
     */
    public function scopeByTransactionType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope pour filtrer par planète
     */
    public function scopeByPlanet($query, int $planetId)
    {
        return $query->where('planet_id', $planetId);
    }

    /**
     * Scope pour filtrer par bunker
     */
    public function scopeByBunker($query, int $bunkerId)
    {
        return $query->where('bunker_id', $bunkerId);
    }

    /**
     * Scope pour filtrer par ressource
     */
    public function scopeByResource($query, int $resourceId)
    {
        return $query->where('resource_id', $resourceId);
    }

    /**
     * Scope pour filtrer par utilisateur
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour les transactions récentes
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Obtenir le texte formaté du type de transaction
     */
    public function getFormattedTypeAttribute(): string
    {
        return match($this->transaction_type) {
            self::TYPE_STORE => 'Stockage',
            self::TYPE_RETRIEVE => 'Récupération',
            default => 'Inconnu'
        };
    }

    /**
     * Obtenir l'icône du type de transaction
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->transaction_type) {
            self::TYPE_STORE => 'fa-arrow-down',
            self::TYPE_RETRIEVE => 'fa-arrow-up',
            default => 'fa-question'
        };
    }
}