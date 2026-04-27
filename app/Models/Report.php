<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $primaryKey = 'report_id';

    protected $fillable = [
        'lab_id',  
        'user_id', 
        'issues_type',
        'description',
        'reported_date',
        'status',
    ];
}
