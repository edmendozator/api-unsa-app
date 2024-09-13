<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $connection = "conexion_siac";
    protected $table = 'actescu_modi';
    //protected $table = 'actescu';    
}
