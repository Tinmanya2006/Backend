<?php

namespace App\Http\Controllers;

Use App\Models\Grupo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notificacion;
use App\Models\Nota;

class ControllerGrupo extends Controller
{
    //Esta funcion sirve para crear un grupo.
    public function store(Request $request) {

        //Obtiene el usuario autenticado.
        $user = Auth::user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado.'], 401);
        }

        // Validacion de los datos que llegan del frontend.
        $datosValidados = $request->validate([
            'nombre' => 'required|string|max:45', // El nombre del grupo es obligatorio y tiene un límite de 45 caracteres.
            'descripcion' => 'required|string|max:200', // La descripción del grupo es obligatoria y tiene un límite de 200 caracteres.
            'logo' => 'nullable|image|max:2048', // El logo es opcional, pero debe ser una imagen y no superar 2MB.
            'miembros' => 'array', // Los miembros deben ser enviados como un array.
        ]);

        // Crear una nueva instancia del modelo Grupo y asignar sus atributos.
        $grupo = new Grupo();
        $grupo->nombre = $datosValidados['nombre'];
        $grupo->descripcion = $datosValidados   ['descripcion'];
        $grupo->idusuario = $user->id; // Asignar el ID del usuario autenticado
        $grupo->admin = $user->id; // Establece  al usuario autenticado como administrador.

