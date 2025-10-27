<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reporter_id',
        'reporter_role',
        'reported_entity_type',
        'reported_entity_id',
        'reason',
        'description',
        'evidence_path',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'action_taken',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Polymorphic relationship to get the reported entity
    public function reportedEntity()
    {
        return $this->morphTo('reported_entity', 'reported_entity_type', 'reported_entity_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}
