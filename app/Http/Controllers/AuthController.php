<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google_Client;
use App\Models\StudentEmail;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Models\Program;
use App\Models\StudentProgram;
use App\Models\Admin;

use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['auth', 'auth_admin', 'auth_demo']]);
    }

    public function auth(Request $request)
    {
        $this->validateAuth($request);

        $token = $request->input('token');

        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($token);

        if ($payload) {
            $email = $payload['email'];
            $name = $payload['name'];
            $admin = Admin::where('email', $email)->first();
            
            if (!$admin) {
                $alumno_unsa = StudentEmail::where('mail', current(explode('@', $email)))->first();
                $cui = $alumno_unsa->cui;
                $isAdmin = false;
            } else {
                $alumno_unsa = null;
                $cui = null;
                $isAdmin = true;
            }	    

            if ($alumno_unsa || $isAdmin) {
                $program = array();
                $hasManyPrograms;
                $student_programs = StudentProgram::join('actescu', 'acdidal.nues', '=', 'actescu.nues')
                    ->where('actescu.nive', 'Z') //solo pregrado
		    ->where('acdidal.cui', $cui)
		    ->whereIn('cond', ['E', 'A', 'N', 'I', 'S'])
                    ->select('acdidal.nues', 'acdidal.espe', DB::raw("SUBSTRING(acdidal.cod0, 3, 2) AS anio_ingreso"))
                    ->get();

                if (count($student_programs) == 1) { // si tiene un solo programa pregrado
                    $hasManyPrograms = false;

                    $nues = $student_programs[0]->nues;
                    $espe = $student_programs[0]->espe;
                    $nuesmen = $nues . $espe;
                    $anio_ingreso = $student_programs[0]->anio_ingreso;
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

                    $program['nues'] = $nues;
                    $program['espe'] = $espe;
                    $program['name'] = $nombre;
                    
                } else {
                    $hasManyPrograms = true;
                }

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'email_verified_at' => now(),
                    ]
                );

                if (!$hasManyPrograms) { // si solo tiene un programa pregrado
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Acceso autorizado',
                        'isAdmin' => $isAdmin,			
                        'access_token' => JWTAuth::fromUser($user),
                        'cui' => $cui,
                        'hasManyPrograms' => $hasManyPrograms,
                        'program' => $program
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Acceso autorizado',
			            'isAdmin' => $isAdmin,			
                        'access_token' => JWTAuth::fromUser($user),
                        'cui' => $cui,
                        'hasManyPrograms' => $hasManyPrograms
                    ], 200);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Acceso no autorizado, no es alumno pregrado'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Token id inválido'
            ], 401);
        }
    }


    public function auth_admin(Request $request)
    {  	 
        $this->validateAuthAdmin($request);

        $token = $request->input('token');
        $cui = $request->input('cui');

        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($token);

        if ($payload) {
            $email = $payload['email'];
            $name = $payload['name'];
	    
            $alumno_unsa = StudentEmail::where('cui', $cui)->first();           	 

            if ($alumno_unsa) {
                $program = array();
                $hasManyPrograms;
                $student_programs = StudentProgram::join('actescu', 'acdidal.nues', '=', 'actescu.nues')
                    ->where('actescu.nive', 'Z') //solo pregrado
                    ->where('acdidal.cui', $cui)
		    ->whereIn('cond', ['E', 'A', 'N', 'I', 'S'])
                    ->select('acdidal.nues', 'acdidal.espe', DB::raw("SUBSTRING(acdidal.cod0, 3, 2) AS anio_ingreso"))
                    ->get();

                if (count($student_programs) == 1) { // si tiene un solo programa pregrado
                    $hasManyPrograms = false;

                    $nues = $student_programs[0]->nues;
                    $espe = $student_programs[0]->espe;
                    $nuesmen = $nues . $espe;
                    $anio_ingreso = $student_programs[0]->anio_ingreso;
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

                    $program['nues'] = $nues;
                    $program['espe'] = $espe;
                    $program['name'] = $nombre;
                    
                } else {
                    $hasManyPrograms = true;
                }

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'email_verified_at' => now(),
                    ]
                );

                if (!$hasManyPrograms) { // si solo tiene un programa pregrado
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Acceso autorizado',
                        'access_token' => JWTAuth::fromUser($user),
                        'cui' => $cui,
                        'hasManyPrograms' => $hasManyPrograms,
                        'program' => $program
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Acceso autorizado',
			            'access_token' => JWTAuth::fromUser($user),
                        'cui' => $cui,
                        'hasManyPrograms' => $hasManyPrograms
                    ], 200);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Acceso no autorizado, no es alumno pregrado'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Token id inválido'
            ], 401);
        }
    }

    public function auth_demo(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $this->validateAuthDemo($request);

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Acceso no autorizado'], 401);
        }

        $user = Auth::user();
        
        $isAdmin = false;          
        $cui = '20244322';            

        $program = array();
        $hasManyPrograms;
        $student_programs = StudentProgram::join('actescu', 'acdidal.nues', '=', 'actescu.nues')
            ->where('actescu.nive', 'Z') //solo pregrado
            ->where('acdidal.cui', $cui)
            ->whereIn('cond', ['E', 'A', 'N', 'I', 'S'])
            ->select('acdidal.nues', 'acdidal.espe', DB::raw("SUBSTRING(acdidal.cod0, 3, 2) AS anio_ingreso"))
            ->get();

        if (count($student_programs) == 1) { // si tiene un solo programa pregrado
            $hasManyPrograms = false;

            $nues = $student_programs[0]->nues;
            $espe = $student_programs[0]->espe;
            $nuesmen = $nues . $espe;
            $anio_ingreso = $student_programs[0]->anio_ingreso;
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

            $program['nues'] = $nues;
            $program['espe'] = $espe;
            $program['name'] = $nombre;
            
        } else {
            $hasManyPrograms = true;
        }        

        if (!$hasManyPrograms) { // si solo tiene un programa pregrado
            return response()->json([
                'status' => 'success',
                'message' => 'Acceso autorizado',
                'isAdmin' => $isAdmin,			
                'access_token' => JWTAuth::fromUser($user),
                'cui' => $cui,
                'hasManyPrograms' => $hasManyPrograms,
                'program' => $program
            ], 200);
        } else {
            return response()->json([
                'status' => 'success',
                'message' => 'Acceso autorizado',
                'isAdmin' => $isAdmin,			
                'access_token' => JWTAuth::fromUser($user),
                'cui' => $cui,
                'hasManyPrograms' => $hasManyPrograms
            ], 200);
        }       
    }

    public function validateAuth(Request $request)
    {
        return $request->validate([
            'token' => 'required',
        ]);
    }

    public function validateAuthAdmin(Request $request)
    {
        return $request->validate([
            'token' => 'required',
            'cui' => 'required'
        ]);
    }

    public function validateAuthDemo(Request $request)
    {
        return $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
    }
}
