<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharityFollow extends Model
{
    protected $fillable = [
        'donor_id',
        'charity_id',
        'is_following',
        'followed_at'
    ];

    protected $casts = [
        'is_following' => 'boolean',
        'followed_at' => 'datetime',
    ];

    public function donor()
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function charity()
    {
        return $this->belongsTo(Charity::class);
    }

    public function scopeFollowing($query)
    {
        return $query->where('is_following', true);
    }

    public function scopeByDonor($query, $donorId)
    {
        return $query->where('donor_id', $donorId);
    }

    public function scopeByCharity($query, $charityId)
    {
        return $query->where('charity_id', $charityId);
    }
}
