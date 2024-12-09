<?php

use App\Models\User;

use function Pest\Laravel\assertAuthenticatedAs;

test('authenticates a given user with valid credentials', function () {
    $user = User::factory()->createOne();

    $response = $this->post('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    assertAuthenticatedAs($user);
    $response->assertJson($user->toArray());
});

test('fails to authenticate a given user with invalid password', function () {
    $user = User::factory()->createOne();

    $response = $this->post('/api/login', [
        'email' => $user->email,
        'password' => 'invalid-password',
    ]);

    $response->assertStatus(401);
});

test('fails to authenticate an unknown email address', function () {
    $response = $this->post('/api/login', [
        'email' => 'unknown@user.com',
        'password' => 'password',
    ]);

    $response->assertStatus(401);
});
