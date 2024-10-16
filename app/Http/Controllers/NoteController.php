<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $cui = $request->cui;
        $nues = $request->nues;
        $espe = $request->espe;
        $casi = $request->casi;
        $anio = $request->anio;
        $ciclo = $request->ciclo;

        $notes = Note::where('cui', $cui)->where('nues', $nues)->where('espe', $espe)
            ->where('casi', $casi)->where('anio', $anio)->where('ciclo', $ciclo)
            ->latest()
            ->get();

        return response()->json($notes, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    	$validatedData = $request->validate([
            'cui' => 'required|string|max:8',
            'nues' => 'required|string|max:3',
            'espe' => 'required|string|max:1',
            'casi' => 'required|string|max:7',
            'anio' => 'required|string|max:4',
            'ciclo' => 'required|string|max:1',
            'descripcion' => 'required',
            
        ]);

        $note = Note::create($validatedData);

        return response()->json($note, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Note $note)
    {
        if (!$note) {
            return response()->json(['message' => 'Apunte no encontrado'], 404);
        }

        return response()->json($note, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Note $note)
    {
	   if (!$note) {
            return response()->json(['message' => 'Apunte no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'cui' => 'required|string|max:8',
            'nues' => 'required|string|max:3',
            'espe' => 'required|string|max:1',
            'casi' => 'required|string|max:7',
            'anio' => 'required|string|max:4',
            'ciclo' => 'required|string|max:1',
            'descripcion' => 'required',

    ]);

        $note->update($validatedData);

        return response()->json($note, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Note $note)
    {
        if (!$note) {
            return response()->json(['message' => 'Apunte no encontrado'], 404);
        }

        $note->delete();

        return response()->json(['message' => 'Apunte eliminado con éxito'], 200);
    }
}
