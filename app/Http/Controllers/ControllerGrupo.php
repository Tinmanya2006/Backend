<?php

namespace App\Http\Controllers;

Use App\Models\Grupo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ControllerGrupo extends Controller
{
    //Esta funcion sirve para crear un grupo, hay que probarla.
    public function store(Request $request)
    {
        //Esto valida los datos que llegan del Frontend.
        $datosValidados = $request->validate([
            'nombre' => 'required|max:45',
            'admin' => 'max:20|in:usuario,admin',
            'nickname' => 'unique:grupos|max:20',
            'descripcion' => 'max:200',
        ]);

        //Esto crea un grupo si los datos se validaron correctamente.
        $grupo = Grupo::create($datosValidados);
    }

     //Esta funcion actualiza los datos del grupo, hay que probarla.
    public function update(Request $request, $id){

        //Esto busca al grupo por su id para poder actualizar sus datos.
        $grupo = Grupo::find($id);

        //Esto valida los datos que llegan del Frontend.
        $datosValidados = $request->validate([
            'nombre' => 'required|max:45',
            'admin' => 'max:20|in:usuario,admin',
            'nickname' => 'required|unique:grupos|max:20',
            'descripcion' => 'max:200',
        ]);

        //Esto actualiza los datos del grupo, si los datos se validaron correctamente.
        if ($grupo) {

            $grupo->update([
                'nombre' => $datosValidados['nombre'],
                'admin' => $datosValidados['admin'],
                'nickname' => $datosValidados['nickname'],
                'descripcion' => $datosValidados['descripcion'],
            ]);

            //Esto muestra un mensaje si el grupo no se encontró.
        } else {
            return response()->json(['messaje' => 'Grupo no encontrado'], 404);
        }

    }
    //Esta funcion elimina el grupo, hay que probarla.
    public function destroy(Request $request, $id){

        //Esto busca al grupo por su id para poder eliminarlo.
        $grupo = Grupo::find($id);

        //Esto elimina el grupo, si se encuentra su id.
        if ($id) {
            $grupo->delete();

            //Esto muestra dos mensajes, si se ha eliminado el grupo y si no se elimino.
            return response()->json('Grupo eliminado correctamente', 204);
        }   else {
            return response()->json('No se eliminó el Grupo', 406);
         }
    }
}
