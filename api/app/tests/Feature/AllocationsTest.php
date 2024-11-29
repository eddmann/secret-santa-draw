<?php

use App\Models\User;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNull;

test('list allocations for a draw as the owner', function () {
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

    $response = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);

    $response->assertOk();
    assertCount(4, $response['_embedded']['allocations']);
    assertEquals(4, $response['total']);
});

test('associates previous draw allocations when user email is registered', function () {
    $this->markTestSkipped();

    $owner = User::factory()->createOne();

    $group = $this
        ->actingAs($owner)
        ->post('/api/groups', [
            'title' => 'Test Title',
        ]);

    $draw = $this
        ->post($group['_links']['conduct-draw']['href'], [
            'description' => 'Sample description',
            'participants' => $this->participants(),
        ]);
    $drawAllocations = $draw['_embedded']['allocations']['_embedded']['allocations'];

    $participantName = $this->participants()[0]['name'];

    $fromAllocation = collect($drawAllocations)->firstWhere('from.name', $participantName);
    $toAllocation = collect($drawAllocations)->firstWhere('to.name', $participantName);
    assertNull($fromAllocation['from']['id']);
    assertNull($toAllocation['to']['id']);

    $this->post('/api/register', [
        'name' => 'Test User',
        'email' => $participantName,
        'password' => 'password',
    ]);

    $updatedDraw = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);
    $updatedDrawAllocations = $updatedDraw['_embedded']['allocations']['_embedded']['allocations'];

    $fromAllocation = collect($updatedDrawAllocations)->firstWhere('from.name', $participantName);
    $toAllocation = collect($updatedDrawAllocations)->firstWhere('to.name', $participantName);
    $participant = User::findByEmail($participantName);
    assertEquals($participant->id, $fromAllocation['from']['id']);
    assertEquals($participant->id, $toAllocation['to']['id']);
});

test('show an allocation as the owner', function () {
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

    $allocations = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);

    $response = $this
        ->actingAs($owner)
        ->get($allocations['_embedded']['allocations'][0]['_links']['self']['href']);

    $response->assertOk();
    assertArrayHasKey('provide-ideas', $response['_links']);
});

test('show an allocation as the authenticated user', function () {
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

    $allocations = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);

    $response = $this
        ->actingAs($participant)
        ->get($allocations['_embedded']['allocations'][0]['_links']['self']['href']);

    $response->assertOk();
    assertArrayHasKey('provide-ideas', $response['_links']);
    $response->assertJson(['from' => ['name' => $this->participants()[0]['name']]]);
});

test('show an allocation using the access token', function () {
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

    $allocations = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);

    auth()->forgetGuards();

    $response = $this
        ->get($allocations['_embedded']['allocations'][0]['_links']['self']['href'], [
            'X-Access-Token' => $allocations['_embedded']['allocations'][0]['from']['access_token'],
        ]);

    $response->assertOk();
    assertArrayHasKey('provide-ideas', $response['_links']);
    $response->assertJson(['from' => ['name' => $this->participants()[0]['name']]]);
});

test('fails to show an allocation as a guest', function () {
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

    $allocations = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);

    auth()->forgetGuards();

    $response = $this
        ->get($allocations['_embedded']['allocations'][0]['_links']['self']['href']);

    $response->assertForbidden();
});

test('fails to show another users allocation as an authenticated user', function () {
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

    $allocations = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);

    $anotherUser = User::factory()->createOne();

    $response = $this
        ->actingAs($anotherUser)
        ->get($allocations['_embedded']['allocations'][0]['_links']['self']['href']);

    $response->assertForbidden();
});

test('provide ideas as the group owner', function () {
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

    $allocations = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);

    $this
        ->actingAs($owner)
        ->put(
            $allocations['_embedded']['allocations'][0]['_links']['provide-ideas']['href'],
            ['ideas' => $idea = 'Sample idea']
        );

    $fromResponse = $this
        ->actingAs($owner)
        ->get($allocations['_embedded']['allocations'][0]['_links']['self']['href']);
    $fromResponse->assertJson(['from' => ['ideas' => $idea]]);

    $toAllocation = collect($allocations['_embedded']['allocations'])->firstWhere('to.name', $allocations['_embedded']['allocations'][0]['from']['name']);
    $toResponse = $this
        ->actingAs($owner)
        ->get($toAllocation['_links']['self']['href']);
    $toResponse->assertJson(['to' => ['ideas' => $idea]]);
});

test('provide ideas as the authenticated user', function () {
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

    $allocations = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);

    $this
        ->actingAs($participant)
        ->put(
            $allocations['_embedded']['allocations'][0]['_links']['provide-ideas']['href'],
            ['ideas' => $idea = 'Sample idea']
        );

    $fromResponse = $this
        ->actingAs($participant)
        ->get($allocations['_embedded']['allocations'][0]['_links']['self']['href']);
    $fromResponse->assertJson(['from' => ['ideas' => $idea]]);

    $toAllocation = collect($allocations['_embedded']['allocations'])->firstWhere('to.name', $allocations['_embedded']['allocations'][0]['from']['name']);
    $toResponse = $this
        ->actingAs($owner)
        ->get($toAllocation['_links']['self']['href']);
    $toResponse->assertJson(['to' => ['ideas' => $idea]]);
});

test('provide ideas using an access token', function () {
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

    $allocations = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);

    auth()->forgetGuards();

    $this
        ->put(
            $allocations['_embedded']['allocations'][0]['_links']['provide-ideas']['href'],
            ['ideas' => $idea = 'Sample idea'],
            ['X-Access-Token' => $allocations['_embedded']['allocations'][0]['from']['access_token']]
        );

    $fromResponse = $this
        ->actingAs($participant)
        ->get($allocations['_embedded']['allocations'][0]['_links']['self']['href']);
    $fromResponse->assertJson(['from' => ['ideas' => $idea]]);

    $toAllocation = collect($allocations['_embedded']['allocations'])->firstWhere('to.name', $allocations['_embedded']['allocations'][0]['from']['name']);
    $toResponse = $this
        ->actingAs($owner)
        ->get($toAllocation['_links']['self']['href']);
    $toResponse->assertJson(['to' => ['ideas' => $idea]]);
});

test('fails to provide ideas as a guest', function () {
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

    $allocations = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);

    auth()->forgetGuards();

    $response = $this
        ->put(
            $allocations['_embedded']['allocations'][0]['_links']['provide-ideas']['href'],
            ['ideas' => 'Sample idea'],
        );

    $response->assertForbidden();
});

test('fails to provide ideas using a different authenticated user', function () {
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

    $allocations = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);

    $anotherUser = User::factory()->createOne();

    $response = $this
        ->actingAs($anotherUser)
        ->put(
            $allocations['_embedded']['allocations'][0]['_links']['provide-ideas']['href'],
            ['ideas' => 'Sample idea'],
        );

    $response->assertForbidden();
});
