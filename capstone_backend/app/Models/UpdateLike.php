<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpdateLike extends Model
{
    use HasFactory;

    protected $table = 'update_likes';

    protected $fillable = [
        'update_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the update that was liked
     */
    public function updatePost(): BelongsTo
    {
        return $this->belongsTo(Update::class, 'update_id');
    }

    /**
     * Get the user who liked the update
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
