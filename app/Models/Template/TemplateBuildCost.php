<?php

namespace App\Models\Template;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateBuildCost extends Model
{
    use HasFactory;

    protected $table = 'template_build_costs';

    protected $fillable = [
        'build_id',
        'resource_id',
        'base_cost',
        'cost_multiplier',
        'level'
    ];

    protected $casts = [
        'base_cost' => 'integer',
        'cost_multiplier' => 'float',
        'level' => 'integer'
    ];

    /**
     * Get the build this cost belongs to
     */
    public function build(): BelongsTo
    {
        return $this->belongsTo(TemplateBuild::class, 'build_id');
    }

    /**
     * Get the resource this cost uses
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(TemplateResource::class, 'resource_id');
    }

    /**
     * Calculate cost for a specific level
     */
    public function calculateCostForLevel(int $level): int
    {
        if ($level <= 0) {
            return 0;
        }

        // Formula: base_cost * (cost_multiplier ^ (level - 1))
        return (int) ($this->base_cost * pow($this->cost_multiplier, $level - 1));
    }

    /**
     * Get total cost from level 1 to target level
     */
    public function getTotalCostToLevel(int $targetLevel): int
    {
        $totalCost = 0;
        for ($i = 1; $i <= $targetLevel; $i++) {
            $totalCost += $this->calculateCostForLevel($i);
        }
        return $totalCost;
    }

    /**
     * Scope for specific build
     */
    public function scopeForBuild($query, $buildId)
    {
        return $query->where('build_id', $buildId);
    }

    /**
     * Scope for specific resource
     */
    public function scopeForResource($query, $resourceId)
    {
        return $query->where('resource_id', $resourceId);
    }

    /**
     * Scope for specific level
     */
    public function scopeForLevel($query, $level)
    {
        return $query->where('level', $level);
    }
}