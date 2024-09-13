<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Period;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\EnrollCollection;

class AcademicController extends Controller
{
    public function periodo_vigente()
    {
        $current_period = Period::where('codi_depe', '999')
            ->select(\DB::raw("CONCAT(SUBSTRING(codi_peri, 1, LENGTH(codi_peri) - 1), '-', SUBSTRING(codi_peri, -1)) AS periodo"))
            ->first()->periodo;

        return [
            'periodo' => $current_period
        ];
    }

    public function matricula(Request $request)
    {
        $current_period = Period::where('codi_depe', '999')
            ->select(\DB::raw("CONCAT(SUBSTRING(codi_peri, 1, LENGTH(codi_peri) - 1), '-', SUBSTRING(codi_peri, -1)) AS periodo"))
            ->first()
            ->periodo;
        
        $anio = substr($current_period, 2, 2);
        $cicl = substr($current_period, 5, 1);
        $cui = $request->cui;
        $nues = $request->nues;
        $espe = $request->espe;

        $matriculas = DB::connection('conexion_siac')->select("SELECT a.casi,b.nasi,b.cred,a.grup,a.matr 
            from acpma" . $anio . "=a left join actasig=b on (a.nues=b.nues and a.casi=b.casi) 
            where a.cicl=? and a.nues=? and a.espe=? and a.cui=? order by a.casi", 
            [$cicl, $nues, $espe, $cui]);
            
        return new EnrollCollection($matriculas);
    }
}
