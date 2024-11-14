<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ControllerUser;
use App\Http\Controllers\ControllerGrupo;
use App\Http\Controllers\ControllerNota;
use App\Http\Controllers\ControllerChat;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ControllerNotificacion;

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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


//Rutas de Usuario.

//Esta ruta ejecuta la funcion de crear un usuario.
Route::post('/users', [ControllerUser::class, 'store']);

//Esta ruta ejecuta la funcion de actualizar los datos de un usuario.
Route::middleware(['auth:sanctum'])->put('/users/actualizar', [ControllerUser::class, 'update']);

//Esta ruta ejecuta la funcion de eliminar un usuario.
Route::delete('/users/{id}', [ControllerUser::class, 'destroy']);

//Esta ruta ejecuta la funcion de iniciar sesion.
Route::post('/login', [AuthenticatedSessionController::class, 'login']);

//Esta ruta ejecuta la funcion de cerrar sesion.
Route::middleware(['auth:sanctum'])->post('/logout',
[AuthenticatedSessionController::class, 'logout']);//->name('api.logout');

//Esta ruta ejecuta la funcion de mostrar los datos de un usuario en el perfil.
Route::middleware('auth:sanctum')->get('/users/ver', [ControllerUser::class, 'show']);

//Esta ruta ejecuta la funcion de cambiar la contraseña del usuario.
Route::middleware('auth:sanctum')->post('/users/cambiarcontraseña', [ControllerUser::class, 'cambiarContraseña']);

Route::middleware('auth:sanctum')->post('/users/avatar', [ControllerUser::class, 'updateAvatar']);


//Rutas de Grupo.

//Esta ruta ejecuta la funcion de crear un Grupo.
Route::middleware('auth:sanctum')->post('/grupos', [ControllerGrupo::class, 'store']);

//Esta ruta ejecuta la funcion de actualizar los datos de un Grupo.
Route::put('/grupos/{id}/actualizar', [ControllerGrupo::class, 'update']);

//Esta ruta ejecuta la funcion de eliminar un grupo.
Route::middleware('auth:sanctum')->delete('/grupos/{id}', [ControllerGrupo::class, 'destroy']);

Route::middleware('auth:sanctum')->get('/grupos/ver', [ControllerGrupo::class, 'show']);

Route::middleware('auth:sanctum')->get('/grupos/datos', [ControllerGrupo::class, 'datosUsuario']);

Route::middleware('auth:sanctum')->get('/grupos/{id}/preferencias', [ControllerGrupo::class, 'datosGrupo']);

Route::middleware('auth:sanctum')->get('/grupos/{id}/showmiembrosnotas', [ControllerGrupo::class, 'showmiembrosnotas']);

Route::middleware('auth:sanctum')->get('/grupos/{id}/showmiembros', [ControllerGrupo::class, 'showmiembros']);

Route::middleware('auth:sanctum')->post('/grupos/{id}/logo', [ControllerGrupo::class, 'updateLogo']);

Route::middleware('auth:sanctum')->delete('/grupos/{id}/miembros/{idmiembro}', [ControllerGrupo::class, 'eliminarMiembro']);

Route::middleware('auth:sanctum')->delete('/grupos/{id}/abandonar', [ControllerGrupo::class, 'abandonarGrupo']);

Route::middleware('auth:sanctum')->put('/grupos/{id}/miembros/{nuevoAdminId}/asignarAdminYAbandonar', [ControllerGrupo::class, 'asignarAdminYAbandonar']);

Route::middleware('auth:sanctum')->get('/grupos/{id}/verificaradmin', [ControllerGrupo::class, 'verificarAdmin']);

Route::middleware('auth:sanctum')->get('/grupos/{id}/admin', [ControllerGrupo::class, 'showAdmin']);

Route::middleware('auth:sanctum')->get('/grupos/{id}/cargarAdmin', [ControllerGrupo::class, 'cargarAdmin']);

Route::middleware('auth:sanctum')->put('/grupos/{id}/miembros/{nuevoAdminId}/asignarAdmin', [ControllerGrupo::class, 'asignarAdmin']);

//Rutas de Nota.

//Esta ruta ejecuta la funcion de Crear una Nota.
Route::middleware('auth:sanctum')->post('/notas', [ControllerNota::class, 'store']);

//Esta ruta ejecuta la funcion de eliminar una Nota.
Route::middleware('auth:sanctum')->delete('/notas/{id}', [ControllerNota::class, 'destroy']);

Route::middleware('auth:sanctum')->get('/notas/ver', [ControllerNota::class, 'show']);

Route::middleware('auth:sanctum')->put('/notas/{id}/completar', [ControllerNota::class, 'update']);

Route::middleware('auth:sanctum')->post('/notas/{id}/grupo', [ControllerNota::class, 'notagrupo']);

Route::middleware('auth:sanctum')->get('/notas/vercompletadas', [ControllerNota::class, 'showcompletadas']);

Route::middleware('auth:sanctum')->get('/notas/shownotagrupo/{id}', [ControllerNota::class, 'shownotagrupo']);

Route::middleware('auth:sanctum')->get('/notas/shownotagrupocompletadas/{id}', [ControllerNota::class, 'shownotagrupocompletadas']);

Route::middleware('auth:sanctum')->delete('/notas/{id}/{idgrupo}/eliminarnotagrupo', [ControllerNota::class, 'destroygrupo']);

Route::middleware('auth:sanctum')->put('/notas/{id}/{idgrupo}/completarnotagrupo', [ControllerNota::class, 'updategrupo']);


//Rutas de Chat.

//Esta ruta ejecuta la funcion de crear un Chat.
Route::post('/chats', [ControllerChat::class, 'store']);


//Rutas de Notificaciones.

Route::middleware('auth:sanctum')->post('/notificacion/{id}/enviar', [ControllerNotificacion::class, 'enviarInvitacionGrupo']);

Route::middleware('auth:sanctum')->post('/notificacion/{id}/responder', [ControllerNotificacion::class, 'responderInvitacion']);

Route::middleware('auth:sanctum')->get('/notificacion/ver', [ControllerNotificacion::class, 'show']);
