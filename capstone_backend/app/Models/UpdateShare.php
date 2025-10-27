<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpdateShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'update_id',
        'user_id',
        'platform',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the update that was shared
     */
    public function update(): BelongsTo
    {
        return $this->belongsTo(Update::class, 'update_id');
    }

    /**
     * Get the user who shared the update
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
