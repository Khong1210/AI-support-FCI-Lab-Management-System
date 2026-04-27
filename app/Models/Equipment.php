<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $primaryKey = 'equipment_id';

    protected $fillable = [
        'lab_id', 
        'equipment_name',
        'serial_number',
        'type',
        'purchase_date',
    ];
}
