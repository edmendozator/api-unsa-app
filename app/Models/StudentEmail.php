<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentEmail extends Model
{
    use HasFactory;

    protected $connection = "conexion_siac";
    protected $table = 'actmail';
}
