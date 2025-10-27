<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonorVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'id_type',
        'id_number',
        'id_document_path',
        'selfie_path',
        'status',
        'review_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
