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
            AND a.parc_id=b.parc_id AND b.parc_anoh=? AND (b.parc_cicl=? OR parc_tipo='A')
            AND a.parc_casi=c.casi AND b.parc_nues=c.nues AND a.parc_cui=? AND b.parc_nues=?
            AND a.parc_id=d.parc_id and a.parc_casi=d.parc_casi AND a.parc_grup=d.parc_grup ORDER BY a.parc_casi,a.parc_id", 
            [$this->periodo->anho, $this->periodo->ciclo, $cui, $nues]);
            
        return $notas_parciales;
    }

    public function asignaturas_aprobadas(Request $request)
    {      
        $cui = $request->cui;
        $nues = $request->nues;
        $espe = $request->espe;
       
        $asignaturas_aprobadas = DB::connection('conexion_siac')->select("SELECT a.casi as codigo, b.nasi as asignatura, a.nota, a.core as estado, b.cred as creditos, CONCAT(a.anoh, '-', a.cicl) as periodo
                            FROM acdl" . $nues . "=a, actasig=b, SIAC_NOTA_APRO=c 
                            WHERE a.casi=b.casi AND b.nues=? AND b.nues=c.codi_depe AND a.anoh=c.nota_anoh 
                            AND a.cicl=c.nota_cicl AND a.cui=? AND (FIND_IN_SET(a.core,'A') OR (a.nota>=c.nota_apro AND FIND_IN_SET(a.core,'J,S,C,V'))) 
                            order by substring(a.casi, 4, 2), a.casi", [$nues, $cui]);
            
        return $asignaturas_aprobadas;
    }

    public function asignaturas_desaprobadas(Request $request)
    {      
        $cui = $request->cui;
        $nues = $request->nues;
	$espe = $request->espe;
	$tabla = 'acdl' . $nues;
       
        $asignaturas_desaprobadas = DB::connection('conexion_siac')->select("SELECT a.casi as codigo, b.nasi as asignatura, a.nota, a.core as estado, b.cred as creditos, CONCAT(a.anoh, '-', a.cicl) as periodo, a.anoh, a.cicl FROM acdl" . $nues . "=a,actasig=b,SIAC_NOTA_APRO=c 
                                        WHERE a.casi=b.casi AND b.nues=? AND b.nues=c.codi_depe AND a.anoh=c.nota_anoh 
                                        AND a.cicl=c.nota_cicl AND a.cui=? AND (FIND_IN_SET(a.core,'D,N,R') OR (a.nota<c.nota_apro AND FIND_IN_SET(a.core,'J,S,C,V'))) 
                                        AND b.vige<>'N' order by substring(a.casi, 4, 2), a.casi", 
					[$nues, $cui]);

	$reprobados = array();

	foreach ($asignaturas_desaprobadas as $asignatura_desaprobada) {
	    $casi = $asignatura_desaprobada->codigo;
	    $anio= $asignatura_desaprobada->anoh;
	    $cicl = $asignatura_desaprobada->cicl;
           
	    if (!$this->equivalencia($nues, $casi, $tabla, $cui, $anio, $cicl)) {
		$reprobados[] = $asignatura_desaprobada;
	    }
	}		
            
        return $reprobados;
    }

    private function equivalencia($nues, $casi, $tabla, $cui, $anio, $cicl) 
    {
	$periodo = $anio . $cicl;    
	$planes = DB::connection('conexion_siac')->select("select cplan from actplan where nues=? order by cplan desc", [$nues]);
       
	foreach ($planes as $plan) {
	    $cplan = substr($plan->cplan, 2, 2);
            
	    $equivalencias = DB::connection('conexion_siac')
		    ->select("select varios,casi2 from actequi2000 where nues=? AND casi1=? AND casi2 like ?", [$nues, $casi, $cplan . '%']);

	    $x=0;   
	    $y=0;

	    foreach ($equivalencias as $equivalencia) {
		$x++;    
		$casi2 = $equivalencia->casi2; 
	        $varios = $equivalencia->varios;	
		$condicion = DB::connection('conexion_siac')->select("select nota from {$tabla} where cui=? AND casi=?", [$cui, $casi2]);
		
		if (!empty($condicion)) {
   		    $nota = $condicion[0]->nota;
    		   
		    //si esta aprobado
                    if($nota>10) {
                      if($varios == 'F')
                         return true;    //OK!!!
                      if($varios == 'T')
                         $y++;
                    }
                    //si esta desaprobado
                    else {
                      if($varios == 'T')
                        return false;   //ERROR!!!
                    }
		}               	
	    } 

	     if($y > 0) {
                 if($x == $y) {
		    return true;    //OK!!!
                 }
                 if($nues == '440' AND $casi == '9703121' AND $y==2) //Caso especial de Ing. Civil desde el 2020-II
                 {
                    return true;
                 }
                 if($nues == '406' AND $casi == '0404124' AND $y==2) //Caso especial de Medicina (Farmacologia, tiene 3 equivalnetes con el 2017,  uno en F y 2 en T) desde el 2021-III
                 {
                    return true;
                 }
                 if($nues == '406' AND $casi == '0402111' AND $y==2) //Caso especial de Medicina (Histoembriologia, tiene 3 equivalnetes con el 2017,  uno en F y 2 en T) desde el 2022-II
                 {
                    return true;
                 }
              }	    
	}

	return false;
    }
}
