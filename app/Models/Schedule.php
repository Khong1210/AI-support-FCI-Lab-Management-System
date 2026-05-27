<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{

    protected $fillable = [
        'lab_id',    
        'course_id', 
        'semester_id',
        'day_of_week',
        'date',
        'start_time',
        'end_time',
    ];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
