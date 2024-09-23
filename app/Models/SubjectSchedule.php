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

    public function hour()
    {
        return $this->hasOne(Hour::class, ['codi_hora', 'codi_depe', 'anno', 'cicl'], ['codi_hora', 'codi_depe', 'anno', 'cicl']);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'codi_aula', 'codi_aula');
    }
}
