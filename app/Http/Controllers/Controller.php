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
    protected $matriculados_table;

    public function __construct()
    {
        $this->periodo = $this->getPeriodoActivo();
        $this->matriculados_table = $this->getMatriculadosTableName($this->periodo->anho);
    }

    protected function getPeriodoActivo()
    {
        return Period::where('codi_depe', '999')->firstOrFail();
    }

    protected function getMatriculadosTableName($year)
    {
        return 'acpma' . substr($year, '2');
    }
}
