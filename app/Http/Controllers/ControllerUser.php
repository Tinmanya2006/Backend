<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ControllerUser extends Controller
{

    //Esta funcion sirve para crear un usuario.
    public function store(Request $request)
    {
        //Esto valida los datos que llegan del Frontend.
        $datosValidados = $request->validate(
            [
                'name' => 'required|max:30',
                'nickname' => 'required|unique:users|max:20',
                'email' => 'required|unique:users|max:45',
                'password' => 'required|max:40',
            ]
        );

        //Esto crea el usuario si los datos se validaron correctamente.
        User::create([
            'name' => $datosValidados['name'],
            'nickname' => $datosValidados['nickname'],
            'email' => $datosValidados['email'],
            'password' => bcrypt($datosValidados['password']), //Se encripta la contraseña al guardarla
        ]);

        //Se envia un mensaje a la consola
        return response()->json("Usuario creado", 200);
    }


    //Esta funcion actualiza los datos del usuario.
    public function update(Request $request)
    {
        //Esto busca al usuario por su id para poder actualizar sus datos.
        $user = $request->user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['mensaje' => 'Usuario no autenticado'], 401);
        }

        //Esto valida los datos que llegan del Frontend.
        $datosValidados = $request->validate(
            [
                'name' => 'sometimes|max:30',
                'nickname' => 'sometimes|unique:users|max:20',
                'biografia' => 'sometimes|max:120',
            ]
        );

        //Esto actualiza los datos del usuario, si los datos se validaron correctamente.
        if ($user) {
            $user->update([
                'name' => $datosValidados['name'] ?? $user->name,
                'nickname' => $datosValidados['nickname'] ?? $user->nickname,
                'biografia' => $datosValidados['biografia'] ?? $user->biografia,
            ]);

            //Esto muestra dos mensajes si se han actualizado los datos correctamente
            //y a su vez muestra si los mismos no se pudieron actualizar.
            return response()->json(['messaje' => 'Se ha actualizado los datos del usuario']);
        } else {
            return response()->json(['messaje' => 'No se pudieron actualizar los datos del usuario'], 404);
        }
    }

    //Esta funcion elimina el usuario <- NO SE UTILIZA PORQUE NUNCA SE PENSO EN ELIMINAR A UN USUARIO
    public function destroy(Request $request, $id)
    {
        //Esto busca al usuario por su id para poder actualizar sus datos.
        $user = User::find($id);

        //Esto elimina al usuario, si se encuentra su id.
        if ($user) {

        $user->delete();

        //Esto muestra dos mensajes, uno por si se logró eliminar el usuario y otro por si no se logró.
        return response()->json('Usuario eliminado correctamente', 204);
     }  else {
        return response()->json('No se eliminó el usuario', 406);
     }
    }

    //esta funcion sirve para cambiar la contraseña.
    public function cambiarContraseña(Request $request)
    {
        //Esto autentica al usuario, si el usuario no esta autenticado se envia un mensaje.
        if (!Auth::check()) {
            return response()->json(['message' => 'No estás autenticado.'], 401);
        }

        //Esto valida los datos que llegan del Frontend.
        $datosValidados = $request->validate([
            'password' => 'required',
            'newpassword' => 'required|min:8|confirmed',
        ]);


        //Esto autentica al usuario que se utiliza.
        $user = $request->user();

        //Esto chequea si la contraseña actual es correcta, si no se envia un mensaje de error.
        if (!Hash::check($datosValidados['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        //Esto cambia la contraseña y la encripta.
        $user->password = Hash::make($datosValidados['newpassword']);

        //Esto guarda la contraseña.
        $user->save();

        //Esto muestra un mensaje si la contraseña se cambio correctamente.
        return response()->json(['message' => 'Contraseña cambiada correctamente.']);
    }

    //Esta funcion sirve para mostrar los datos del usuario autenticado.
    public function show(Request $request)
    {
        //Se autentica el usuario
        $user = Auth::user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        //Se obtienen los datos del usuario autenticado de la base de datos.
        $user = DB::table('users')
                    ->select('name', 'nickname', 'biografia', 'avatar')
                    ->where('id', $user->id)
                    ->first();

        //Si el usuario no se encuentra en la base de datos se envia un mensaje
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        //Busca al avatar del usuario y si no tiene le pone uno por default
        $user->avatar = $user->avatar
        ? url('storage/' . $user->avatar)
        : url('/storage/images/user.png');


        //Se envia los datos a la consola
        return response()->json($user);
    }

    //Esta funcion sirve para cambiar el avatar
    public function updateAvatar(Request $request)
    {
        //Se autentica el usuario
        $user = $request->user();

        //Mensaje de error si el usuario no esta autenticado.
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        //Se busca el avatar actual y se guarda el nuevo
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $path = $file->store('images', 'public'); // Guarda en 'storage/app/public/images'

            // Actualiza la ruta del avatar en la base de datos
            // Aquí utilizamos $user, que ya es una instancia de User
            $user->avatar = $path;

            // Guardamos los cambios en la base de datos
            $user->save();

            //Se envia un mensaje a la consola
            return response()->json(['message' => 'Avatar actualizado correctamente']);
        }

        //Se envia un mensaje de error a la consola
        return response()->json(['message' => 'No se acutalizo el avatar'], 400);
    }
}
