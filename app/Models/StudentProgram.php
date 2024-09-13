<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProgram extends Model
{
    use HasFactory;

    protected $connection = "conexion_siac";
    protected $table = 'acdidal';
    public $incrementing = false;

    public function program()
    {
        return $this->belongsTo(Program::class, 'nues', 'nues');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'cui', 'cui');
    }
}
