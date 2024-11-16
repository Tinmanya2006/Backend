<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */

     //Esta funcion sirve para Iniciar Sesion
     public function login(Request $request)
    {
        //Se validan los datos enviados del frontend
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        //Se autentican los datos si son coinciden con alguno de la base de datos
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        //Se autentica el usuario
        $user = $request->user();

        //Esto crea un token para un usuario
        $token = $user->createToken('auth-token')->plainTextToken;

        //Se envia un mensaje a la consola
        return response()->json(['message' => 'Ha iniciado sesion correctamente', 'token' => $token, 'user'=>$user]);
    }


    /*
    public function store(LoginRequest $request): Response
    {
        //$request->authenticate();

        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return response()->json(['message' => 'Login successful']);//->noContent();
    }
    */


    public function logout(Request $request)//: RedirectResponse
    {
    //Auth::logout();

    //$request->session()->invalidate();

    //$request->session()->regenerateToken();
    $sesiones_cerradas = $request->user()->tokens()->delete();
    //return redirect('/');
    return response()->json(['message'=>"logout",'sesiones_cerradas'=>"$sesiones_cerradas"]);
    }


    /**
     * Destroy an authenticated session.
     */
   /* public function destroy(Request $request): Response
    {
        //Auth::guard('web')->logout();

        //$request->session()->invalidate();

        //$request->session()->regenerateToken();

        //return response()->noContent();
    } */
}
