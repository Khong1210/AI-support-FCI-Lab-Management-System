<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    protected $fillable = [
        'lab_name',
        'status',
        'capacity',
    ];

    public function equipments()
    {
        return $this->hasMany(Equipment::class, 'lab_id');
    }

    public function softwares()
    {
        return $this->hasMany(Software::class, 'lab_id');
    }
}
