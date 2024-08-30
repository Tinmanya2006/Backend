<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ControllerUser extends Controller
{
    public function store(Request $request)
    {
        $datosValidados = $request->validate(
            [
                'name' => 'required|max:30',
                'nickname' => 'required|unique:users|max:20',
                'email' => 'required|unique:users|max:45',
                'password' => 'required|max:40',
            ]
        );

        User::create([
            'name' => $datosValidados['name'],
            'nickname' => $datosValidados['nickname'],
            'email' => $datosValidados['email'],
            'password' => bcrypt($datosValidados['password']),
        ]);

        return response()->json("Usuario creado", 200);
    }

    public function update(Request $request, $id)
    {

        $user = User::find($id);

        $datosValidados = $request->validate(
            [
                'nickname' => 'required|unique:users|max:20',
                'password' => 'required|max:40|confirmed',
            ]
        );

        if ($user) {
            $user->update([
                'nickname' => $datosValidados['nickname'],
                'password' => isset($datosValidados['password']) ? Hash::make($datosValidados['password']) : $user->password,
            ]);

            return response()->json(['messaje' => 'Se ha actualizado los datos del usuario']);
        } else {
            return response()->json(['messaje' => 'No se pudieron actualizar los datos del usuario'], 404);
        }
    }


    public function destroy(Request $request, $id)
    {
     if ($user) {
        $user = User::find($id);

        $user->delete();

        return response()->json('Usuario eliminado correctamente', 204);
     }  else {
        return response()->json('No se eliminó el usuario', 406);
     }
    }
}
