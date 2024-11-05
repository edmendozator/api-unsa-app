<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AcademicController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\NoteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */


Route::post('auth', [AuthController::class, 'auth']);
Route::post('auth_admin', [AuthController::class, 'auth_admin']);

//Route::get('programs/{cui}', [ProgramController::class, 'index'])->middleware('auth:sanctum');
Route::get('profile/{cui}', [StudentController::class, 'perfil']);//->middleware('auth:api');
Route::get('programs/{cui}', [StudentController::class, 'programs']);//->middleware('auth:api');
Route::get('current_period', [AcademicController::class, 'periodo_vigente']);//->middleware('auth:api');
Route::get('enroll', [AcademicController::class, 'matricula']);//->middleware('auth:api');
Route::get('enroll_payment', [AcademicController::class, 'pago_matricula']);//->middleware('auth:api');

Route::get('grades/{cui}/{nues}/{espe}', [GradeController::class, 'show']);//->middleware('auth:api');
Route::get('partial_notes/{cui}/{nues}/{espe}', [GradeController::class, 'notas_parciales']);//->middleware('auth:api');
Route::get('approved_courses', [GradeController::class, 'asignaturas_aprobadas']);//->middleware('auth:api');
Route::get('failed_courses', [GradeController::class, 'asignaturas_desaprobadas']);//->middleware('auth:api');

Route::get('student_schedule', [AcademicController::class, 'horario_alumno']);//->middleware('auth:api');
Route::get('schedule_subject', [AcademicController::class, 'horario_asignatura']);//->middleware('auth:api');
Route::get('curriculum', [AcademicController::class, 'plan_estudios']);//->middleware('auth:api');

Route::apiResource('notes', NoteController::class);




