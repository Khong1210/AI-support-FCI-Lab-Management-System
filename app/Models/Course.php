<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'user_id', // FK for the lecturer in charge
        'course_name',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
