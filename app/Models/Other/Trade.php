<?php

namespace App\Models\Other;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Planet\Planet;
use App\Models\Template\TemplateResource;
class Trade extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'buyer_id',
        'seller_planet_id',
        'buyer_planet_id',
        'offered_resource_id',
        'offered_amount',
        'requested_resource_id',
        'requested_amount',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'offered_amount' => 'integer',
        'requested_amount' => 'integer',
    ];

    /**
     * Relation avec le vendeur
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Relation avec l'acheteur
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Relation avec la planète du vendeur
     */
    public function sellerPlanet(): BelongsTo
    {
        return $this->belongsTo(Planet::class, 'seller_planet_id');
    }

    /**
     * Relation avec la planète de l'acheteur
     */
    public function buyerPlanet(): BelongsTo
    {
        return $this->belongsTo(Planet::class, 'buyer_planet_id');
    }

    /**
     * Relation avec la ressource offerte
     */
    public function offeredResource(): BelongsTo
    {
        return $this->belongsTo(TemplateResource::class, 'offered_resource_id');
    }

    /**
     * Relation avec la ressource demandée
     */
    public function requestedResource(): BelongsTo
    {
        return $this->belongsTo(TemplateResource::class, 'requested_resource_id');
    }

    /**
     * Scope pour les offres actives (en attente et non expirées)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope pour les offres d'un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('seller_id', $userId);
    }

    /**
     * Scope pour les offres disponibles pour un utilisateur (excluant ses propres offres)
     */
    public function scopeAvailableFor($query, $userId)
    {
        return $query->where('seller_id', '!=', $userId)
                    ->where('status', 'pending');
    }

    /**
     * Vérifie si l'offre est expirée
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Vérifie si l'offre peut être acceptée
     */
    public function canBeAccepted(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Marque l'offre comme acceptée
     */
    public function accept(User $buyer, Planet $buyerPlanet): bool
    {
        if (!$this->canBeAccepted()) {
            return false;
        }

        $this->update([
            'buyer_id' => $buyer->id,
            'buyer_planet_id' => $buyerPlanet->id,
            'status' => 'accepted',
        ]);

        return true;
    }

    /**
     * Annule l'offre
     */
    public function cancel(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update(['status' => 'cancelled']);
        return true;
    }

    /**
     * Marque l'offre comme complétée
     */
    public function complete(): bool
    {
        if ($this->status !== 'accepted') {
            return false;
        }

        $this->update(['status' => 'completed']);
        return true;
    }

    /**
     * Calcule le ratio d'échange
     */
    public function getExchangeRatio(): float
    {
        if ($this->offered_amount == 0) {
            return 0;
        }

        return $this->requested_amount / $this->offered_amount;
    }

    /**
     * Formate le temps restant avant expiration
     */
    public function getTimeRemaining(): ?string
    {
        if (!$this->expires_at) {
            return null;
        }

        if ($this->isExpired()) {
            return 'Expiré';
        }

        return $this->expires_at->diffForHumans();
    }

    /**
     * Vérifie si le vendeur a suffisamment de ressources
     */
    public function sellerHasEnoughResources(): bool
    {
        $planetResource = $this->sellerPlanet->resources()
            ->where('template_resource_id', $this->offered_resource_id)
            ->first();

        return $planetResource && $planetResource->current_amount >= $this->offered_amount;
    }

    /**
     * Vérifie si l'acheteur a suffisamment de ressources
     */
    public function buyerHasEnoughResources(): bool
    {
        if (!$this->buyer_planet_id) {
            return false;
        }

        $planetResource = $this->buyerPlanet->resources()
            ->where('template_resource_id', $this->requested_resource_id)
            ->first();

        return $planetResource && $planetResource->current_amount >= $this->requested_amount;
    }
}