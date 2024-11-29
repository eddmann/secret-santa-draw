<?php

namespace App\Http\Controllers;

class BootstrapController
{
    public function index()
    {
        if (! auth()->check()) {
            return response()->json([
                '_links' => [
                    'register' => [
                        'href' => route('register'),
                    ],
                    'login' => [
                        'href' => route('login'),
                    ],
                ],
            ]);
        }

        return response()->json([
            '_links' => [
                'logout' => [
                    'href' => route('logout'),
                ],
                'groups' => [
                    'href' => route('groups'),
                ],
            ],
            'user' => auth()->user()->toArray(),
        ]);
    }
}
