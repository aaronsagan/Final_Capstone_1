<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonationChannel extends Model
{
    protected $fillable = [
        'charity_id',
        'type',      // gcash|maya|paymaya|paypal|bank|bank_transfer|ewallet|other
        'label',
        'account_name',
        'account_number',
        'details',   // json for additional data
        'qr_code_path',
        'is_active',
    ];

    protected $casts = [
        'details' => 'array',         // important: stored as JSON in DB
        'is_active' => 'boolean',
    ];

    protected $appends = ['qr_code_url'];

    // Relationships
    public function charity()
    {
        return $this->belongsTo(Charity::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_donation_channel')
            ->withTimestamps();
    }

    // Accessor for QR code URL
    public function getQrCodeUrlAttribute()
    {
        if ($this->qr_code_path) {
            return url('storage/' . $this->qr_code_path);
        }
        return null;
    }
}
