<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Volunteer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'charity_id',
        'campaign_id',
        'name',
        'email',
        'phone',
        'address',
        'role',
        'skills',
        'experience',
        'status',
        'availability',
        'joined_at',
        'left_at',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected $casts = [
        'availability' => 'array',
        'joined_at' => 'date',
        'left_at' => 'date',
    ];

    // Relationships
    public function charity()
    {
        return $this->belongsTo(Charity::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeForCharity($query, $charityId)
    {
        return $query->where('charity_id', $charityId);
    }

    // Accessors
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }
}
