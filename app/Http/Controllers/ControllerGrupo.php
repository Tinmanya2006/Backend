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
    public function store(Request $request) {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Validar los datos
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:45',
            'descripcion' => 'required|string|max:200',
            'logo' => 'nullable|image|max:2048', // Validación para el archivo de imagen
        ]);

        // Crear el grupo
        $grupo = new Grupo();
        $grupo->nombre = $validatedData['nombre'];
        $grupo->descripcion = $validatedData['descripcion'];
        $grupo->idusuario = $user->id; // Asignar el ID del usuario autenticado

        // Verificar si se subió un logo y guardarlo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->store('images', 'public'); // Guardar en 'storage/app/public/images'
            $grupo->logo = $path;
        }

        // Guardar el grupo en la base de datos
        $grupo->save();

        return response()->json(['message' => 'Grupo creado exitosamente', 'id' => $grupo->id]);

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
            ->select('nombre', 'id', 'logo')
            ->where('idusuario', $user->id)
            ->get()
            ->map(function ($grupo) {
                // Generar la URL completa para el logo
                $grupo->logo = $grupo->logo
                ? url('storage/' . $grupo->logo)
                : url('/storage/images/halcyon.png');
                return $grupo;
            });

        return response()->json($grupos);
    }

    public function datosUsuario(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $user = DB::table('users')
                    ->select('name', 'nickname', 'biografia', 'avatar')
                    ->where('id', $user->id)
                    ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Construye la URL del avatar solo si existe
        $user->avatar = $user->avatar
            ? url('storage/' . $user->avatar)
            : url('/storage/images/user.png');

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



    public function showmiembros(Request $request, $id) {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

    if (!$id) {
        return response()->json(['message' => 'La id del grupo es requerida'], 400);
    }
        $grupos = DB::table('grupos')
                    ->join('users', 'grupos.idusuario', '=', 'users.id')
                    ->where('grupos.id', $id)
                    ->select('users.id as idusuario', 'users.nickname', 'users.avatar')
                    ->get();

                    $user->avatar = $user->avatar
            ? url('storage/' . $user->avatar)
            : url('/storage/images/user.png');

        return response()->json($user);

        return response()->json($grupos);
    }

    public function updateLogo(Request $request) {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Verifica que se haya proporcionado el ID del grupo
        $grupoId = $request->input('grupo');
        if (!$grupoId) {
            return response()->json(['message' => 'Group ID is required'], 400);
        }

        // Encuentra el grupo al que pertenece el usuario
        $grupo = Grupo::find($grupoId);
        if (!$grupo) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        // Verifica si el archivo 'logo' se ha subido
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->store('images', 'public'); // Guarda en 'storage/app/public/images'

            // Actualiza la ruta del logo en la base de datos
            $grupo->logo = $path;
            $grupo->save();

            return response()->json(['message' => 'Logo updated successfully']);
        }

        return response()->json(['message' => 'No logo uploaded'], 400);
    }
}


