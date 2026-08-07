<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    use HasFactory;

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
