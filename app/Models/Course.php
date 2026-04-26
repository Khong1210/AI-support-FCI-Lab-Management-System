<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $primaryKey = 'course_id';

    protected $fillable = [
        'user_id', // FK for the lecturer in charge
        'course_name',
        'description',
    ];
}
