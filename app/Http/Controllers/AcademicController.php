<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Period;
use App\Models\Enroll;
use App\Models\Subject;
use App\Models\SubjectSchedule;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\EnrollCollection;
use App\Http\Resources\EnrollPaymentResource;

class AcademicController extends Controller
{
    public function periodo_vigente()
    {      
        return [
            'periodo' => $this->periodo->anho . "-" . $this->periodo->ciclo
        ];
    }

    public function matricula(Request $request)
    {        
        $cui = $request->cui;
        $nues = $request->nues;
        $espe = $request->espe;

        $matriculas = DB::connection('conexion_siac')->select("SELECT a.casi, b.nasi, b.cred, a.grup, a.matr 
            from " . $this->matricula_table . "=a left join actasig=b on (a.nues=b.nues and a.casi=b.casi) 
            where a.cicl=? and a.nues=? and a.espe=? and a.cui=? order by a.casi", 
            [$this->periodo->ciclo, $nues, $espe, $cui]);
            
        return new EnrollCollection($matriculas);
    }

    public function pago_matricula(Request $request)
    {       
        $cui = $request->cui;
        $nues = $request->nues;
        $espe = $request->espe;

        $pago_matricula = DB::connection('conexion_siac')->select("SELECT fdig as fecha_pago, digi as cajero, mont+montn+montr as monto_pagado 
            from " . $this->pago_matricula_table . " where cicl=? and nues=? and espe=? and cui=?", 
            [$this->periodo->ciclo, $nues, $espe, $cui]);
            
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
            ->select('casi', 'nues', 'espe', 'cicl', 'grup')
            ->where('cui', $cui)->where('nues', $nues)->where('espe', $espe)
            ->get();

        $horario = array();

        foreach ($matriculas as $idx => $matricula) {
            $horario[$idx]['asignatura'] = $matricula->subject->nasi;

            $horas = SubjectSchedule::with('classroom', 'day', 'hour')
                ->where('codi_depe', $matricula->nues)
                ->where('codi_asig', $matricula->casi)
                ->where('codi_grup', $matricula->grup)
                ->where('anno', $this->periodo->anho)
                ->where('cicl', $matricula->cicl)
		        ->orderBy('fdig_asho', 'asc')
                ->get();

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

	    $horario[$idx]['aula'] = $hora->classroom->nomb_aula;
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
            ->select('casi', 'nues', 'espe', 'cicl', 'grup')
            ->where('cui', $cui)->where('nues', $nues)->where('espe', $espe)
            ->get();        

        $horario = array();
        $contadorHoras = 0;

        foreach ($matriculas as $idx => $matricula) {
            $horas = SubjectSchedule::with('day', 'hour')
                ->where('codi_depe', $matricula->nues)
                ->where('codi_asig', $matricula->casi)
                ->where('codi_grup', $matricula->grup)
                ->where('anno', $this->periodo->anho)
                ->where('cicl', $matricula->cicl)
                ->orderBy('fdig_asho', 'asc')
                ->get();

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

            $horas_distintas = SubjectSchedule::with('classroom')
                ->where('codi_depe', $matricula->nues)
                ->where('codi_asig', $matricula->casi)
                ->where('codi_grup', $matricula->grup)
                ->where('anno', $this->periodo->anho)
                ->where('cicl', $matricula->cicl)
                ->select('codi_asig', 'codi_aula', 'codi_dias')
                ->distinct('codi_dias')
                ->orderBy('fdig_asho', 'asc')
                ->get();

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

    public function plan_estudios(Request $request)
    {
        $nues = $request->nues;
        $espe = $request->espe;
        $planes_estudio = array();
        $planes = Plan::where('nues', $nues)->orderBy('cplan', 'desc')->limit(2)->get();

        foreach ($planes as $key1 => $plan) {
            $plan = $plan->cplan;
            $plan_abrev = substr($plan, 2, 2);
            $planes_estudio[$key1]['plan'] = $plan;

            $asignaturas = Subject::where('nues', $nues)
                                ->where('casi', 'like', $plan_abrev . '%')
                                ->where('vige', 'S')
                                ->where('cond', '')
                                ->whereRaw("SUBSTRING(casi, 3, 1) IN ('0', $espe)")
                                ->select('comp','casi','nasi','depa','depa2','depa3','cred','prq1','prq2','prq3','prq4','prq5','prq6','prq7','prq8','ncre','hteo','hpra','htpr','hsem','hlab')
                                ->get();

            $cursos = array();
            
            foreach ($asignaturas as $key2 => $asignatura) {
                $anio = substr($asignatura->casi, 3, 1);
                $semestre = substr($asignatura->casi, 4, 1);
                
                switch($anio) {
                    case '1':
                        $cursos[$key2]['anio'] = 'PRIMER AÑO';

                        switch($semestre) {
                            case '1':
                                $cursos[$key2]['semestre'] = 'PRIMER SEMESTRE';        
                                break;
                            case '2':
                                $cursos[$key2]['semestre'] = 'SEGUNDO SEMESTRE';        
                                break;
                        }                       

                        break;

                    case '2':
                        $cursos[$key2]['anio'] = 'SEGUNDO AÑO';

                        switch($semestre) {
                            case '1':
                                $cursos[$key2]['semestre'] = 'TERCER SEMESTRE';
                                break;
                            case '2':
                                $cursos[$key2]['semestre'] = 'CUARTO SEMESTRE';
                                break;
                        }

                        break;

                    case '3':
                        $cursos[$key2]['anio'] = 'TERCER AÑO';

                        switch($semestre) {
                            case '1':
                                $cursos[$key2]['semestre'] = 'QUINTO SEMESTRE';
                                break;
                            case '2':
                                $cursos[$key2]['semestre'] = 'SEXTO SEMESTRE';
                                break;
                        }

                        break;
                    
                    case '4':
                        $cursos[$key2]['anio'] = 'CUARTO AÑO';

                        switch($semestre) {
                            case '1':
                                $cursos[$key2]['semestre'] = 'SEPTIMO SEMESTRE';
                                break;
                            case '2':
                                $cursos[$key2]['semestre'] = 'OCTAVO SEMESTRE';
                                break;
                        }

                        break;

                    case '5':
                        $cursos[$key2]['anio'] = 'QUINTO AÑO';

                        switch($semestre) {
                            case '1':
                                $cursos[$key2]['semestre'] = 'NOVENO SEMESTRE';
                                break;
                            case '2':
                                $cursos[$key2]['semestre'] = 'DECIMO SEMESTRE';
                                break;
                        }

                        break;
                    
                    case '6':
                        $cursos[$key2]['anio'] = 'SEXTO AÑO';

                        switch($semestre) {
                            case '1':
                                $cursos[$key2]['semestre'] = 'DECIMO PRIMERO SEMESTRE';
                                break;
                            case '2':
                                $cursos[$key2]['semestre'] = 'DECIMO SEGUNDO SEMESTRE';
                                break;
                        }

                        break;
                }

                $cursos[$key2]['asignatura'] = $asignatura;
            }

            $planes_estudio[$key1]['asignaturas'] = $cursos;
        }

        return $planes_estudio;
    }
}
