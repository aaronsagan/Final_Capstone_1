<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActionLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action_type',
        'target_type',
        'target_id',
        'details',
        'notes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Static helper method to log actions
    public static function logAction(
        int $adminId,
        string $actionType,
        ?string $targetType = null,
        ?int $targetId = null,
        ?array $details = null,
        ?string $notes = null
    ) {
        return self::create([
            'admin_id' => $adminId,
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'details' => $details,
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
