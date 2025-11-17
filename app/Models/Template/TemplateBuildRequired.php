<?php

namespace App\Models\Template;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateBuildRequired extends Model
{
    use HasFactory;

    protected $table = 'template_build_requireds';

    protected $fillable = [
        'build_id',
        'required_build_id',
        'required_level',
        'is_active'
    ];

    protected $casts = [
        'required_level' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Get the build this requirement belongs to
     */
    public function build(): BelongsTo
    {
        return $this->belongsTo(TemplateBuild::class, 'build_id');
    }

    /**
     * Get the required build
     */
    public function requiredBuild(): BelongsTo
    {
        return $this->belongsTo(TemplateBuild::class, 'required_build_id');
    }

    /**
     * Check if requirement is met for a planet
     */
    public function isMetForPlanet($planetId): bool
    {
        $requiredBuild = $this->requiredBuild;
        
        if ($requiredBuild->isBuilding()) {
            $planetBuilding = \App\Models\Planet\PlanetBuilding::where('planet_id', $planetId)
                ->where('build_id', $this->required_build_id)
                ->where('is_active', true)
                ->first();
            
            return $planetBuilding && $planetBuilding->level >= $this->required_level;
        }
        
        if ($requiredBuild->isResearch()) {
            // For research, check user technology level
            $planet = \App\Models\Planet\Planet::find($planetId);
            if (!$planet) return false;
            
            $userTechnology = \App\Models\User\UserTechnology::where('user_id', $planet->user_id)
                ->where('technology_id', $this->required_build_id)
                ->first();
            
            return $userTechnology && $userTechnology->level >= $this->required_level;
        }
        
        return false;
    }

    /**
     * Check if requirement is met for a user (for research)
     */
    public function isMetForUser($userId): bool
    {
        $requiredBuild = $this->requiredBuild;
        
        if ($requiredBuild->isResearch()) {
            $userTechnology = \App\Models\User\UserTechnology::where('user_id', $userId)
                ->where('technology_id', $this->required_build_id)
                ->first();
            
            return $userTechnology && $userTechnology->level >= $this->required_level;
        }
        
        // For buildings, we need to check if user has any planet with this building
        if ($requiredBuild->isBuilding()) {
            $userPlanets = \App\Models\Planet\Planet::where('user_id', $userId)->pluck('id');
            
            foreach ($userPlanets as $planetId) {
                if ($this->isMetForPlanet($planetId)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Scope for active requirements
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific build
     */
    public function scopeForBuild($query, $buildId)
    {
        return $query->where('build_id', $buildId);
    }

    /**
     * Scope for specific required build
     */
    public function scopeForRequiredBuild($query, $requiredBuildId)
    {
        return $query->where('required_build_id', $requiredBuildId);
    }
}