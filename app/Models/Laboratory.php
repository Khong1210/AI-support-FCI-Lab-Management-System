<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    protected $primaryKey = 'lab_id';

    protected $fillable = [
        'equipment_id', 
        'software_id',  
        'status',
        'capacity',
    ];
}
