<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the comment that was liked
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(UpdateComment::class, 'comment_id');
    }

    /**
     * Get the user who liked the comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
