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

        $matriculas = Enroll::with('subject:nasi,casi,nues,espe')            
            ->select('casi', 'nues', 'espe')
            ->where('cui', $cui)->where('nues', $nues)->where('espe', $espe)
            ->get();

        $horario = array();

        foreach ($matriculas as $idx => $matricula) {
            $horario[$idx]['asignatura'] = $matricula->subject->nasi;

            $horas = SubjectSchedule::with('day', 'hour')->where('codi_depe', $matricula->nues)
                ->where('codi_asig', $matricula->casi)->where('anno', '2024')->where('cicl', 'A')
		        ->orderBy('fdig_asho', 'asc')->get();

            $bloque_lunes = '';
            $bloque_martes = '';
            $bloque_miercoles = '';
            $bloque_jueves = '';
            $bloque_viernes = '';
                
            foreach ($horas as $key => $hora) {
                switch($hora->day->codi_dias) {
                    case 1:
                        $bloque_lunes .= $hora->hour->desd_hora . " - " . $hora->hour->hast_hora . ", ";
                        break;
                    case 2:
                        $bloque_martes .= $hora->hour->desd_hora . " - " . $hora->hour->hast_hora . ", ";
                        break;
                    case 3:
                        $bloque_miercoles .= $hora->hour->desd_hora . " - " . $hora->hour->hast_hora . ", ";
                        break;
                    case 4:
                        $bloque_jueves .= $hora->hour->desd_hora . " - " . $hora->hour->hast_hora . ", ";
                        break;
                    case 5:
                        $bloque_viernes .= $hora->hour->desd_hora . " - " . $hora->hour->hast_hora . ", ";
                        break;
                }		       
            }

            if ($bloque_lunes != '') {
                $horario[$idx]['lunes'] = $bloque_lunes;
            }

            if ($bloque_martes != '') {
                $horario[$idx]['martes'] = $bloque_martes;
            }

            if ($bloque_miercoles != '') {
                $horario[$idx]['miercoles'] = $bloque_miercoles;
            }

            if ($bloque_jueves != '') {
                $horario[$idx]['jueves'] = $bloque_jueves;
            }

            if ($bloque_viernes != '') {
                $horario[$idx]['viernes'] = $bloque_viernes;
            }
        }
              
        return $horario;
    }

    public function horario_alumno(Request $request)
    {
        $cui = $request->cui;
        $nues = $request->nues;
        $espe = $request->espe;

        $matriculas = Enroll::with('subject:nasi,casi,nues,espe')
            /* ->with(['subject_schedules' => function ($query) {
                $query->where('anno', '2024')->where('cicl', 'A');
            }]) */
            ->select('casi', 'nues', 'espe')
            ->where('cui', $cui)->where('nues', $nues)->where('espe', $espe)
            ->get();        

        $horario = array();
        $contadorHoras = 0;

        foreach ($matriculas as $idx => $matricula) {
            $horas = SubjectSchedule::with('day', 'hour')->where('codi_depe', $matricula->nues)
                ->where('codi_asig', $matricula->casi)->where('anno', '2024')->where('cicl', 'A')
                ->orderBy('fdig_asho', 'asc')->get();

            $bloque_lunes = '';
            $bloque_martes = '';
            $bloque_miercoles = '';
            $bloque_jueves = '';
            $bloque_viernes = '';
                
            foreach ($horas as $key => $hora) {
                switch($hora->day->codi_dias) {
                    case 1:
                        $bloque_lunes .= $hora->hour->desd_hora . " - " . $hora->hour->hast_hora . ", ";
                        break;
                    case 2:
                        $bloque_martes .= $hora->hour->desd_hora . " - " . $hora->hour->hast_hora . ", ";
                        break;
                    case 3:
                        $bloque_miercoles .= $hora->hour->desd_hora . " - " . $hora->hour->hast_hora . ", ";
                        break;
                    case 4:
                        $bloque_jueves .= $hora->hour->desd_hora . " - " . $hora->hour->hast_hora . ", ";
                        break;
                    case 5:
                        $bloque_viernes .= $hora->hour->desd_hora . " - " . $hora->hour->hast_hora . ", ";
                        break;
                }		       
            }

            $horas_distintas = SubjectSchedule::with('classroom')->where('codi_depe', $matricula->nues)
                ->where('codi_asig', $matricula->casi)->where('anno', '2024')->where('cicl', 'A')
                ->select('codi_asig', 'codi_aula', 'codi_dias')->distinct('codi_dias')->orderBy('fdig_asho', 'asc')->get();

            foreach ($horas_distintas as $index => $hora_distinta) {
                switch ($hora_distinta->codi_dias) {
                    case 1:
                        if ($bloque_lunes != '') {
                            $horario[$contadorHoras]['dia'] = 'lunes';
                            $horario[$contadorHoras]['asignatura'] = $matricula->subject->nasi;
                            $horario[$contadorHoras]['hora'] = $bloque_lunes;
                        }
                        break;
                    case 2:
                        if ($bloque_martes != '') {
                            $horario[$contadorHoras]['dia'] = 'martes';
                            $horario[$contadorHoras]['asignatura'] = $matricula->subject->nasi;
                            $horario[$contadorHoras]['hora'] = $bloque_martes;
                        }
                        break;
                    case 3:
                        if ($bloque_miercoles != '') {
                            $horario[$contadorHoras]['dia'] = 'miercoles';
                            $horario[$contadorHoras]['asignatura'] = $matricula->subject->nasi;
                            $horario[$contadorHoras]['hora'] = $bloque_miercoles;
                        }
                        break;
                    case 4:
                        if ($bloque_jueves != '') {
                            $horario[$contadorHoras]['dia'] = 'jueves';
                            $horario[$contadorHoras]['asignatura'] = $matricula->subject->nasi;
                            $horario[$contadorHoras]['hora'] = $bloque_jueves;
                        }
                        break;
                    case 5:
                        if ($bloque_viernes != '') {
                            $horario[$contadorHoras]['dia'] = 'viernes';
                            $horario[$contadorHoras]['asignatura'] = $matricula->subject->nasi;
                            $horario[$contadorHoras]['hora'] = $bloque_viernes;
                        } 
                        break;                    
                }      

                $horario[$contadorHoras]['aula'] = $hora_distinta->classroom->nomb_aula;                            

                $contadorHoras++;          
            }           
        }       

        return $horario;
    }
}
