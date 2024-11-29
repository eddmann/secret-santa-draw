
<?php

use App\Models\User;

use function Pest\Laravel\assertAuthenticatedAs;

test('registers a new participant', function () {
    $response = $this->post('/api/register', [
        'name' => 'Test Participant',
        'email' => $email = 'test@participant.com',
        'password' => 'password',
    ]);

    assertAuthenticatedAs(User::findByEmail($email));
    $response->assertCreated();
});

test('fails to register a new participant with existing email address', function () {
    $this->post('/api/register', $existingParticipant = [
        'name' => 'Test Participant',
        'email' => 'test@participant.com',
        'password' => 'password',
    ]);

    $response = $this->post('/api/register', $existingParticipant);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});

test('fails to register a new participant with missing properties', function () {
    $response = $this->post('/api/register');

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'email', 'password']);
});
