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
                        'href' => route('account.register'),
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
                'delete-account' => [
                    'href' => route('account.delete'),
                ],
            ],
            'user' => auth()->user()->toArray(),
        ]);
    }
}
