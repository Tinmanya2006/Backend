<?php

namespace App\Http\Controllers;

Use App\Models\Grupo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        if (!auth()->check()) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $datosValidados['idusuario'] = auth()->id();
        //Esto crea una nota si los datos se validaron correctamente.
        $grupo = Grupo::create($datosValidados);

        return response()->json([
            'messaje' => 'El grupo ha sido creada correctamente',
            'grupo' => $grupo
        ]);
    }

     //Esta funcion actualiza los datos del grupo, hay que probarla.
    public function update(Request $request, $id){

        //Esto busca al grupo por su id para poder actualizar sus datos.
        $grupo = Grupo::find($id);

        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }
        //Esto valida los datos que llegan del Frontend.
        $datosValidados = $request->validate([
            'nombre' => 'required|max:45',
            'admin' => 'max:20|in:usuario,admin',
            'descripcion' => 'max:200',
        ]);


        //Esto actualiza los datos del grupo, si los datos se validaron correctamente.
        if($grupo){
            $grupo->update($datosValidados);
            return response()->json('Datos del grupo actualizado correctamente: ', 204);
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

    public function show(Request $request){
        $user = Auth::user();

        if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
     }
        $grupos = DB::table('grupos')
                ->select('nombre', 'id')
                ->where('idusuario', $user->id)
                ->get();

        return response()->json($grupos);
    }

    public function datosUsuario(Request $request)
    {
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }
    $user = DB::table('users')
                ->select('name', 'nickname')
                ->where('id', $user->id)
                ->get();

    return response()->json($user);
    }

    public function datosGrupo(Request $request, $id){
        $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    if (!$id) {
        return response()->json(['message' => 'La id del grupo es requerida'], 400);
    }

        $grupo = DB::table('grupos')
                    ->select('nombre', 'descripcion', 'created_at')
                    ->where('id', '=', $id)
                    ->get();

        return response()->json($grupo);
    }
}


