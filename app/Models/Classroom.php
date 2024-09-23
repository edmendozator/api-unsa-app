<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $connection = "conexion_siac";
    protected $table = 'SIAC_AULA';

    public function subject_schedule()
    {
        return $this->hasMany(SubjectSchedule::class, 'codi_aula', 'codi_aula');
    }
    
}
