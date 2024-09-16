<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;


class ControllerNota extends Controller
{
    //Esta funcion sirve para crear una nota, hay que probarla.
    public function store(Request $request)
    {

        //Esto valida los datos que llegan del Frontend.
        $datosvalidados = $request->validate([
            'descripcion' => 'max:300',
            'categoria' => 'required|in:Trabajo,Estudios,Gimnasio,Dieta,Ocio,Viajes,Otro',
            'prioridad' => 'required|in:Baja,Media,Alta',
            'asignacion' => 'boolean',
        ]);

        if (!auth()->check()) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $datosvalidados['idusuario'] = auth()->id();
        //Esto crea una nota si los datos se validaron correctamente.
        $nota = Nota::create($datosvalidados);

        return response()->json([
            'messaje' => 'La nota ha sido creada correctamente',
            'nota' => $nota
        ]);

    }

    public function update(Request $request, $id)
    {
        //Esto busca al usuario por su id para poder actualizar sus datos.
        $user = $request->user();

        if (!$user) {
            return response()->json(['mensaje' => 'Usuario no autenticado'], 401);
        }

        //Esto valida los datos que llegan del Frontend.
        $datosValidados = $request->validate(
            [
                'estado' => 'in:Pendiente,Completada',
                'finalizacion' => 'nullable|date_format:Y-m-d H:i:s',
            ]
        );

        $nota = $user->notas()->find($id);

        //Esto actualiza los datos del usuario, si los datos se validaron correctamente.
        if ($nota) {
                $estado = $datosValidados['estado'] ?? $nota->estado;
                $finalizacion = $datosValidados['finalizacion'] ?? ($estado === 'Completada' ? now() : $nota->finalizacion);

            $nota->update([
                'estado' => $estado,
                'finalizacion' => $finalizacion,
            ]);
            //Esto muestra dos mensajes si se han actualizado los datos correctamente
            //y a su vez muestra si los mismos no se pudieron actualizar.
            return response()->json(['messaje' => 'Se ha actualizado el estado de la nota']);
        } else {
            return response()->json(['messaje' => 'No se pudieron actualizar los datos del usuario'], 404);
        }
    }

    //Esta funcion elimina la nota, hay que probarla.
    public function destroy(Request $request, $id)
{
    $user = $request->user();//Auth::user();  // Obtenemos el usuario autenticado
    if (!$user) {
        return response()->json(['message' => 'Usuario no autenticado'], 401);
    }

    // Verificamos si el usuario tiene una nota con ese ID
    $nota = $user->notas()->find($id);
    if (!$nota) {
        return response()->json(['message' => 'Nota no encontrada o no perteneciente al usuario'], 404);
    }

    // Eliminamos la nota si existe
    $nota->delete();
    return response()->json(['message' => 'Nota eliminada correctamente'], 200);
}

    public function show(Request $request)
    {
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }
    $notas = DB::table('notas')
                ->select('descripcion', 'categoria', 'prioridad', 'asignacion', 'id')
                ->where('idusuario', $user->id)
                ->get();

    return response()->json($notas);
    }

    public function showgrupo(Request $request)
    {
    $user = Auth::user();

        $grupo = $user->grupos;

    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }
    $notas = DB::table('notas')
                ->select('descripcion', 'categoria', 'prioridad', 'asignacion')
                ->where('idgrupo', $grupo->id)
                ->get();

    return response()->json($notas);
    }

    public function showcompletadas(Request $request)
    {
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }
    $notas = DB::table('notas')
                ->select('descripcion', 'categoria', 'finalizacion')
                ->where('idusuario', $user->id)
                ->where('estado', 'Completada')
                ->orderBy('finalizacion', 'desc')
                ->limit(5)
                ->get();

    return response()->json($notas);
    }
}
