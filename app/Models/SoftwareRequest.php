<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoftwareRequest extends Model
{
    protected $primaryKey = 'software_request_id';

    protected $fillable = [
        'user_id',     
        'software_id', 
        'version',
        'status',
    ];
}
