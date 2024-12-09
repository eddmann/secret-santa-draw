<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials)) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $request->session()->regenerate();

        return response()->json(auth()->user());
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response(status: Response::HTTP_ACCEPTED);
    }
}
