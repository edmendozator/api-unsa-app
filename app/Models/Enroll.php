<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Awobaz\Compoships\Compoships;


class Enroll extends Model
{
    use HasFactory, Compoships;

    protected $connection = "conexion_siac";
    protected $table = 'acpma24';

    public function subject()
    {
        return $this->belongsTo(Subject::class, ['nues', 'casi'], ['nues', 'casi']);
    }

    public function subject_schedules()
    {
        return $this->hasMany(SubjectSchedule::class, ['codi_depe', 'codi_asig'], ['nues', 'casi']);
    }
}
