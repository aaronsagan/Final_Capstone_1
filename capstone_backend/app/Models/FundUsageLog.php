<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundUsageLog extends Model
{
    protected $fillable = [
        'charity_id',
        'campaign_id',
        'amount',
        'category',          // supplies|staffing|transport|operations|other
        'description',
        'spent_at',
        'attachment_path',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'spent_at' => 'datetime',
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
}
