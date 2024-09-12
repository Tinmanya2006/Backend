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
            'password' => bcrypt($datosValidados['password']),
        ]);

        return response()->json("Usuario creado", 200);
    }


    //Esta funcion actualiza los datos del usuario, se debe probar.
    public function update(Request $request)
    {
        //Esto busca al usuario por su id para poder actualizar sus datos.
        $user = $request->user();

        if (!$user) {
            return response()->json(['mensaje' => 'Usuario no autenticado'], 401);
        }

        //Esto valida los datos que llegan del Frontend.
        $datosValidados = $request->validate(
            [
                'nickname' => 'sometimes|unique:users|max:20',
                'biografia' => 'sometimes|max:120',
            ]
        );

        //Esto actualiza los datos del usuario, si los datos se validaron correctamente.
        if ($user) {
            $user->update([
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

    //Esta funcion elimina el usuario, se debe probar.
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

    //esta funcion sirve para cambiar la contraseña, se debe probar.
    public function cambiarContraseña(Request $request)
    {
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

        //Esto chequea si la contraseña actual es correcta.
        if (!Hash::check($datosValidados['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        //Esto cambia la contraseña.
        $user->password = Hash::make($datosValidados['newpassword']);

        //Esto guarda la contraseña.
        $user->save();

        //Esto muestra un mensaje si la contraseña se cambio correctamente.
        return response()->json(['message' => 'Contraseña cambiada correctamente.']);
    }

    public function show(Request $request)
    {
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }
    $user = DB::table('users')
                ->select('name', 'nickname', 'biografia', 'avatar')
                ->where('id', $user->id)
                ->get();

    return response()->json($user);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,bmp|max:2048',
        ]);

        $user = $request->user();//Auth::user();



        if ($user->avatar) {
            Storage::delete($user->avatar);
        }


        $path = $request->file('avatar')->store('avatars');

        // Actualizar la ruta del avatar en la base de datos
        $user->avatar = $path;
        $user->save();

        return response()->json([
            'message' => 'Avatar actualizado correctamente',
            'avatar' => Storage::url($path) // Devolver la URL de la imagen
        ], 200);
    }


}
