<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $primaryKey = 'booking_id';

    protected $fillable = [
        'user_id', 
        'lab_id',  
        'purpose',
        'date',
        'start_time',
        'end_time',
        'status',
    ];
}
