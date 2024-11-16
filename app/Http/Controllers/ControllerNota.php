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
use App\Models\Grupo;


class ControllerNota extends Controller
{


    //Comienzo de Funciones Para crear notas.


    //Esta funcion sirve para crear una nota.

    public function store(Request $request)
    {

        //Esto valida los datos que llegan del Frontend.
        $datosvalidados = $request->validate([
            'descripcion' => 'max:300',
            'categoria' => 'required|in:Trabajo,Estudios,Gimnasio,Dieta,Ocio,Viajes,Otro',
            'prioridad' => 'required|in:Baja,Media,Alta',
            'asignacion' => 'boolean',
        ]);


        //Esto Chequea que el usuario esta autenticado y si no lo esta, envia un mensaje de usuario no autenticado.
        if (!auth()->check()) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        //Esto sirve para obtener el id del usuario y asignarselo a el campo idusuario en datos validados
        $datosvalidados['idusuario'] = auth()->id();


        //Esto crea una nota si los datos se validaron correctamente y la guarda en la base de datos.
        $nota = Nota::create($datosvalidados);

        //Esto envia un mensaje a la consola de que la nota se a creado correctamente.
        return response()->json([
            'message' => 'La nota ha sido creada correctamente',
            'nota' => $nota
        ]);

    }

     //Esta funcion sirve para crear las notas del grupo.
    public function notagrupo(Request $request, $id)
    {

        //Esto valida los datos que llegan del Frontend.
        $datosvalidados = $request->validate([
            'descripcion' => 'max:300',
            'prioridad' => 'required|in:Baja,Media,Alta',
            'asignacion' => 'required|max:20',
        ]);

        //Esto Chequea que el usuario esta autenticado y si no lo esta, envia un mensaje de usuario no autenticado.
        if (!auth()->check()) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        //Esto verifica que el usuario pertenezca al grupo y envia un mensaje
        if (!$id) {
            return response()->json(['message' => 'El usuario no pertenece a un grupo válido'], 400);
        }

        //Se validan los datos y se define la id en datosValidados.
        $datosvalidados['idgrupo'] = $id;

        //Esto crea una nota si los datos se validaron correctamente.
        $nota = Nota::create($datosvalidados);

        //Esto envia un mensaje a la consola de que la nota se a creado correctamente.
        return response()->json([
            'messaje' => 'La nota ha sido creada correctamente',
            'nota' => $nota
        ]);

    }

    //Terminacion de Funciones de crear notas.






    //Comienzo de Funciones de Actualizar Notas.

    //Esta funcion sirve para actualizar las notas
    public function update(Request $request, $id)
    {
        // Esto autentica el usuario.
        $user = $request->user();

        //Mensaje de error si el usuario no esta autenticado.
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

        //Se busca la nota por su id y si pertenece al usuario.
        $nota = $user->notas()->find($id);

        //Esto actualiza la nota del usuario, si los datos se validaron correctamente.
        if ($nota) {
                $estado = $datosValidados['estado'] ?? $nota->estado;
                $finalizacion = $datosValidados['finalizacion'] ?? ($estado === 'Completada' ? now() : $nota->finalizacion);

            //Se cambia el estado y la fecha de finalización de la nota
            $nota->update([
                'estado' => $estado,
                'finalizacion' => $finalizacion,
            ]);

            //Esto muestra dos mensajes si se han actualizado los datos correctamente
            //y a su vez muestra si los mismos no se pudieron actualizar.
            return response()->json(['messaje' => 'Se ha actualizado el estado de la nota']);
        } else {
            return response()->json(['messaje' => 'No se ha podido actualizar el estado de la nota'], 404);
        }
    }

