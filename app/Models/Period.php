<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use HasFactory;

    protected $connection = "conexion_siac";
    protected $table = 'SIAC_PERI';   

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'anho',
        'ciclo'
    ];

    /**
     * Get the period year
     *
     * @return string
     */
    public function getAnhoAttribute()
    {
        return substr($this->codi_peri, 0, -1);
    }

    /**
     * Get the ciclo
     *
     * @return string
     */
    public function getCicloAttribute()
    {
        return substr($this->codi_peri, -1);
    }
}
