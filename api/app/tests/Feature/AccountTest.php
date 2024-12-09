
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

test('fails to show account details for guest session', function () {
    $response = $this->get('/api/account');

    $response->assertStatus(401);
});

test('shows account details for authenticated session', function () {
    $user = User::factory()->createOne();

    $response = $this
        ->actingAs($user)
        ->get('/api/account');

    $response->assertJson($user->toArray());
});

test('deletes an authenticated users account', function () {
    $user = User::factory()->createOne();
    $group = $this
        ->actingAs($user)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);
    $this
        ->actingAs($user)
        ->post($group['_links']['conduct-draw']['href'], [
            'description' => 'Sample description',
            'participants' => $this->participants(),
        ]);

    $response = $this
        ->actingAs($user)
        ->delete('/api/account');

    $response->assertAccepted();
    auth()->forgetGuards();
    $this->get('/api/account')->assertStatus(401);
});
