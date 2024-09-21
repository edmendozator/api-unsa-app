<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Awobaz\Compoships\Compoships;

class Hour extends Model
{
    use HasFactory, Compoships;

    protected $connection = "conexion_siac";
    protected $table = 'SIAC_HORA';

    //public function hour()
    //{
       // return $this->hasOne(l::class, ['codi_hora, 'casi'], ['nues', 'casi']);
   // }
}
