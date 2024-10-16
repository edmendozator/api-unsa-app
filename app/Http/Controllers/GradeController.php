<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\GradeCollection;

class GradeController extends Controller
{
    public function show($cui, $nues, $espe)
    {
        $table = 'acdl' . $nues;

        $grades = DB::connection('conexion_siac')
            ->table($table)
            ->join('actasig', $table . '.casi', '=', 'actasig.casi')
            ->where('cui', $cui)
            ->where('nues', $nues)
            ->where('actasig.espe', $espe)
            ->select('nasi', 'nota', 'matr', 'anoh', 'cicl', 'fech')
            ->get();

        return new GradeCollection($grades);
    }

    public function notas_parciales($cui, $nues, $espe)
    {
        $notas_parciales = DB::connection('conexion_siac')->select("SELECT a.parc_id,a.parc_casi,a.parc_grup,a.parc_nota,a.parc_core,c.nasi,b.parc_desc,parc_peso,parc_apla FROM
            SIAC_PARC_NOTA=a,SIAC_PARC=b,actasig=c,SIAC_PARC_ASIG=d WHERE a.parc_core in ('A','D','N')
            AND a.parc_id=b.parc_id AND b.parc_anoh=? AND (b.parc_cicl='A' OR parc_tipo='A')
            AND a.parc_casi=c.casi AND b.parc_nues=c.nues AND a.parc_cui=? AND b.parc_nues=?
            AND a.parc_id=d.parc_id and a.parc_casi=d.parc_casi AND a.parc_grup=d.parc_grup ORDER BY a.parc_casi,a.parc_id", 
            ['2022', $cui, $nues]);
            
        return $notas_parciales;
    }

    public function asignaturas_aprobadas(Request $request)
    {      
        $cui = $request->cui;
        $nues = $request->nues;
        $espe = $request->espe;
       
        $asignaturas_aprobadas = DB::connection('conexion_siac')->select("SELECT a.casi as codigo, b.nasi as asignatura, a.nota, a.matr as matricula
                            FROM acdl401=a,actasig=b,SIAC_NOTA_APRO=c 
                            WHERE a.casi=b.casi AND b.nues=? AND b.nues=c.codi_depe AND a.anoh=c.nota_anoh 
                            AND a.cicl=c.nota_cicl AND a.cui=? AND (FIND_IN_SET(a.core,'A') OR (a.nota>=c.nota_apro AND FIND_IN_SET(a.core,'J,S,C,V'))) 
                            order by substring(a.casi,4,2),a.casi", [$nues, $cui]);
            
        return $asignaturas_aprobadas;
    }

    public function asignaturas_desaprobadas(Request $request)
    {      
        $cui = $request->cui;
        $nues = $request->nues;
        $espe = $request->espe;
       
        $asignaturas_desaprobadas = DB::connection('conexion_siac')->select("SELECT a.casi as codigo, b.nasi as asignatura, a.nota, a.matr as matricula 
                                        FROM acdl401=a,actasig=b,SIAC_NOTA_APRO=c 
                                        WHERE a.casi=b.casi AND b.nues=? AND b.nues=c.codi_depe AND a.anoh=c.nota_anoh 
                                        AND a.cicl=c.nota_cicl AND a.cui=? AND (FIND_IN_SET(a.core,'D,N,R') OR (a.nota<c.nota_apro AND FIND_IN_SET(a.core,'J,S,C,V'))) 
                                        AND b.vige<>'N' order by substring(a.casi,4,2),a.casi", [$nues, $cui]);
            
        return $asignaturas_desaprobadas;
    }
}
