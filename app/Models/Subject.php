<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Awobaz\Compoships\Compoships;


class Subject extends Model
{
    use HasFactory, Compoships;

    protected $connection = "conexion_siac";
    protected $table = 'actasig';

    public function enrolls()
    {
        return $this->hasMany(Enroll::class, ['nues', 'casi'], ['nues', 'casi']);
    }
}
