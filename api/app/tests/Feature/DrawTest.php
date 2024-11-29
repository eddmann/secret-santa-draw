<?php

use App\Mail\DrawConducted;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

test('conduct a draw as the owner of the group', function () {
    Mail::fake();

    $user = User::factory()->createOne();

    $group = $this
        ->actingAs($user)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $response = $this
        ->actingAs($user)
        ->post($group['_links']['conduct-draw']['href'], [
            'description' => 'Sample description',
            'participants' => $this->participants(),
        ]);

    $response->assertCreated();
    assertArrayHasKey('allocations', $response['_links']);
    assertArrayHasKey('allocations', $response['_embedded']);
    assertCount(4, $response['_embedded']['allocations']['_links']['allocations']);
    assertCount(4, $response['_embedded']['allocations']['_embedded']['allocations']);
    $response->assertJson([
        'year' => (int) date('Y'),
        'description' => 'Sample description',
    ]);
    Mail::assertQueued(DrawConducted::class, 4);
});

test('remove a draw', function () {
    $user = User::factory()->createOne();

    $group = $this
        ->actingAs($user)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $draw = $this
        ->actingAs($user)
        ->post($group['_links']['conduct-draw']['href'], [
            'description' => 'Sample description',
            'participants' => $this->participants(),
        ]);

    $response = $this->delete($draw['_links']['remove-draw']['href']);

    $response->assertAccepted();
    $this->get($draw['_links']['self']['href'])->assertNotFound();
});

test('list draws for group', function () {
    $user = User::factory()->createOne();

    $group = $this
        ->actingAs($user)
        ->post('/api/groups', [
            'title' => 'Test Group One',
        ]);

    $this
        ->actingAs($user)
        ->post($group['_links']['conduct-draw']['href'], [
            'description' => 'Sample description',
            'participants' => $this->participants(),
        ]);

    $response = $this
        ->actingAs($user)
        ->get($group['_links']['draws']['href']);

    $response->assertOk();
    assertCount(1, $response['_embedded']['draws']);
    assertEquals(1, $response['total']);
});

test('fails to conduct a draw for group owned by other user', function () {
    $owner = User::factory()->createOne();

    $group = $this
        ->actingAs($owner)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $nonOwner = User::factory()->createOne();

    $response = $this
        ->actingAs($nonOwner)
        ->post($group['_links']['conduct-draw']['href'], [
            'description' => 'Sample description',
            'participants' => $this->participants(),
        ]);

    $response->assertForbidden();
});

test('show a draw as the owner of the group', function () {
    $user = User::factory()->createOne();

    $group = $this
        ->actingAs($user)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $draw = $this
        ->actingAs($user)
        ->post($group['_links']['conduct-draw']['href'], [
            'description' => 'Sample description',
            'participants' => $this->participants(),
        ]);

    $response = $this
        ->actingAs($user)
        ->get($draw['_links']['self']['href']);

    $response->assertOk();
    assertArrayHasKey('allocations', $response['_links']);
    assertArrayHasKey('allocations', $response['_embedded']);
    assertCount(4, $response['_embedded']['allocations']['_links']['allocations']);
    assertCount(4, $response['_embedded']['allocations']['_embedded']['allocations']);
    assertEquals((int) date('Y'), $response['year']);
    assertEquals('Sample description', $response['description']);
});

test('show a draw as the allocated authenticated user', function () {
    $owner = User::factory()->createOne();

    $participant = User::create([
        'name' => $this->participants()[0]['name'],
        'email' => $this->participants()[0]['email'],
        'password' => 'password',
    ]);

    $group = $this
        ->actingAs($owner)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $draw = $this
        ->actingAs($owner)
        ->post($group['_links']['conduct-draw']['href'], [
            'description' => 'Sample description',
            'participants' => $this->participants(),
        ]);

    $response = $this
        ->actingAs($participant)
        ->get($draw['_links']['self']['href']);

    $response->assertOk();
    assertArrayNotHasKey('allocations', $response['_links']);
    assertArrayNotHasKey('allocations', $response['_embedded']);
    assertArrayHasKey('allocation', $response['_links']);
    assertArrayHasKey('allocation', $response['_embedded']);
    assertEquals((int) date('Y'), $response['year']);
    assertEquals('Sample description', $response['description']);
});

test('show a draw using the allocated access token', function () {
    $owner = User::factory()->createOne();

    $group = $this
        ->actingAs($owner)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $draw = $this
        ->actingAs($owner)
        ->post($group['_links']['conduct-draw']['href'], [
            'description' => 'Sample description',
            'participants' => $this->participants(),
        ]);

    auth()->forgetGuards();

    $participantAccessToken = $draw['_embedded']['allocations']['_embedded']['allocations'][0]['from']['access_token'];

    $response = $this
        ->get($draw['_links']['self']['href'], [
            'X-Access-Token' => $participantAccessToken,
        ]);

    $response->assertOk();
    assertArrayNotHasKey('allocations', $response['_links']);
    assertArrayNotHasKey('allocations', $response['_embedded']);
    assertArrayHasKey('allocation', $response['_links']);
    assertArrayHasKey('allocation', $response['_embedded']);
    assertEquals((int) date('Y'), $response['year']);
    assertEquals('Sample description', $response['description']);
});

test('show a draw as the owner and allocated authenticated user', function () {
    $owner = User::create([
        'name' => $this->participants()[0]['name'],
        'email' => $this->participants()[0]['email'],
        'password' => 'password',
    ]);

    $group = $this
        ->actingAs($owner)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $draw = $this
        ->actingAs($owner)
        ->post($group['_links']['conduct-draw']['href'], [
            'description' => 'Sample description',
            'participants' => $this->participants(),
        ]);

    $response = $this
        ->actingAs($owner)
        ->get($draw['_links']['self']['href']);

    $response->assertOk();
    assertArrayHasKey('allocations', $response['_links']);
    assertArrayHasKey('allocations', $response['_embedded']);
    assertArrayHasKey('allocation', $response['_links']);
    assertArrayHasKey('allocation', $response['_embedded']);
    assertEquals((int) date('Y'), $response['year']);
    assertEquals('Sample description', $response['description']);
});

test('fails to show a draw which the authenticated user is not allocated to', function () {
    $owner = User::factory()->createOne();

    $group = $this
        ->actingAs($owner)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $draw = $this
        ->actingAs($owner)
        ->post($group['_links']['conduct-draw']['href'], [
            'description' => 'Sample description',
            'participants' => $this->participants(),
        ]);

    $nonOwner = User::factory()->createOne();

    $response = $this
        ->actingAs($nonOwner)
        ->get($draw['_links']['self']['href']);

    $response->assertForbidden();
});

test('fails to show a draw for a guest', function () {
    $owner = User::factory()->createOne();

    $group = $this
        ->actingAs($owner)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $draw = $this
        ->actingAs($owner)
        ->post($group['_links']['conduct-draw']['href'], [
            'description' => 'Sample description',
            'participants' => $this->participants(),
        ]);

    auth()->forgetGuards();

    $response = $this
        ->get($draw['_links']['self']['href']);

    $response->assertForbidden();
});
