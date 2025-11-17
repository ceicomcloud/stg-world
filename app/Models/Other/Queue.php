<?php

namespace App\Models\Other;

use App\Models\Planet\Planet;
use App\Models\User;
use App\Models\Template\TemplateBuild;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'planet_id',
        'user_id',
        'type',
        'item_id',
        'level',
        'quantity',
        'start_time',
        'end_time',
        'duration',
        'cost',
        'is_active',
        'is_completed',
        'completed_at',
        'position'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'completed_at' => 'datetime',
        'cost' => 'array',
        'is_active' => 'boolean',
        'is_completed' => 'boolean'
    ];

    // Types de construction possibles
    const TYPE_BUILDING = 'building';
    const TYPE_UNIT = 'unit';
    const TYPE_DEFENSE = 'defense';
    const TYPE_SHIP = 'ship';
    const TYPE_TECHNOLOGY = 'technology';

    /**
     * Relation avec la planète
     */
    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planet::class);
    }

    /**
     * Relation avec l'utilisateur (pour les technologies)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec l'élément à construire
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(TemplateBuild::class, 'item_id');
    }

    /**
     * Scope pour les éléments actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les éléments non complétés
     */
    public function scopeNotCompleted($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope pour une planète spécifique
     */
    public function scopeForPlanet($query, $planetId)
    {
        return $query->where('planet_id', $planetId);
    }

    /**
     * Scope pour un utilisateur spécifique
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour un type spécifique
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour ordonner par position
     */
    public function scopeOrderedByPosition($query)
    {
        return $query->orderBy('position');
    }

    /**
     * Vérifie si l'élément est terminé
     */
    public function isFinished(): bool
    {
        return $this->end_time <= now();
    }

    public function getTimeRemainingBlade(): string
    {
        if (!$this->end_time) {
            return 'Temps non défini';
        }

        $now = Carbon::now();
        
        if ($now->gte($this->end_time)) {
            // Éviter l'affichage 00:00:00 qui peut poser problème côté UI
            // Si l'item est encore marqué actif et non complété, forcer un minimum visuel de 00:00:30
            if ($this->is_active && !$this->is_completed) {
                return '00:00:30';
            }
            return '00:00:00';
        }

        $diff = $this->end_time->diff($now);
        
        $hours = $diff->h + ($diff->days * 24);
        $minutes = $diff->i;
        $seconds = $diff->s;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Alias utilisé par les vues: retourne HH:MM:SS avec un minimum de 30s pour les items actifs
     */
    public function getTimeRemaining(): string
    {
        // Utilise la logique de calcul mais applique un clamp minimum pour les items actifs
        $seconds = $this->getRemainingTime();

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }


    /**
     * Calcule le temps restant en secondes
     */
    public function getRemainingTime(): int
    {
        if (!$this->end_time) {
            return 0;
        }

        // Temps restant jusqu'à la fin (valeur signée)
        $remainingTime = $this->end_time->diffInSeconds(now(), false);

        // S'assurer que le temps restant n'est jamais négatif
        return max(0, $remainingTime);
    }

    /**
     * Calcule le pourcentage de progression
     */
    public function getProgressPercentage(): float
    {
        $totalDuration = $this->duration;
        $elapsed = now()->diffInSeconds($this->start_time);
        
        if ($elapsed >= $totalDuration) {
            return 100.0;
        }
        
        return ($elapsed / $totalDuration) * 100;
    }

    /**
     * Marque l'élément comme terminé
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now()
        ]);
    }

    /**
     * Annule la construction
     */
    public function cancel(): void
    {
        $this->update([
            'is_active' => false
        ]);
        
        // Réorganiser les positions
        $this->reorganizeQueue();
    }

    /**
     * Réorganise la file d'attente après suppression
     */
    private function reorganizeQueue(): void
    {
        $query = static::active()->notCompleted();
        
        if ($this->planet_id) {
            $query->forPlanet($this->planet_id);
        } else {
            $query->forUser($this->user_id);
        }
        
        $items = $query->where('position', '>', $this->position)
                      ->orderBy('position')
                      ->get();
        
        foreach ($items as $item) {
            $item->update(['position' => $item->position - 1]);
        }
    }

    /**
     * Ajoute un nouvel élément à la file
     */
    public static function addToQueue(array $data): self
    {
        // Calculer la position
        $query = static::active()->notCompleted();
        
        if (isset($data['planet_id'])) {
            $query->forPlanet($data['planet_id']);
        } else {
            $query->forUser($data['user_id']);
        }
        
        // Scope par type pour éviter le blocage entre types (bâtiments/unités/défenses/vaisseaux)
        if (isset($data['type'])) {
            $query->ofType($data['type']);
        }
        
        $position = $query->max('position') + 1;
        
        // Calculer les temps de début et fin
        $lastItem = $query->orderBy('position', 'desc')->first();
        
        if ($lastItem && !$lastItem->isFinished()) {
            $startTime = $lastItem->end_time;
        } else {
            $startTime = now();
        }
        
        // Appliquer une durée minimale de 30 secondes
        $duration = max(30, (int) ($data['duration'] ?? 0));

        $endTime = $startTime->copy()->addSeconds($duration);

        return static::create(array_merge($data, [
            'position' => $position,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration' => $duration,
        ]));
    }

    /**
     * Vérifie s'il y a une file d'attente active pour une planète
     */
    public static function hasActiveQueue($planetId, $type = null): bool
    {
        $query = static::active()
                      ->notCompleted()
                      ->forPlanet($planetId);
        
        if ($type) {
            $query->ofType($type);
        }
        
        return $query->exists();
    }

    /**
     * Count active queue items for a planet and optional type.
     */
    public static function countActiveQueue($planetId, $type = null): int
    {
        $query = static::active()
                       ->notCompleted()
                       ->forPlanet($planetId);

        if ($type) {
            $query->ofType($type);
        }

        return (int) $query->count();
    }

    /**
     * Obtient la file d'attente pour une planète
     */
    public static function getQueueForPlanet($planetId, $type = null)
    {
        $query = static::active()
                      ->notCompleted()
                      ->forPlanet($planetId)
                      ->orderedByPosition()
                      ->with('item');
        
        if ($type) {
            $query->ofType($type);
        }
        
        return $query->get();
    }

    /**
     * Obtient la file d'attente pour un utilisateur (technologies)
     */
    public static function getQueueForUser($userId)
    {
        return static::active()
                    ->notCompleted()
                    ->forUser($userId)
                    ->ofType(static::TYPE_TECHNOLOGY)
                    ->orderedByPosition()
                    ->with('item')
                    ->get();
    }

    /**
     * Obtient les éléments terminés pour une planète
     */
    public static function getCompletedItems($planetId, $type = null)
    {
        $query = static::active()
                      ->notCompleted()
                      ->forPlanet($planetId)
                      ->where('end_time', '<=', now());
        
        if ($type) {
            $query->ofType($type);
        }
        
        return $query->get();
    }

    /**
     * Traite les éléments terminés
     */
    public static function processCompletedItems(int $batchSize = 1000, int $sleepMs = 0): int
    {
        $processed = 0;

        $query = static::active()
            ->notCompleted()
            ->where('end_time', '<=', now())
            ->orderBy('id');

        $query->chunkById($batchSize, function ($items) use (&$processed, $sleepMs) {
            foreach ($items as $item) {
                try {
                    $item->processCompletion();
                    $processed++;
                } catch (\Throwable $e) {
                    Log::error('Erreur lors du traitement d\'un élément de file', [
                        'queue_id' => $item->id ?? null,
                        'type' => $item->type ?? null,
                        'planet_id' => $item->planet_id ?? null,
                        'user_id' => $item->user_id ?? null,
                        'error' => $e->getMessage(),
                    ]);
                    // Continuer avec les autres éléments
                }
            }
            if ($sleepMs > 0) {
                usleep($sleepMs * 1000);
            }
        }, 'id');

        return $processed;
    }

    /**
     * Alias pour processCompletion() pour compatibilité
     */
    public function complete(): void
    {
        $this->processCompletion();
    }

    /**
     * Traite la finalisation d'un élément
     */
    public function processCompletion(): void
    {
        switch ($this->type) {
            case static::TYPE_BUILDING:
                $this->completeBuildingConstruction();
                break;
            case static::TYPE_UNIT:
                $this->completeUnitConstruction();
                break;
            case static::TYPE_DEFENSE:
                $this->completeDefenseConstruction();
                break;
            case static::TYPE_SHIP:
                $this->completeShipConstruction();
                break;
            case static::TYPE_TECHNOLOGY:
                $this->completeTechnologyResearch();
                break;
        }
        
        $this->markAsCompleted();
    }

    /**
     * Finalise la construction d'un bâtiment
     */
    private function completeBuildingConstruction(): void
    {
        $building = $this->planet->buildings()->where('building_id', $this->item_id)->first();
        
        if ($building) {
            // Use update() instead of increment() to trigger model events
            $building->update(['level' => $building->level + 1]);
        } else {
            // Create new building - this will trigger the created event
            $this->planet->buildings()->create([
                'building_id' => $this->item_id,
                'level' => $this->level,
                'is_active' => true
            ]);
        }
    }

    /**
     * Finalise la construction d'unités
     */
    private function completeUnitConstruction(): void
    {
        $unit = $this->planet->units()->where('unit_id', $this->item_id)->first();
        
        if ($unit) {
            $unit->increment('quantity', $this->quantity);
        } else {
            $this->planet->units()->create([
                'unit_id' => $this->item_id,
                'quantity' => $this->quantity
            ]);
        }
    }

    /**
     * Finalise la construction de défenses
     */
    private function completeDefenseConstruction(): void
    {
        $defense = $this->planet->defenses()->where('defense_id', $this->item_id)->first();
        
        if ($defense) {
            $defense->increment('quantity', $this->quantity);
        } else {
            $this->planet->defenses()->create([
                'defense_id' => $this->item_id,
                'quantity' => $this->quantity
            ]);
        }
    }

    /**
     * Finalise la construction de vaisseaux
     */
    private function completeShipConstruction(): void
    {
        $ship = $this->planet->ships()->where('ship_id', $this->item_id)->first();
        
        if ($ship) {
            $ship->increment('quantity', $this->quantity);
        } else {
            $this->planet->ships()->create([
                'ship_id' => $this->item_id,
                'quantity' => $this->quantity
            ]);
        }
    }

    /**
     * Finalise la recherche de technologie
     */
    private function completeTechnologyResearch(): void
    {
        $technology = $this->user->technologies()->where('technology_id', $this->item_id)->first();
        
        if ($technology) {
            $technology->increment('level');
        } else {
            $this->user->technologies()->create([
                'technology_id' => $this->item_id,
                'level' => $this->level
            ]);
        }
    }
}