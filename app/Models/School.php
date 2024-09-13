<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;
    protected $connection = "conexion_siac";
    protected $table = 'actescu';

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'facu', 'facu');
    }
}
