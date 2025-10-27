<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UpdateComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'update_id',
        'user_id',
        'content',
        'is_hidden',
        'likes_count',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $with = ['user'];

    /**
     * Get the update this comment belongs to
     */
    public function updatePost(): BelongsTo
    {
        return $this->belongsTo(Update::class, 'update_id');
    }

    /**
     * Get the user who wrote the comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the likes for this comment
     */
    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class, 'comment_id');
    }
}
