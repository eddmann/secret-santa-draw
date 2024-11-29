<?php

use App\Models\User;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

test('create new group', function () {
    $user = User::factory()->createOne();

    $response = $this
        ->actingAs($user)
        ->post('/api/groups', $group = [
            'title' => 'Test Title',
        ]);

    $response->assertCreated();
    $response->assertJson($group);
});

test('list owners groups', function () {
    $user = User::factory()->createOne();

    $this
        ->actingAs($user)
        ->post('/api/groups', [
            'title' => 'Test Group One',
        ]);

    $this
        ->actingAs($user)
        ->post('/api/groups', [
            'title' => 'Test Group Two',
        ]);

    $response = $this
        ->actingAs($user)
        ->get('/api/groups');

    $response->assertOk();
    assertCount(2, $response['_embedded']['groups']);
    assertEquals(2, $response['total']);
});

test('fails to create a group with missing title', function () {
    $user = User::factory()->createOne();

    $response = $this
        ->actingAs($user)
        ->post('/api/groups', [
            'title' => '',
        ]);

    $response->assertUnprocessable();
});

test('update a group as the owner', function () {
    $user = User::factory()->createOne();

    $group = $this
        ->actingAs($user)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $response = $this
        ->actingAs($user)
        ->put($group['_links']['update-group']['href'], $updatedGroup = [
            'title' => 'Updated Title',
        ]);

    $response->assertAccepted();
    $response->assertJson($updatedGroup);
});

test('fails to update a group owned by another user', function () {
    $owner = User::factory()->createOne();

    $group = $this
        ->actingAs($owner)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $nonOwner = User::factory()->createOne();

    $response = $this
        ->actingAs($nonOwner)
        ->put($group['_links']['update-group']['href'], [
            'title' => 'Updated Title',
        ]);

    $response->assertForbidden();
});

test('fails to update a group with missing title', function () {
    $owner = User::factory()->createOne();

    $group = $this
        ->actingAs($owner)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $response = $this
        ->actingAs($owner)
        ->put($group['_links']['update-group']['href'], [
            'title' => '',
        ]);

    $response->assertUnprocessable();
});

test('show a group to the owner', function () {
    $user = User::factory()->createOne();

    $group = $this
        ->actingAs($user)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $response = $this
        ->actingAs($user)
        ->get($group['_links']['self']['href']);

    $response->assertOk();
});

test('fails to show guest a group', function () {
    $user = User::factory()->createOne();

    $group = $this
        ->actingAs($user)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    auth()->forgetGuards();

    $response = $this->get($group['_links']['self']['href']);

    $response->assertForbidden();
});

test('fails to show group owned by another user', function () {
    $owner = User::factory()->createOne();

    $group = $this
        ->actingAs($owner)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $nonOwner = User::factory()->createOne();

    $response = $this
        ->actingAs($nonOwner)
        ->get($group['_links']['self']['href']);

    $response->assertForbidden();
});

test('includes related draws within group', function () {
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
        ->get($group['_links']['self']['href']);

    assertArrayHasKey('draws', $response['_links']);
    assertArrayHasKey('draws', $response['_embedded']);
    assertCount(1, $response['_embedded']['draws']['_links']['draws']);
    assertCount(1, $response['_embedded']['draws']['_embedded']['draws']);
});
