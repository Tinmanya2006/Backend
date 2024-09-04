<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        //Esto crea una nota si los datos se validaron correctamente.
        $nota = Nota::create($datosvalidados);

        return response()->json([
            'messaje' => 'La nota ha sido creada correctamente'
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
}
