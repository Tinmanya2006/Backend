<?php

namespace App\Http\Controllers;

use App\Models\Genera;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ControllerGenera extends Controller
{
    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'idusuario' => 'required|exists:users,id',
            'idnotas' => 'required|exists:notas,id',
        ]);

        $user = User::find($datosValidados['idusuario']);
        $notaId = $datosValidados['idnotas'];

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        $user->notas()->attach($notaId);

        $user = User::find($datosValidados['idusuario']);
        $notas = $user->notas;

        return response()->json([
            'message' => 'Nota relacionada exitosamente',
            'user' => $user,
            'notas' => $notas
        ], 201);
    }

}

