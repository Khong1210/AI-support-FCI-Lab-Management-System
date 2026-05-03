<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'username',
        'password',
        'email',
        'user_role',
    ];

    protected $hidden = [
        'password',
    ];
}
