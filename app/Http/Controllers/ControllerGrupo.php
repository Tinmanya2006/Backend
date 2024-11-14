<?php

namespace App\Http\Controllers;

Use App\Models\Grupo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notificacion;

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
        $grupo->admin = $user->id;

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

        $user = Auth::user();

        Notificacion::where('idgrupo', $id)->delete();

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

    public function datosGrupo(Request $request, $id)
{
    // Obtén el usuario autenticado
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    // Verifica si se recibió la ID del grupo
    if (!$id) {
        return response()->json(['message' => 'La id del grupo es requerida'], 400);
    }

    // Obtén el grupo específico según la ID proporcionada
    $grupo = Grupo::find($id); // Aquí cambiamos a Eloquent

    // Verifica si se encontró el grupo
    if (!$grupo) {
        return response()->json(['message' => 'Grupo no encontrado'], 404);
    }

    // Generar la URL completa para el logo
    $grupo->logo = $grupo->logo
        ? url('storage/' . $grupo->logo)
        : url('/storage/images/logo.png');

    // Verifica si el usuario es el administrador del grupo
    $isAdmin = $grupo->admin === $user->id; // Suponiendo que 'admin' es el ID del administrador

    return response()->json([
        'grupo' => [
            'nombre' => $grupo->nombre,
            'descripcion' => $grupo->descripcion,
            'created_at' => $grupo->created_at->toDateString(),
            'logo' => $grupo->logo,
        ],
        'isAdmin' => $isAdmin // true si el usuario es el administrador
    ]);
}

    public function showAdmin($id)
{
    $grupo = Grupo::findOrFail($id);
    $user = auth()->user();

    return response()->json([
        'perfil' => [
            'nombre' => $grupo->nombre,
            'descripcion' => $grupo->descripcion,
            // Otros datos del grupo
        ],
        'esAdmin' => $grupo->admin === $user->id // true si el usuario es el administrador
    ]);
}

public function cargarAdmin($id)
{
    $grupo = Grupo::findOrFail($id);
    $user = auth()->user();

    return response()->json([
        'esAdmin' => $grupo->admin === $user->id // true si el usuario es el administrador
    ]);
}



