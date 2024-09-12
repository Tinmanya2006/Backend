<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;


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

    //Esta funcion elimina la nota, hay que probarla.
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        //Esto elimina la nota.
        $notas = DB::table('notas')
                ->where('id', $id)
                ->where('idusuario', $user->id)
                ->delete();


        if ($nota) {
            return response()->json(['message' => 'Nota eliminada correctamente'], 200);
        } else {
        return response()->json(['message' => 'Nota no encontrada o no perteneciente al usuario'], 404);
        }

    }

    public function show(Request $request)
    {
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }
    $notas = DB::table('notas')
                ->select('descripcion', 'categoria', 'prioridad', 'asignacion')
                ->where('idusuario', $user->id)
                ->get();

    return response()->json($notas);
    }

    public function showgrupo(Request $request)
    {
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }
    $notas = DB::table('notas')
                ->select('descripcion', 'categoria', 'prioridad', 'asignacion')
                ->where('idgrupo', $grupo->id)
                ->get();

    return response()->json($notas);
    }
}