        // Verificar si se subió un logo y guardarlo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->store('images', 'public'); // Guardar en 'storage/app/public/images'
            $grupo->logo = $path; // Asignar la ruta del archivo al grupo.
        }


        // Obtiene la lista de miembros desde el request o un array vacío por defecto.
        $miembros = $request->input('miembros', []);

         // Añade el ID del creador a la lista de miembros.
        $miembros[] = $user->id;

        // Elimina los miembros duplicados y almacenar los miembros como un JSON en la base de datos.
        $grupo->miembros = json_encode(array_unique($miembros));


        // Guardar el grupo en la base de datos
        $grupo->save();

         // Envia una respuesta exitosa con el ID del grupo creado.
        return response()->json(['message' => 'Grupo creado exitosamente', 'id' => $grupo->id]);

    }


     //Esta funcion actualiza los datos del grupo.
    public function update(Request $request, $id){

        //Esto busca al grupo por su id en la base de datos.
        $grupo = Grupo::find($id);

        //Mensaje de error si el grupo no se encontro en la base de datos.
        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }
        //Esto valida los datos que llegan del Frontend.
        $datosValidados = $request->validate([
            'nombre' => 'required|max:45', // El nombre deñ grupo es obligatorio y no debe exceder los 45 caracteres.
            'admin' => 'max:20|in:usuario,admin',  // El rol del administrador puede ser 'usuario' o 'admin'.
            'descripcion' => 'max:200',  // La descripción no debe exceder 200 caracteres.
        ]);


        //Esto actualiza los datos del grupo, si los datos se validaron correctamente y envia un mensaje.
        if($grupo){
            $grupo->update($datosValidados);
            return response()->json('Datos del grupo actualizado correctamente: ', 204);
        }


    }


    //Esta funcion elimina el grupo.
    public function destroy(Request $request, $id){

        //Obtiene el usuario autenticado.
        $user = Auth::user();

        //Elimina todas las notificaciones relacionadas con el grupo.
        Notificacion::where('idgrupo', $id)->delete();

        //Elimina todas las notas relacionadas con el grupo.
        Nota::where('idgrupo', $id)->delete();

        //Esto busca al grupo por su id.
        $grupo = Grupo::find($id);

         // Verificar si el grupo existe antes de intentar eliminarlo.
        if ($id) {

            // Eliminar el grupo.
            $grupo->delete();

            //Esto muestra dos mensajes, si se ha eliminado el grupo y si no se elimino.
            return response()->json('Grupo eliminado correctamente', 204);
        }   else {
            return response()->json('No se eliminó el Grupo', 406);
         }
    }

    //Esta funcion muestra los grupos.
    public function show(Request $request){

        //Obtiene el usuario autenticado.
        $user = Auth::user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        //Pide los grupos a la base de datos, de que el usuario es miembro o creador.
        $grupos = DB::table('grupos')
            ->select('nombre', 'id', 'logo')  //Se selecciona solo los campos necesarios.
            ->where(function($query) use ($user) {
                // Grupos creados por el usuario
                $query->where('idusuario', $user->id)
                      ->orWhere('miembros', 'LIKE', '%'.$user->id.'%'); // Grupos donde el usuario es miembro
            })

            //Obtiene los grupos
            ->get()

            ->map(function ($grupo) {
                // Asignar la URL completa del logo o una imagen predeterminada si no hay logo.
                $grupo->logo = $grupo->logo
                ? url('storage/' . $grupo->logo)
                : url('/storage/images/logo.png');
                return $grupo;
            });

        //Mensaje que Muestra los grupos
        return response()->json($grupos);
    }


    //Esta funcion muestra los datos del usuario, en el apartado de mostrar los grupos.
    public function datosUsuario(Request $request)
    {
        //Obtiene el usuario autenticado.
        $user = Auth::user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        //Busca el usuario autenticado en la base de datos y obtiene los datos.
        $user = DB::table('users')
                    ->select('name', 'nickname', 'biografia', 'avatar')  //Se seleccionan los campos necesarios.
                    ->where('id', $user->id)
                    ->first();


        // Verificar si se encontraron los datos del usuario.
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado.'], 404);
        }

        // Construir la URL completa del avatar, o asignar una imagen predeterminada si no existe.
        $user->avatar = $user->avatar
            ? url('storage/' . $user->avatar)
            : url('/storage/images/user.png');

        //Mensaje con los datos del usuario obtenidos
        return response()->json($user);
    }

    //Esta funcion muestra los datos del grupo y utiliza la id que llego del frontend del grupo.
    public function datosGrupo(Request $request, $id)
    {
        //Obtiene el usuario autenticado.
        $user = Auth::user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Verifica si se recibió la ID del grupo
        if (!$id) {
            return response()->json(['message' => 'La id del grupo es requerida'], 400);
        }

        // Busca el grupo por su ID
        $grupo = Grupo::find($id);

        // Verifica si se encontró el grupo, si no se envia un mensaje.
        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

    // Generar la URL completa para el logo del grupo o asignar un logo predeterminado.
            $grupo->logo = $grupo->logo
            ? url('storage/' . $grupo->logo)
            : url('/storage/images/logo.png');


        // Determinar si el usuario autenticado es el administrador del grupo.
        $isAdmin = $grupo->admin === $user->id;

        //Se envia un mensaje con los datos del grupo y el estado de administrador
        return response()->json([
            'grupo' => [
                'nombre' => $grupo->nombre,
                'descripcion' => $grupo->descripcion,
                'created_at' => $grupo->created_at->toDateString(),
                'logo' => $grupo->logo,
            ],
            'isAdmin' => $isAdmin // Indicar si el usuario es administrador del grupo.
        ]);
    }

    //Esto funcion autentica al administrador del grupo.
    public function showAdmin($id)
    {
        //Esto Autentica el grupo por su id
        $grupo = Grupo::findOrFail($id);

        //Esto autentica al usuario.
        $user = auth()->user();

        // Se obtiene el administrador del grupo y los datos
        return response()->json([
            'perfil' => [
                'nombre' => $grupo->nombre,
                'descripcion' => $grupo->descripcion,
                // Otros datos del grupo
            ],
            'esAdmin' => $grupo->admin === $user->id //Se envia true a la consola, si el usuario es el administrador.
        ]);
    }

    //Esta funcion verifica el administrador
    public function cargarAdmin($id)
    {
        //Esto Autentica el grupo por su id.
        $grupo = Grupo::findOrFail($id);

        //Esto Autentica el usuario.
        $user = auth()->user();

        //Se obtiene la respuesta del administrador
        return response()->json([
            'esAdmin' => $grupo->admin === $user->id // Se envia true a la consola si el usuario es el administrador.
        ]);
    }


    //Esta funcion sirve para mostrar los miembros.
    public function showmiembros(Request $request, $id) {

        //Esto Autentica el usuario
        $user = Auth::user();

         //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Verifica si se recibió la ID del grupo
        if (!$id) {
            return response()->json(['message' => 'La id del grupo es requerida'], 400);
        }

        //Se obtiene el grupo por su ID
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

            //Se envian los datos a la consola
            return response()->json([
                'miembros' => $miembros,
                'esAdmin' => $esAdmin, // Indica si el usuario es administrador
                'adminId' => $grupo->admin // Agregar el ID del administrador
            ]);
        }

        //Se envia un mensaje a la consola
        return response()->json(['message' => 'Grupo no encontrado'], 404); // Grupo no encontrado
    }

    //Esta funcion sirve para cambiar el logo
    public function updateLogo(Request $request, $id) {

        //Esto Autentica el usuario
        $user = Auth::user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        // Encuentra el grupo al que pertenece el usuario y envia un mensaje.
        $grupo = Grupo::find($id);
        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        // Verifica si el archivo 'logo' se ha subido
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->store('images', 'public'); // Guarda el logo en 'storage/app/public/images'

            // Actualiza la ruta del logo en la base de datos
            $grupo->logo = $path;

            //Se guarda el logo
            $grupo->save();

            //Se envia un mensaje a la consola del logo actualizado
            return response()->json(['message' => 'Logo actualizada correctamente']);
        }

        //Se envia un mensaje a la consola de logo no actualizado.
        return response()->json(['message' => 'No se actualizo el logo'], 400);
    }

    //Esta funcion sirve para mostrar los nombres de los miembros.
    public function showmiembrosnotas(Request $request, $id) {

        //Esto Autentica el usuario
        $user = Auth::user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
        // Verifica si se recibió la ID del grupo
        if (!$id) {
            return response()->json(['message' => 'La id del grupo es requerida'], 400);
        }

        //Se obtiene el grupo por su id
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

            //Se obtienen los miembros de la base de dato por su id y obtiene su nickname
            $miembros = DB::table('users')->whereIn('id', $miembrosIds)->select('id as idusuario', 'nickname')->get();

            //Se envian los miembros a la consola
            return response()->json($miembros);
        }

        //Se envia un mensaje a la consola
        return response()->json(['message' => 'Grupo no encontrado'], 404); // Grupo no encontrado
    }

    //Esta funcion sirve para eliminar un miembro
    public function eliminarMiembro($idgrupo, $idmiembro)
    {
        // Encuentra el grupo por su id
        $grupo = Grupo::find($idgrupo);

        //Se envia un mensaje a la consola
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

            //Se guardan los cambios
            $grupo->save();

            //Se envia un mensaje a la consola
            return response()->json(['message' => 'Miembro eliminado'], 200);
        }

        //Se envia un mensaje a la consola.
        return response()->json(['message' => 'Miembro no encontrado en el grupo'], 404);
    }

    //Esta funcion sirve para abandonar el grupo
    public function abandonarGrupo(Request $request, $id)
    {
        //Esto Autentica el usuario
        $user = Auth::user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        // Encuentra el grupo
        $grupo = Grupo::find($id);

        //Verifica si se encontro el grupo.
        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        //Verifica si es el creador de grupo y envia un mensaje si es.
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

            //Se guardan los cambios
            $grupo->save();

            //Se envia un mensaje a la consola.
            return response()->json(['message' => 'Has abandonado el grupo exitosamente.'], 200);
        }

        //Se envia un mensaje a la consola.
        return response()->json(['message' => 'No se encontró al usuario en el grupo.'], 404);
    }

    //Esta funcion sirve para abandonar el grupo si eres administrador y asignarle a otro miembro el rol
    public function asignarAdminYAbandonar($id, $nuevoAdminId)
    {
        // Esto autentica el usuario.
        $user = auth()->user();

        //Esto Autentica el grupo por su id.
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

        //Se guarda el cambio
        $grupo->save();

        // Decodifica los miembros si existen, manteniendo un formato de array
        $miembros = $grupo->miembros ? json_decode($grupo->miembros, true) : [];

        // Elimina al administrador actual de la lista de miembros solo si está presente
        if (in_array($user->id, $miembros)) {
            $miembros = array_values(array_diff($miembros, [$user->id])); // Remueve el ID del usuario
            $grupo->miembros = json_encode($miembros); // Convierte a JSON manteniendo formato de array

            //Se guardan los cambios.
            $grupo->save();
        }

        //Se envia un mensaje a la consola
        return response()->json(['message' => 'Nuevo administrador asignado y has abandonado el grupo con éxito'], 200);
    }

    //Esta funcion sirve para verifiar el administrador.
    public function verificarAdmin($id)
    {
        // Esto autentica el usuario.
        $user = auth()->user();

        //Esto Autentica el grupo por su id.
        $grupo = Grupo::findOrFail($id);

        //Envia un mensaje si es administrador
        return response()->json([
            'esAdmin' => $grupo->admin === $user->id
        ]);
    }

    public function asignarAdmin($id, $nuevoAdminId)
    {
        // Esto autentica el usuario.
        $user = auth()->user();

        //Esto Autentica el grupo por su id.
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

        //Se guardan los cambios
        $grupo->save();

        //Se envia un mensaje a la consola.
        return response()->json(['message' => 'Nuevo administrador asignado con éxito'], 200);
    }
}