public function showmiembros(Request $request, $id) {
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    if (!$id) {
        return response()->json(['message' => 'La id del grupo es requerida'], 400);
    }

    // Obtén el grupo por su ID
    $grupo = DB::table('grupos')->where('id', $id)->first();

    if ($grupo) {
        // Verifica si el usuario autenticado es el administrador del grupo
        $esAdmin = $grupo->admin == $user->id;

        // Elimina los espacios y decodifica el JSON
        $miembrosIds = json_decode(trim($grupo->miembros, '"'));

        // Verificar si json_decode devolvió un array válido
        if (is_null($miembrosIds) || !is_array($miembrosIds)) {
            return response()->json(['error' => 'Formato de miembros inválido'], 500);
        }

        // Validar que todos los IDs sean enteros
        $miembrosIds = array_filter(array_map('intval', $miembrosIds));

        // Consultar los usuarios en base a los IDs
        if (empty($miembrosIds)) {
            return response()->json([], 404); // No hay miembros para mostrar
        }

        // Obtener miembros con sus avatares completos
        $miembros = DB::table('users')
            ->whereIn('id', $miembrosIds)
            ->select('id as idusuario', 'nickname', 'avatar')
            ->get();

        // Construir URL completa para los avatares o proporcionar un avatar por defecto
        foreach ($miembros as $miembro) {
            $miembro->avatar = $miembro->avatar
                ? url('storage/' . $miembro->avatar)
                : url('/storage/images/user.png');
        }

        return response()->json([
            'miembros' => $miembros,
            'esAdmin' => $esAdmin, // Indica si el usuario es administrador
            'adminId' => $grupo->admin // Agregar el ID del administrador
        ]);
    }

    return response()->json(['message' => 'Grupo no encontrado'], 404); // Grupo no encontrado
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

        $grupo = DB::table('grupos')->where('id', $id)->first();

        if ($grupo) {
            // Elimina los espacios y decodifica el JSON
            $miembrosIds = json_decode(trim($grupo->miembros, '"'));

            // Verificar si json_decode devolvió un array válido
            if (is_null($miembrosIds) || !is_array($miembrosIds)) {
                return response()->json(['error' => 'Formato de miembros inválido'], 500);
            }

            // Validar que todos los IDs sean enteros
            $miembrosIds = array_filter(array_map('intval', $miembrosIds));

            // Consultar los usuarios en base a los IDs
            if (empty($miembrosIds)) {
                return response()->json([], 404); // No hay miembros para mostrar
            }

            $miembros = DB::table('users')->whereIn('id', $miembrosIds)->select('id as idusuario', 'nickname')->get();

            return response()->json($miembros);
        }

        return response()->json(['message' => 'Grupo no encontrado'], 404); // Grupo no encontrado
    }

    public function eliminarMiembro($idgrupo, $idmiembro)
    {
        // Encuentra el grupo
        $grupo = Grupo::find($idgrupo);

        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        // Obtén la lista de miembros decodificando el JSON
        $miembros = json_decode($grupo->miembros, true); // true para obtener un array asociativo

        // Convierte el ID del miembro a entero para la comparación
        $idmiembro = (int) $idmiembro;

        // Si el miembro está en la lista, elimínalo
        if (($key = array_search($idmiembro, $miembros)) !== false) {
            unset($miembros[$key]);
            // Actualiza la columna de miembros
            $grupo->miembros = json_encode(array_values($miembros)); // Reindexar y volver a codificar a JSON
            $grupo->save();

            return response()->json(['message' => 'Miembro eliminado'], 200);
        }

        return response()->json(['message' => 'Miembro no encontrado en el grupo'], 404);
    }

    public function abandonarGrupo(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Encuentra el grupo
        $grupo = Grupo::find($id);

        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        // Si el usuario es el creador del grupo, manejar la salida de otra forma o advertirle.
        if ($grupo->idusuario === $user->id) { // Asegúrate de que el campo sea el ID del creador.
            return response()->json(['message' => 'No puedes abandonar el grupo porque eres el creador.'], 403);
        }

        // Decodifica la lista de miembros
        $miembros = json_decode($grupo->miembros, true);

        // ID del usuario que abandona el grupo
        $idmiembro = $user->id;

        // Si el miembro está en la lista, elimínalo
        if (($key = array_search($idmiembro, $miembros)) !== false) {
            unset($miembros[$key]);
            $grupo->miembros = json_encode(array_values($miembros)); // Reindexa y guarda
            $grupo->save();

            return response()->json(['message' => 'Has abandonado el grupo exitosamente.'], 200);
        }

        return response()->json(['message' => 'No se encontró al usuario en el grupo.'], 404);
    }

    public function asignarAdminYAbandonar($id, $nuevoAdminId)
    {
        $user = auth()->user(); // Usuario autenticado
        $grupo = Grupo::findOrFail($id);

        // Validación de grupo
        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        // Verifica si el usuario es el administrador actual
        if ((int)$user->id !== (int)$grupo->admin) {
            return response()->json(['message' => 'No eres el administrador del grupo'], 403);
        }

        // Asigna al nuevo administrador
        $grupo->admin = $nuevoAdminId;
        $grupo->idusuario = $nuevoAdminId; // Actualiza el campo idusuario al nuevo administrador
        $grupo->save();

        // Decodifica los miembros si existen, manteniendo un formato de array
        $miembros = $grupo->miembros ? json_decode($grupo->miembros, true) : [];

        // Elimina al administrador actual de la lista de miembros solo si está presente
        if (in_array($user->id, $miembros)) {
            $miembros = array_values(array_diff($miembros, [$user->id])); // Remueve el ID del usuario
            $grupo->miembros = json_encode($miembros); // Convierte a JSON manteniendo formato de array
            $grupo->save();
        }

        return response()->json(['message' => 'Nuevo administrador asignado y has abandonado el grupo con éxito'], 200);
    }

    public function verificarAdmin($id)
    {
        $user = auth()->user();
        $grupo = Grupo::findOrFail($id);

        return response()->json([
            'esAdmin' => $grupo->admin === $user->id
        ]);
    }

    public function asignarAdmin($id, $nuevoAdminId)
{
    $user = auth()->user(); // Usuario autenticado
    $grupo = Grupo::findOrFail($id);

    // Validación de grupo
    if (!$grupo) {
        return response()->json(['message' => 'Grupo no encontrado'], 404);
    }

    // Verifica si el usuario es el administrador actual
    if ((int)$user->id !== (int)$grupo->admin) {
        return response()->json(['message' => 'No eres el administrador del grupo'], 403);
    }

    // Asigna al nuevo administrador
    $grupo->admin = $nuevoAdminId;
    $grupo->idusuario = $nuevoAdminId; // Actualiza el campo idusuario al nuevo administrador
    $grupo->save();

    return response()->json(['message' => 'Nuevo administrador asignado con éxito'], 200);
}
}


