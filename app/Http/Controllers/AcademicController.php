<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Period;
use App\Models\Enroll;
use App\Models\SubjectSchedule;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\EnrollCollection;
use App\Http\Resources\EnrollPaymentResource;

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

    public function pago_matricula(Request $request)
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

        $pago_matricula = DB::connection('conexion_siac')->select("SELECT fdig as fecha_pago,digi as cajero, mont+montn+montr as monto_pagado 
            from acprm" . $anio . " where cicl=? and nues=?  and espe=? and cui=?", 
            [$cicl, $nues, $espe, $cui]);
            
            // Get the first result, or null if no results
        $pago_matricula = !empty($pago_matricula) ? (object)$pago_matricula[0] : null;

        if ($pago_matricula) {
            return new EnrollPaymentResource($pago_matricula);
        } else {
            return 'no tiene pago de matricula';
        }      
    }

    public function horario_asignatura(Request $request)
    {
        $cui = $request->cui;
        $nues = $request->nues;
        $espe = $request->espe;

        $matriculas = Enroll::with('subject:nasi,casi,nues,espe', 'subject_schedules')
            ->select('casi', 'nues', 'espe')
            ->where('cui', $cui)->where('nues', $nues)->where('espe', $espe)
            ->get();

        $horario = array();

        foreach ($matriculas as $key => $matricula) {
            $horario[$key]['asignatura'] = $matricula->subject->nasi;

            $horarios = SubjectSchedule::with('day')->where('codi_depe', $matricula->nues)
                ->where('codi_asig', $matricula->casi)->where('anno', '2024')->where('cicl', 'A')
                ->orderBy('fdig_asho', 'asc')->get();

            \Log::info($horarios);

        }
      
        //\Log::info($horario);
        //return $asignaturas;

    }

    public function horario_semestre()
    {

    }
}
