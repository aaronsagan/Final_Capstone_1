<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonorRegistrationDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
