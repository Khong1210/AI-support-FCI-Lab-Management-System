<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Booking;

class Schedule extends Model
{
    use HasFactory;


    protected $fillable = [
        'lab_id',
        'course_id',
        'semester_id',
        'booking_id',
        'schedule_type',
        'day_of_week',
        'date',
        'start_time',
        'end_time',
        'is_recurring',
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

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
