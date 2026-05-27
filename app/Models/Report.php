<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'lab_id',  
        'user_id', 
        'issues_type',
        'description',
        'reported_date',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }
}
