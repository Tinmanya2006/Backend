<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class ControllerNota extends Controller
{
    //Esta funcion sirve para crear una nota, hay que probarla.
    public function store(Request $request)
    {

        //Esto valida los datos que llegan del Frontend.
        $datosvalidados = $request->validate([
            'descripcion' => 'max:300',
            'categoria' => 'required|max:45',
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
        //Esto busca la nota por su id para poder eliminarla.
        $nota = Nota::find($id);

        //Esto elimina la nota.
         if ($nota) {

        $nota->delete();

        //Esto muestra dos mensajes, si se ha eliminado la nota y si no se elimino.
        return response()->json('Nota eliminada correctamente', 204);
     }  else {
        return response()->json('No se eliminó la Nota', 406);
     }
    }

    public function verNotas()
    {
        // Obtén el usuario autenticado
        $user = Auth::user();

        // Obtén las notas del usuario
        $nota = $user->nota;  // Asumiendo que tienes una relación "notas" en tu modelo User

        // Retorna las notas en formato JSON
        return response()->json($nota, 200);
    }

    public function show(Request $request)
    {
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    $nota = $user->nota;

    return response()->json(['messaje' => 'Se han encontrado las notas']);
    {
    return response()->json(['messaje' => 'No se ha podido encontrar las notas'], 404);
    }
}
}
