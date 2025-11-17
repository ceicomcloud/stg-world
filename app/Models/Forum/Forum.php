<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Forum extends Model
{
    use HasSlug;

    protected $fillable = [
        'category_id',
        'parent_id',
        'slug',
        'name',
        'description',
        'icon',
        'sort_order',
        'is_locked',
        'is_active',
        'last_post_id',
        'last_post_at'
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_active' => 'boolean',
        'last_post_at' => 'datetime'
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Forum::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Forum::class, 'parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(ForumTopic::class, 'forum_id')
                    ->where('is_active', true)
                    ->orderBy('is_pinned', 'desc')
                    ->orderBy('last_post_at', 'desc');
    }

    public function allTopics(): HasMany
    {
        return $this->hasMany(ForumTopic::class, 'forum_id')
                    ->orderBy('is_pinned', 'desc')
                    ->orderBy('last_post_at', 'desc');
    }

    public function lastPost(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class, 'last_post_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function isSubforum(): bool
    {
        return !is_null($this->parent_id);
    }

    public function posts_count()
    {
        $count = 0;

        foreach ($this->topics as $topic) {
            $count += $topic->posts->count();
        }

        return $count;
    }

    public function topics_count()
    {
        return $this->topics()->count();
    }
}
