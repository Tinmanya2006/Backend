<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ControllerNotificacion extends Controller
{

    public function enviarInvitacionGrupo(Request $request, $id) {

        // Esto autentica el usuario.
        $user = $request->user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['mensaje' => 'Usuario no autenticado'], 401);
        }

        // Busca el grupo usando el ID que se pasa como parámetro
        $grupo = Grupo::find($id);

        //Mensaje de error si el grupo no se encontro en la base de datos.
        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        // Busca el usuario por el nickname proporcionado
        $usuario = User::where('nickname', $request->nickname)->first();

        //Mensaje de error si el usuario no se lo encontro en la base de datos
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Se crea la notificacion con un texto por defecto.
        $notificacion = Notificacion::create([
            'idusuario' => $usuario->id, // Usa el ID del usuario encontrado
            'idgrupo' => $grupo->id, // Asegúrate de que el campo se llame 'grupo_id'
            'titulo' => 'Invitación al Grupo',
            'mensaje' => 'Has sido invitado a unirte al grupo ' . $grupo->nombre,
        ]);

        //Se envia un mensaje a la consola
        return response()->json(['message' => 'Invitación enviada.', 'notificacion' => $notificacion]);
    }

    //Esta funcion sirve para responder la notificacion.
    public function responderInvitacion(Request $request, $id)
    {
        //Se busca la notificacion por su id
        $notificacion = Notificacion::find($id);

        //Mensaje de error si la notificacion no se encontro en la base de datos.
        if (!$notificacion) {
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }

        //Si la invitacion ya fue respondida se envia un mensaje.
        if ($notificacion->estado !== 'Pendiente') {
            return response()->json(['message' => 'La invitación ya fue respondida.'], 400);
        }

        //Esta es la respuesta de la notificacion
        $estado = $request->input('estado');
        if (!in_array($estado, ['Aceptada', 'Rechazada'])) {
            return response()->json(['message' => 'Estado inválido.'], 400);
        }

        //Esto se encarga de cambiar el estado de la notificacion correcta.
        $notificacion->estado = $estado;
        $notificacion->save();

        //Esto ve si se acepto la solicitud
        if ($estado === 'Aceptada') {

            //Verifica el id del grupo si es igual al de la notificacion
            $idgrupo = $notificacion->idgrupo;

            //Mensaje de error que la id del grupo es nula
            if (!$idgrupo) {
                return response()->json(['message' => 'El campo idgrupo es nulo'], 400);
            }

            //Se busca al grupo por su id
            $grupo = Grupo::find($idgrupo);

            //Mensaje de error si el grupo no se encontro en la base de datos.
            if (!$grupo) {
                return response()->json(['message' => 'Grupo no encontrado'], 404);
            }

            // Agregar al usuario a los miembros en el campo JSON
            $grupo->agregarMiembro($notificacion->idusuario);
        }

        //Se envia un mensaje a la consola.
        return response()->json(['message' => 'Respuesta registrada correctamente.']);
    }

    //Esta funcion sirve para mostrar las notificaciones del usuario autenticado
    public function show(Request $request)
    {
        //Esto autentica a el usuario.
        $user = Auth::user();

        //Si el usuario no esta autenticado, se envia un mensaje de "Usuario no autenticado"
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        //Esto obtiene las notas del usuario desde la base de datos, mediante condiciones.
        $notificaciones = DB::table('notificacions')
                    ->select('titulo', 'mensaje', 'id', 'idgrupo')
                    ->where('idusuario', $user->id)
                    ->where('estado', 'Pendiente')
                    ->get();

        //Esto envia las notas obtenidas.
        return response()->json($notificaciones);
    }
}
