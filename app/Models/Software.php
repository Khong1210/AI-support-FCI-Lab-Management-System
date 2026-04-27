<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    protected $primaryKey = 'software_id';

    protected $fillable = [
        'lab_id', 
        'software_name',
        'version',
        'expiry_date',
        'status',
    ];
}
