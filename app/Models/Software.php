<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    protected $fillable = [
        'lab_id',
        'software_name',
        'version',
        'expiry_date',
        'status',
    ];

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }
}
