<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'street_address',
        'barangay',
        'city',
        'province',
        'region',
        'postal_code',
        'country',
        'full_address',
        'cause_preferences',
        'pref_email',
        'pref_sms',
        'pref_updates',
        'pref_urgent',
        'pref_reports',
    ];

    protected $casts = [
        'cause_preferences' => 'array',
        'date_of_birth' => 'date',
        'pref_email' => 'boolean',
        'pref_sms' => 'boolean',
        'pref_updates' => 'boolean',
        'pref_urgent' => 'boolean',
        'pref_reports' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
