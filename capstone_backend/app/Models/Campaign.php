<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'charity_id',
        'title',
        'description',
        'problem',
        'solution',
        'expected_outcome',
        'beneficiary',
        'beneficiary_category',
        'region',
        'province',
        'city',
        'barangay',
        'target_amount',
        'deadline_at',
        'cover_image_path',
        'status',
        'donation_type',
        'campaign_type',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'target_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'beneficiary_category' => 'array',
    ];

    protected $appends = ['current_amount'];

    // Relationships
    public function charity()
    {
        return $this->belongsTo(Charity::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    // Accessors
    public function getCurrentAmountAttribute()
    {
        return $this->donations()
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function fundUsageLogs()
    {
        return $this->hasMany(FundUsageLog::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(CampaignComment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(CampaignComment::class)->where('status', 'approved');
    }

    public function volunteers()
    {
        return $this->hasMany(Volunteer::class);
    }

    public function donationChannels()
    {
        return $this->belongsToMany(DonationChannel::class, 'campaign_donation_channel')
            ->withTimestamps();
    }

    public function campaignUpdates()
    {
        return $this->hasMany(CampaignUpdate::class);
    }
}
