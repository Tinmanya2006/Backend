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

            $user = $request->user();

            if (!$user) {
                return response()->json(['mensaje' => 'Usuario no autenticado'], 401);
            }

            // Busca el grupo usando el ID que se pasa como parámetro
            $grupo = Grupo::find($id);

            if (!$grupo) {
                return response()->json(['message' => 'Grupo no encontrado'], 404);
            }

            // Busca el usuario por el nickname proporcionado
            $usuario = User::where('nickname', $request->nickname)->first();

            if (!$usuario) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }

            // Asegúrate de incluir el idgrupo en la creación de la notificación
            $notificacion = Notificacion::create([
                'idusuario' => $usuario->id, // Usa el ID del usuario encontrado
                'idgrupo' => $grupo->id, // Asegúrate de que el campo se llame 'grupo_id'
                'titulo' => 'Invitación al Grupo',
                'mensaje' => 'Has sido invitado a unirte al grupo ' . $grupo->nombre,
            ]);

            return response()->json(['message' => 'Invitación enviada.', 'notificacion' => $notificacion]);
        }

    public function responderInvitacion(Request $request, $id)
    {
        $notificacion = Notificacion::find($id);

    if (!$notificacion) {
        return response()->json(['message' => 'Notificación no encontrada'], 404);
    }

    if ($notificacion->estado !== 'Pendiente') {
        return response()->json(['message' => 'La invitación ya fue respondida.'], 400);
    }

    $estado = $request->input('estado');
    if (!in_array($estado, ['Aceptada', 'Rechazada'])) {
        return response()->json(['message' => 'Estado inválido.'], 400);
    }

    $notificacion->estado = $estado;
    $notificacion->save();

    if ($estado === 'Aceptada') {
        $idgrupo = $notificacion->idgrupo;

        if (!$idgrupo) {
            return response()->json(['message' => 'El campo idgrupo es nulo'], 400);
        }

        $grupo = Grupo::find($idgrupo);

        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        // Agregar al usuario a los miembros en el campo JSON
        $grupo->agregarMiembro($notificacion->idusuario);
    }

    return response()->json(['message' => 'Respuesta registrada correctamente.']);
    }

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
