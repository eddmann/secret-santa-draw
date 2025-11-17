<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccountController
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => 'required',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->string('password'),
        ]);

        foreach (Allocation::where('from_email', $request->email)->get() as $participant) {
            $participant->update(['from_user_id' => $user->id]);
        }

        foreach (Allocation::where('to_email', $request->email)->get() as $participant) {
            $participant->update(['to_user_id' => $user->id]);
        }

        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        return response()->json(auth()->user(), status: Response::HTTP_CREATED);
    }

    public function show(Request $request)
    {
        return response()->json(auth()->user());
    }

    public function delete(Request $request)
    {
        $request->user()->delete();

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response(status: Response::HTTP_ACCEPTED);
    }
}