    //Esta funcion sirve para actualizar las notas del grupo.
    public function updategrupo(Request $request, $id, $idgrupo)
    {
       // Esto autentica el usuario.
        $user = $request->user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['mensaje' => 'Usuario no autenticado'], 401);
        }

        //Esto busca al usuario por su id, que es recibida del frontend
        $grupo = Grupo::find($idgrupo);

        // Verifica si se recibió la ID del grupo
        if (!$grupo) {
            return response()->json(['message' => 'La id del grupo es requerida'], 400);
        }

        //Esto valida los datos que llegan del Frontend.
        $datosValidados = $request->validate(
            [
                'estado' => 'in:Pendiente,Completada',
                'finalizacion' => 'nullable|date_format:Y-m-d H:i:s',
            ]
        );

        //Se busca la nota por su id y si pertenece al grupo.
        $nota = Nota::where('id', $id)->where('idgrupo', $idgrupo)->first();

        //Esto actualiza los datos de la nota, si los datos se validaron correctamente.
        if ($nota) {
                $estado = $datosValidados['estado'] ?? $nota->estado;
                $finalizacion = $datosValidados['finalizacion'] ?? ($estado === 'Completada' ? now() : $nota->finalizacion);

            //Se cambia el estado y la fecha de finalización de la nota
            $nota->update([
                'estado' => $estado,
                'finalizacion' => $finalizacion,
            ]);

            //Esto muestra dos mensajes si se han actualizado los datos correctamente
            //y a su vez muestra si los mismos no se pudieron actualizar.
            return response()->json(['messaje' => 'Se ha actualizado el estado de la nota']);
        } else {
            return response()->json(['messaje' => 'No se ha podido actualizar el estado de la nota'], 404);
        }
    }

        //Terminacion de Funciones de Actualizar Notas.








        //Comienzo de Funciones de Eliminar Notas.

        //Esta funcion sirve para eliminar la nota.
     public function destroy(Request $request, $id)
    {
        // Esto autentica el usuario.
         $user = $request->user();

        //Mensaje de error si el usuario no esta autenticado.
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

         //Se envia un mensaje a la consola
         return response()->json(['message' => 'Nota eliminada correctamente'], 200);
    }



     public function destroygrupo(Request $request, $id, $idgrupo)
    {
        // Esto autentica el usuario.
         $user = $request->user();

        //Mensaje de error si el usuario no esta autenticado.
         if (!$user) {
             return response()->json(['message' => 'Usuario no autenticado'], 401);
         }

        //Esto busca al grupo por su id
         $grupo = Grupo::find($idgrupo);

        // Verifica si se recibió la ID del grupo
         if (!$grupo) {
             return response()->json(['message' => 'La id del grupo es requerida'], 400);
         }

        // Verificamos si el usuario tiene una nota con ese ID
         $nota = Nota::where('id', $id)->where('idgrupo', $idgrupo)->first();
         if (!$nota) {
             return response()->json(['message' => 'Nota no encontrada o no perteneciente al usuario'], 404);
         }

        // Eliminamos la nota si existe
         $nota->delete();

        //Se envia un mensaje a la consola.
         return response()->json(['message' => 'Nota eliminada correctamente'], 200);
    }

        //Terminación de Funcion de Eliminar Notas.







        //Comienzo de Funciones de Mostrar Notas Pendientes.

    //Esta funcion sirve para mostrar las notas del usuario autenticado.
     public function show(Request $request)
    {
        //Esto autentica a el usuario.
        $user = Auth::user();

        //Si el usuario no esta autenticado, se envia un mensaje de "Usuario no autenticado"
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        //Esto obtiene las notas del usuario desde la base de datos, mediante condiciones.
        $notas = DB::table('notas')
                    ->select('descripcion', 'categoria', 'prioridad', 'asignacion', 'id')
                    ->where('idusuario', $user->id)
                    ->where('estado', 'Pendiente')
                    ->whereNull('idgrupo')
                    ->get();

        //Esto envia las notas obtenidas.
        return response()->json($notas);
    }


    //Esta funcion sirve para mostrar las notas del grupo.
    public function shownotagrupo(Request $request, $id)
    {
        // Esto autentica el usuario.
        $user = Auth::user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        // Verifica si se recibió la ID del grupo
        if (!$id) {
            return response()->json(['message' => 'La id del grupo es requerida'], 400);
        }

        // Obtener las notas para el grupo específico
        $notas = DB::table('notas')
                    ->select('descripcion', 'prioridad', 'asignacion', 'id', 'estado')
                    ->where('idgrupo', '=', $id) // Solo notas que pertenecen a un grupo
                    ->where('estado', 'Pendiente')
                    ->get();

        // Obtener los nicknames de los miembros asignados
        $miembrosIds = $notas->pluck('asignacion')->unique()->toArray();

        // Obtener miembros con sus avatares completos
        $miembros = DB::table('users')
            ->whereIn('nickname', $miembrosIds)
            ->select('nickname', 'avatar')
            ->get();

        // Construir URL completa para los avatares o proporcionar un avatar por defecto
        $miembros->map(function ($miembro) {
            $miembro->avatar = $miembro->avatar
                ? url('storage/' . $miembro->avatar)
                : url('/storage/images/user.png');
            return $miembro;
        });

        // Crear un mapa de nicknames a avatares
        $avataresPorNickname = $miembros->pluck('avatar', 'nickname')->toArray();

        // Mapear las notas para incluir el avatar correspondiente y permisos de edición solo para el miembro asignado
        $notasConUsuario = $notas->map(function($nota) use ($avataresPorNickname, $user) {
            return [
                'id' => $nota->id,
                'descripcion' => $nota->descripcion,
                'prioridad' => $nota->prioridad,
                'estado' => $nota->estado,
                'asignacion' => $nota->asignacion,
                'avatar' => $avataresPorNickname[$nota->asignacion] ?? url('/storage/images/user.png'), // URL del avatar
                'editable' => $nota->asignacion === $user->nickname // Permitir edición solo si el usuario autenticado es el asignado
            ];
        });

        //Esto envia las notas obtenidas.
        return response()->json($notasConUsuario);
    }

        //Terminación de Funcion de Mostrar Notas Pendientes.








        //Comienzo de Funciones de Mostrar Notas Completadas.

    //Esta funcion sirve para mostrar las notas del usuario autenticado
    public function showcompletadas(Request $request)
    {
        // Esto autentica el usuario.
        $user = Auth::user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        //Esto obtiene las notas completadas del usuario
        $notas = DB::table('notas')
                    ->select('descripcion', 'categoria', 'finalizacion')
                    ->where('idusuario', $user->id)
                    ->where('estado', 'Completada')
                    ->orderBy('finalizacion', 'desc')
                    ->limit(5)
                    ->get();

        //Se envian las notas a la consola
        return response()->json($notas);
    }


    //Esta funcion sirve para mostrar las notas completadas del grupo.
    public function shownotagrupocompletadas(Request $request, $id)
    {
        // Esto autentica el usuario.
        $user = Auth::user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        // Verifica si se recibió la ID del grupo
        if (!$id) {
            return response()->json(['message' => 'La id del grupo es requerida'], 400);
        }

        // Obtener las notas completadas del grupo
        $notas = DB::table('notas')
                    ->select('descripcion', 'prioridad', 'asignacion', 'id', 'estado')
                    ->where('idgrupo', '=', $id) // Solo notas que pertenecen a un grupo
                    ->where('estado',  'Completada')
                    ->get();

        // Obtener los nicknames de los miembros asignados
        $miembrosIds = $notas->pluck('asignacion')->unique(); // Obtener los nicknames únicos

        // Obtener miembros con sus avatares completos
        $miembros = DB::table('users')
            ->whereIn('nickname', $miembrosIds) // Cambié `id` a `nickname`
            ->select('id as idusuario', 'nickname', 'avatar')
            ->get();

        // Construir URL completa para los avatares o proporcionar un avatar por defecto
        foreach ($miembros as $miembro) {
            $miembro->avatar = $miembro->avatar
                ? url('storage/' . $miembro->avatar)
                : url('/storage/images/user.png');
        }

        // Asociar cada nota con el avatar correspondiente
        $notasConAvatar = $notas->map(function ($nota) use ($miembros) {
            $miembro = $miembros->firstWhere('nickname', $nota->asignacion); // Buscar el miembro por nickname

            return [
                'id' => $nota->id,
                'descripcion' => $nota->descripcion,
                'prioridad' => $nota->prioridad,
                'estado' => $nota->estado,
                'asignacion' => $nota->asignacion,
                'avatar' => $miembro ? $miembro->avatar : url('/storage/images/user.png') // Asigna el avatar o un avatar por defecto
            ];
        });

        //Envia las notas completadas del grupo a la consola
        return response()->json($notasConAvatar);
    }
    //Terminación de Funcion de Mostrar Notas Completadas.
}
