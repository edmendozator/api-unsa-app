<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    protected $connection = "conexion_siac";
    protected $table = 'actfacu';

    public function schools()
    {
        return $this->hasMany(School::class, 'facu', 'facu');
    }
}
