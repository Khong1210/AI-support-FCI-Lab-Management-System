<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'lab_id',    
        'course_id', 
        'day_of_week',
        'date',
        'start_time',
        'end_time',
    ];
}
