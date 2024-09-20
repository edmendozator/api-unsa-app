<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Awobaz\Compoships\Compoships;


class SubjectSchedule extends Model
{
    use HasFactory, Compoships;

    protected $connection = "conexion_siac";
    protected $table = 'SIAC_ASIG_HORA';  
    
    public function day()
    {
        return $this->hasOne(Day::class, 'codi_dias', 'codi_dias');
    }
}
