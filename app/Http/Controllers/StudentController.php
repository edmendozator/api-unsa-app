<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Program;
use App\Models\School;
use App\Models\StudentProgram;
use App\Http\Resources\ProfileResource;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProgramCollection;


class StudentController extends Controller
{
    public function perfil($cui)
    {
        $student = Student::with('student_programs')
            ->select('cui', 
                DB::raw('(SUBSTRING(dic, "1", "1")) AS tipo_documento'),
                DB::raw('(SUBSTRING(dic, "2")) AS nro_documento'),
                DB::raw("(SUBSTRING_INDEX(SUBSTRING_INDEX(REPLACE(apn, '/', ' '), ',', 2), ', ', -1)) AS nombres"),
                DB::raw("(SUBSTRING_INDEX(REPLACE(apn, '/', ' '), ',', 1)) AS apellidos")
            )
            ->where('cui', $cui)
            ->first();
        
        $student->programs = $this->get_programs($cui);

        $programs = array();

        foreach ($student->programs as $index => $program) {
            $programs[$index]['nues'] = $program['nues'];
            $programs[$index]['espe'] = $program['espe'];
            $programs[$index]['nombre'] = $program['nombre'];
            $School = School::where('nues', $program['nues'])->first();
            $programs[$index]['facultad'] = $School->faculty->nfac;
        }

        $student->programs = $programs;

        //Creaditos logrados
        //tabla: acdl.nues        

        /* SELECT sum(b.cred) FROM $tabla=a,actasig=b WHERE a.casi=b.casi AND b.nues=$nues AND a.cui=$cui AND b.nues<>462 AND b.nues<>476 AND b.nues<>477  AND a.nota>10

        $creditos_logrados = DB::connection('conexion_siac')->select("SELECT sum(b.cred) 
            FROM $tabla=a,actasig=b WHERE a.casi=b.casi AND b.nues=?
            AND a.cui=? AND b.nues<>462 AND b.nues<>476 AND b.nues<>477  AND a.nota>10", 
            [$cui, $nues]);

        \Log::info($student); */

        return new ProfileResource($student);
    }

    protected function get_programs($cui)
    {
        $programs = array();

        $student_programs = StudentProgram::join('actescu', 'acdidal.nues', '=', 'actescu.nues')
            ->where('actescu.nive', 'Z') //solo pregrado
            ->where('acdidal.cui', $cui)
            ->select('acdidal.nues', 'acdidal.espe', DB::raw("SUBSTRING(acdidal.cod0, 3, 2) AS anio_ingreso"))
            ->get();

        foreach ($student_programs as $key => $student_program) {
            $nues = $student_program->nues;
            $espe = $student_program->espe;
            $nuesmen = $nues . $espe;
            $anio_ingreso = $student_program->anio_ingreso;
            $nombre = '';

            if ($anio_ingreso >= '00' and $anio_ingreso <= '50') {
                $anio_ingreso = '20' . $anio_ingreso;
            } else {
                $anio_ingreso = '19' . $anio_ingreso;
            }

            $programa = Program::where('plan', '=', function ($query) use ($anio_ingreso,  $nuesmen) {
                $query->select(DB::raw('MAX(plan)'))
                    ->from('actescu_modi')
                    ->where('plan', '<=', $anio_ingreso)
                    ->where('nuesmen', $nuesmen);
            })
                ->where('nuesmen', $nuesmen)
                ->select('actescu_modi.nombre')
                ->first();

            if (!$programa) {
                $programa = Program::where('plan', '=', function ($query) use ($nuesmen) {
                    $query->select(DB::raw('MIN(plan)'))
                        ->from('actescu_modi')
                        ->where('nuesmen', $nuesmen);
                })
                    ->where('nuesmen', $nuesmen)
                    ->select('actescu_modi.nombre')
                    ->first();
            }

            $nombre = $programa->nombre;

            $programs[$key]['nues'] = $nues;
            $programs[$key]['espe'] = $espe;
            $programs[$key]['nombre'] = $nombre;
        }

        return $programs;        
    }

    public function programs($cui)
    {
        $programs = $this->get_programs($cui);

        return new ProgramCollection($programs);
    }    
}
