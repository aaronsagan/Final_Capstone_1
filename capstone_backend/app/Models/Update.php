<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Update extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'charity_id',
        'parent_id',
        'content',
        'media_urls',
        'is_pinned',
        'likes_count',
        'comments_count',
        'shares_count',
    ];

    protected $casts = [
        'media_urls' => 'array',
        'is_pinned' => 'boolean',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'shares_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // Append computed attributes to JSON
    protected $appends = ['is_liked'];

    protected $with = ['charity'];

    /**
     * Get the charity that owns the update
     */
    public function charity(): BelongsTo
    {
        return $this->belongsTo(Charity::class);
    }

    /**
     * Get the parent update (for threads)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Update::class, 'parent_id');
    }

    /**
     * Get the child updates (thread replies)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Update::class, 'parent_id')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get all likes for this update
     */
    public function likes(): HasMany
    {
        return $this->hasMany(UpdateLike::class);
    }

    /**
     * Get all comments for this update
     */
    public function comments(): HasMany
    {
        return $this->hasMany(UpdateComment::class)
            ->where('is_hidden', false)
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get all shares for this update
     */
    public function shares(): HasMany
    {
        return $this->hasMany(UpdateShare::class);
    }

    /**
     * Check if user has liked this update
     */
    public function isLikedBy($userId): bool
    {
        // Use direct DB query instead of relationship
        $result = \DB::table('update_likes')
            ->where('update_id', $this->id)
            ->where('user_id', $userId)
            ->exists();
        
        $count = \DB::table('update_likes')
            ->where('update_id', $this->id)
            ->where('user_id', $userId)
            ->count();
            
        \Log::info("isLikedBy(update={$this->id}, user={$userId}): result=" . ($result ? 'TRUE' : 'FALSE') . ", count={$count}");
        
        return $result;
    }
    
    /**
     * Accessor for is_liked attribute
     * Returns true if the authenticated user has liked this update
     */
    public function getIsLikedAttribute(): bool
    {
        $userId = auth()->id();
        if (!$userId) {
            return false;
        }
        return $this->isLikedBy($userId);
    }

    /**
     * Scope to get only root updates (not threaded)
     */
    public function scopeRootOnly($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get pinned updates
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
}
