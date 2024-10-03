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
}
