<?php

namespace App\Models\Forum;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumPost extends Model
{
    protected $fillable = [
        'topic_id',
        'user_id',
        'content',
        'is_active',
        'edited_at',
        'edited_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'edited_at' => 'datetime'
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function editedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ForumReport::class, 'post_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isEdited(): bool
    {
        return !is_null($this->edited_at);
    }

    public function canEdit(User $user): bool
    {
        return $this->user_id === $user->id || $user->isAdmin();
    }

    public function canDelete(User $user): bool
    {
        return $this->user_id === $user->id || $user->isAdmin();
    }

    public function markAsEdited(User $user)
    {
        $this->update([
            'edited_at' => now(),
            'edited_by' => $user->id
        ]);
    }
}
