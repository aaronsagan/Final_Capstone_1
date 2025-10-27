<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = [
        'donor_id','donor_name','donor_email','charity_id','campaign_id','amount','purpose','is_anonymous',
        'status','proof_path','proof_type','channel_used','reference_number','message','external_ref','receipt_no','donated_at',
        'is_recurring','recurring_type','recurring_end_date','next_donation_date','parent_donation_id'
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_recurring' => 'boolean',
        'donated_at'   => 'datetime',
        'next_donation_date' => 'datetime',
        'recurring_end_date' => 'datetime',
        'amount'       => 'decimal:2',
    ];

    public function donor(){ return $this->belongsTo(User::class,'donor_id'); }
    public function charity(){ return $this->belongsTo(Charity::class); }
    public function campaign(){ return $this->belongsTo(Campaign::class); }
    public function parentDonation(){ return $this->belongsTo(Donation::class, 'parent_donation_id'); }
    public function recurringDonations(){ return $this->hasMany(Donation::class, 'parent_donation_id'); }
}
