<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharityDocument extends Model
{
    protected $fillable = [
        'charity_id',
        'doc_type',     // registration|tax|bylaws|audit|other
        'file_path',
        'sha256',
        'uploaded_by',
    ];

    // Relationships
    public function charity()
    {
        return $this->belongsTo(Charity::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
