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
        $datosValidados = $request->validate([
            'nombre' => 'required|string|max:45',
            'descripcion' => 'required|string|max:200',
            'logo' => 'nullable|image|max:2048',
            'miembros' => 'array',
        ]);

        // Crear el grupo
        $grupo = new Grupo();
        $grupo->nombre = $datosValidados['nombre'];
        $grupo->descripcion = $datosValidados   ['descripcion'];
        $grupo->idusuario = $user->id; // Asignar el ID del usuario autenticado

        // Verificar si se subió un logo y guardarlo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->store('images', 'public'); // Guardar en 'storage/app/public/images'
            $grupo->logo = $path;
        }


        // Añadir el creador del grupo a la lista de miembros
        $miembros = $request->input('miembros', []); // Obtener miembros del request
        $miembros[] = $user->id; // Agregar el ID del creador a los miembros

        // Almacenar miembros como JSON
        $grupo->miembros = json_encode(array_unique($miembros)); // Convertir a JSON, asegurando que no haya duplicados


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
            ->where(function($query) use ($user) {
                // Grupos creados por el usuario
                $query->where('idusuario', $user->id)
                      ->orWhere('miembros', 'LIKE', '%'.$user->id.'%'); // Grupos donde el usuario es miembro
            })
            ->get()
            ->map(function ($grupo) {
                // Generar la URL completa para el logo
                $grupo->logo = $grupo->logo
                ? url('storage/' . $grupo->logo)
                : url('/storage/images/logo.png');
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

        // Verifica si se recibió la ID del grupo
        if (!$id) {
            return response()->json(['message' => 'La id del grupo es requerida'], 400);
        }

        // Obtén el grupo específico del usuario según la ID proporcionada
        $grupo = DB::table('grupos')
            ->select('nombre', 'descripcion', 'created_at', 'logo')
            ->where(function($query) use ($user, $id) {
                // Grupos creados por el usuario o a los que el usuario es miembro
                $query->where('idusuario', $user->id)
                      ->orWhere('miembros', 'LIKE', '%'.$user->id.'%'); // Verifica si el ID del usuario está en el campo 'miembros'
            })
            ->where('id', $id)
            ->first(); // Obtén solo un grupo

        // Verifica si se encontró el grupo
        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        // Generar la URL completa para el logo
        $grupo->logo = $grupo->logo
            ? url('storage/' . $grupo->logo)
            : url('/storage/images/logo.png');

        return response()->json($grupo); // Devuelve el grupo encontrado
    }



    public function showmiembros(Request $request, $id) {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

    if (!$id) {
        return response()->json(['message' => 'La id del grupo es requerida'], 400);
    }

            // Obtén los miembros del grupo
    $grupo = DB::table('grupos')->select('miembros')->where('id', $id)->first();

    if ($grupo) {
        // Decodifica el JSON a un array
        $miembrosArray = json_decode($grupo->miembros, true); // Asegúrate de que este campo sea un JSON válido

        // Asegúrate de que sea un array
        if (!is_array($miembrosArray)) {
            $miembrosArray = [];
        }

        // Obtén los miembros usando el array
        $miembros = DB::table('users')
            ->whereIn('id', $miembrosArray)
            ->select('id as idusuario', 'nickname', 'avatar')
            ->get();
    } else {
        $miembros = []; // Si no hay grupo, asigna un array vacío
    }

    // Procesar los avatares de los miembros
    foreach ($miembros as $miembro) {
        $miembro->avatar = $miembro->avatar
            ? url('storage/' . $miembro->avatar)
            : url('/storage/images/user.png');
    }

    // Devuelve solo los miembros
    return response()->json($miembros);
    }

    public function updateLogo(Request $request, $id) {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Encuentra el grupo al que pertenece el usuario
        $grupo = Grupo::find($id);
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

    public function showmiembrosnotas(Request $request, $id) {
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
                    ->select('users.id as idusuario', 'users.nickname')
                    ->get();

        return response()->json($grupos);
    }
}


