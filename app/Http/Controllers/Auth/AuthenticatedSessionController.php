<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */

     public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = $request->user();//Auth::user();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json(['message' => 'Login successful', 'token' => $token, 'user'=>$user]);
    }



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

    public function logout(){

        Auth::logout();

        return redirec('/login')->whit('status', 'Sesion cerrada correctamente');

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        //Auth::guard('web')->logout();

        //$request->session()->invalidate();

        //$request->session()->regenerateToken();

        //return response()->noContent();
    }
}
