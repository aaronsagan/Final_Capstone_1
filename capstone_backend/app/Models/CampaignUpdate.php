<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignUpdate extends Model
{
    protected $fillable = [
        'campaign_id',
        'title',
        'content',
        'is_milestone',
        'image_path',
    ];

    protected $casts = [
        'is_milestone' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the campaign that owns the update
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
