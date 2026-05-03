<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = [
        'lab_id',
        'equipment_name',
        'serial_number',
        'type',
        'purchase_date',
        'status',
    ];

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }
}
