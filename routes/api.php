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

//Route::middleware('auth:api')->group(function () {
    Route::get('programs/{cui}', [StudentController::class, 'programs']);
    Route::get('current_period', [AcademicController::class, 'periodo_vigente']);
    Route::get('profile/{cui}', [StudentController::class, 'perfil']);
    Route::get('enroll', [AcademicController::class, 'matricula']);
    Route::get('enroll_payment', [AcademicController::class, 'pago_matricula']);
    Route::get('grades/{cui}/{nues}/{espe}', [GradeController::class, 'show']);
    Route::get('partial_notes/{cui}/{nues}/{espe}', [GradeController::class, 'notas_parciales']);
    Route::get('approved_courses', [GradeController::class, 'asignaturas_aprobadas']);
    Route::get('failed_courses', [GradeController::class, 'asignaturas_desaprobadas']);
    Route::get('student_schedule', [AcademicController::class, 'horario_alumno']);
    Route::get('schedule_subject_all', [AcademicController::class, 'horario_asignatura_todo']);
    Route::get('schedule_subject_by_year', [AcademicController::class, 'horario_asignatura_por_anio']);
    Route::get('curriculum', [AcademicController::class, 'plan_estudios']);
    Route::apiResource('notes', NoteController::class);
//});
