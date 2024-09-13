<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $connection = "conexion_siac";
    protected $table = 'acdiden';
    protected $primaryKey = 'cui';
    public $incrementing = false;
    protected $keyType = 'string';

    public function student_programs()
    {
        return $this->hasMany(StudentProgram::class, 'cui', 'cui');
    }
}
