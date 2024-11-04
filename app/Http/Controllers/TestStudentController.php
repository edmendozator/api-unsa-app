<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentTest;

class TestStudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $cui = $request->input('fake_cui');
        try {
            $alumno_test = StudentTest::find(1);
            
            if ($alumno_test) {
                $alumno_test->update(['fake_cui' => $cui]);
                return response()->json(['error' => false, 'message' => 'Alumno test actualizado con éxito']);
            } else {
                return response()->json(['true' => false, 'message' => 'Alumno test no encontrado'], 404);
            }            
            
        } catch (\Exception $e) {
            \Log::error('TestStudent@update, Detalle: "' . $e->getMessage() . '" on file ' . $e->getFile() . ':' . $e->getLine());

            return response()->json([
                'error' => true,
                'message' => 'No se pudo actualizar el alumno test'
            ], 500);
        }        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
