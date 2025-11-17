<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ForumCategory extends Model
{
    use HasSlug;
    
    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'color',
        'sort_order',
        'is_locked',
        'is_active'
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function forums(): HasMany
    {
        return $this->hasMany(Forum::class, 'category_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }

    public function allForums(): HasMany
    {
        return $this->hasMany(Forum::class, 'category_id')
                    ->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
