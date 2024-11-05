<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Period;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    protected $periodo;
    protected $matricula_table;
    protected $pago_matricula_table;

    public function __construct()
    {
        $this->periodo = $this->getPeriodoActivo();
        $this->matricula_table = $this->getMatriculasTableName($this->periodo->anho);
        $this->pago_matricula_table = $this->getPagoMatriculaTableName($this->periodo->anho);
    }

    protected function getPeriodoActivo()
    {
        return Period::where('codi_depe', '999')->firstOrFail();
    }

    protected function getMatriculasTableName($year)
    {
        return 'acpma' . substr($year, '2');
    }

    protected function getPagoMatriculaTableName($year)
    {
        return 'acprm' . substr($year, '2');
    }
}
