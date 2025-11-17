<?php

namespace App\Models\Server;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServerNews extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'server_news';

    protected $fillable = [
        'title',
        'content',
        'excerpt',
        'author_id',
        'category',
        'priority',
        'is_published',
        'is_pinned',
        'published_at',
        'expires_at',
        'image_url',
        'external_url',
        'tags',
        'view_count',
        'is_active'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_pinned' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'tags' => 'array',
        'view_count' => 'integer',
        'is_active' => 'boolean'
    ];

    protected $dates = [
        'published_at',
        'expires_at',
        'deleted_at'
    ];

    // News categories
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_UPDATE = 'update';
    const CATEGORY_MAINTENANCE = 'maintenance';
    const CATEGORY_EVENT = 'event';
    const CATEGORY_ANNOUNCEMENT = 'announcement';
    const CATEGORY_PATCH = 'patch';
    const CATEGORY_COMPETITION = 'competition';
    const CATEGORY_COMMUNITY = 'community';

    // News priorities
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Get the author of this news
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Check if news is currently published and active
     */
    public function isCurrentlyPublished(): bool
    {
        if (!$this->is_published || !$this->is_active) {
            return false;
        }

        $now = now();
        
        // Check if published date has passed
        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        // Check if not expired
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if news is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if news is scheduled for future
     */
    public function isScheduled(): bool
    {
        return $this->published_at && $this->published_at->isFuture();
    }

    /**
     * Get time until publication
     */
    public function getTimeUntilPublication(): ?int
    {
        if (!$this->isScheduled()) {
            return null;
        }

        return $this->published_at->diffInSeconds(now());
    }

    /**
     * Get time until expiration
     */
    public function getTimeUntilExpiration(): ?int
    {
        if (!$this->expires_at || $this->isExpired()) {
            return null;
        }

        return $this->expires_at->diffInSeconds(now());
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Get formatted excerpt or generate from content
     */
    public function getFormattedExcerpt(int $length = 150): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        // Generate excerpt from content
        $content = strip_tags($this->content);
        return strlen($content) > $length 
            ? substr($content, 0, $length) . '...' 
            : $content;
    }

    /**
     * Get reading time estimate in minutes
     */
    public function getReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $wordsPerMinute = 200; // Average reading speed
        
        return max(1, ceil($wordCount / $wordsPerMinute));
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColor(): string
    {
        return match($this->priority) {
            self::PRIORITY_URGENT => 'red',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_NORMAL => 'blue',
            self::PRIORITY_LOW => 'gray',
            default => 'blue'
        };
    }

    /**
     * Get category icon for UI
     */
    public function getCategoryIcon(): string
    {
        return match($this->category) {
            self::CATEGORY_UPDATE => 'refresh',
            self::CATEGORY_MAINTENANCE => 'wrench',
            self::CATEGORY_EVENT => 'calendar',
            self::CATEGORY_ANNOUNCEMENT => 'megaphone',
            self::CATEGORY_PATCH => 'download',
            self::CATEGORY_COMPETITION => 'trophy',
            self::CATEGORY_COMMUNITY => 'users',
            default => 'info'
        };
    }

    /**
     * Publish news immediately
     */
    public function publish(): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => now()
        ]);
    }

    /**
     * Unpublish news
     */
    public function unpublish(): void
    {
        $this->update([
            'is_published' => false
        ]);
    }

    /**
     * Schedule news for future publication
     */
    public function schedule($publishAt): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => $publishAt
        ]);
    }

    /**
     * Pin news to top
     */
    public function pin(): void
    {
        $this->update(['is_pinned' => true]);
    }

    /**
     * Unpin news
     */
    public function unpin(): void
    {
        $this->update(['is_pinned' => false]);
    }

    /**
     * Set expiration date
     */
    public function setExpiration($expiresAt): void
    {
        $this->update(['expires_at' => $expiresAt]);
    }

    /**
     * Remove expiration
     */
    public function removeExpiration(): void
    {
        $this->update(['expires_at' => null]);
    }

    /**
     * Add tag to news
     */
    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    /**
     * Remove tag from news
     */
    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        $tags = array_filter($tags, fn($t) => $t !== $tag);
        $this->update(['tags' => array_values($tags)]);
    }

    /**
     * Check if news has specific tag
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    /**
     * Get latest published news
     */
    public static function getLatestNews(int $limit = 10)
    {
        return self::published()
            ->active()
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get news by category
     */
    public static function getByCategory(string $category, int $limit = 10)
    {
        return self::published()
            ->active()
            ->where('category', $category)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get pinned news
     */
    public static function getPinnedNews()
    {
        return self::published()
            ->active()
            ->where('is_pinned', true)
            ->orderBy('published_at', 'desc')
            ->get();
    }

    /**
     * Get urgent news
     */
    public static function getUrgentNews()
    {
        return self::published()
            ->active()
            ->where('priority', self::PRIORITY_URGENT)
            ->orderBy('published_at', 'desc')
            ->get();
    }

    /**
     * Search news by title or content
     */
    public static function search(string $query, int $limit = 20)
    {
        return self::published()
            ->active()
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%");
            })
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get news statistics
     */
    public static function getStatistics(): array
    {
        return [
            'total' => self::count(),
            'published' => self::published()->count(),
            'draft' => self::draft()->count(),
            'scheduled' => self::scheduled()->count(),
            'expired' => self::expired()->count(),
            'pinned' => self::pinned()->count(),
            'total_views' => self::sum('view_count'),
            'categories' => self::published()
                ->active()
                ->groupBy('category')
                ->selectRaw('category, count(*) as count')
                ->pluck('count', 'category')
                ->toArray()
        ];
    }

    /**
     * Scope for published news
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for draft news
     */
    public function scopeDraft($query)
    {
        return $query->where('is_published', false);
    }

    /**
     * Scope for scheduled news
     */
    public function scopeScheduled($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '>', now());
    }

    /**
     * Scope for expired news
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Scope for pinned news
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope for active news
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
     * Scope by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope by author
     */
    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    /**
     * Scope by tag
     */
    public function scopeByTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Scope for recent news (last 30 days)
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('published_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for popular news (high view count)
     */
    public function scopePopular($query, $minViews = 100)
    {
        return $query->where('view_count', '>=', $minViews);
    }
}