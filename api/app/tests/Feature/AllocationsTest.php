<?php

use App\Models\Allocation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

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

    $allocationsResponse = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);
    $drawAllocations = $allocationsResponse['_embedded']['allocations'];

    $participantName = $this->participants()[0]['name'];
    $participantEmail = $this->participants()[0]['email'];

    $fromAllocation = collect($drawAllocations)->firstWhere('from.name', $participantName);
    $toAllocation = collect($drawAllocations)->firstWhere('to.name', $participantName);
    assertNull($fromAllocation['from']['id']);
    assertNull($toAllocation['to']['id']);

    $this->post('/api/register', [
        'name' => 'Test User',
        'email' => $participantEmail,
        'password' => 'password',
    ]);

    $updatedAllocationsResponse = $this
        ->actingAs($owner)
        ->get($draw['_links']['allocations']['href']);
    $updatedAllocationsResponse->assertOk();
    $updatedDrawAllocations = $updatedAllocationsResponse['_embedded']['allocations'];

    $fromAllocation = collect($updatedDrawAllocations)->firstWhere('from.name', $participantName);
    $toAllocation = collect($updatedDrawAllocations)->firstWhere('to.name', $participantName);
    $participant = User::findByEmail($participantEmail);
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
            ['ideas' => $ideas = ['Sample idea 1', 'Sample idea 2', 'https://example.com/gift']]
        );

    $fromResponse = $this
        ->actingAs($owner)
        ->get($allocations['_embedded']['allocations'][0]['_links']['self']['href']);
    $fromResponse->assertJson(['from' => ['ideas' => $ideas]]);

    $toAllocation = collect($allocations['_embedded']['allocations'])->firstWhere('to.name', $allocations['_embedded']['allocations'][0]['from']['name']);
    $toResponse = $this
        ->actingAs($owner)
        ->get($toAllocation['_links']['self']['href']);
    $toResponse->assertJson(['to' => ['ideas' => $ideas]]);
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
            ['ideas' => $ideas = ['Sample idea']]
        );

    $fromResponse = $this
        ->actingAs($participant)
        ->get($allocations['_embedded']['allocations'][0]['_links']['self']['href']);
    $fromResponse->assertJson(['from' => ['ideas' => $ideas]]);

    $toAllocation = collect($allocations['_embedded']['allocations'])->firstWhere('to.name', $allocations['_embedded']['allocations'][0]['from']['name']);
    $toResponse = $this
        ->actingAs($owner)
        ->get($toAllocation['_links']['self']['href']);
    $toResponse->assertJson(['to' => ['ideas' => $ideas]]);
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
            ['ideas' => $ideas = ['Sample idea']],
            ['X-Access-Token' => $allocations['_embedded']['allocations'][0]['from']['access_token']]
        );

    $fromResponse = $this
        ->actingAs($participant)
        ->get($allocations['_embedded']['allocations'][0]['_links']['self']['href']);
    $fromResponse->assertJson(['from' => ['ideas' => $ideas]]);

    $toAllocation = collect($allocations['_embedded']['allocations'])->firstWhere('to.name', $allocations['_embedded']['allocations'][0]['from']['name']);
    $toResponse = $this
        ->actingAs($owner)
        ->get($toAllocation['_links']['self']['href']);
    $toResponse->assertJson(['to' => ['ideas' => $ideas]]);
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
            ['ideas' => ['Sample idea']],
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
            ['ideas' => ['Sample idea']],
        );

    $response->assertForbidden();
});

test('can provide an empty array of ideas', function () {
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
        ->put(
            $allocations['_embedded']['allocations'][0]['_links']['provide-ideas']['href'],
            ['ideas' => []]
        );

    $response->assertAccepted();

    $fromResponse = $this
        ->actingAs($owner)
        ->get($allocations['_embedded']['allocations'][0]['_links']['self']['href']);
    $fromResponse->assertJson(['from' => ['ideas' => []]]);
});

test('can provide ideas without the ideas field', function () {
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
        ->put(
            $allocations['_embedded']['allocations'][0]['_links']['provide-ideas']['href'],
            []
        );

    $response->assertAccepted();

    $fromResponse = $this
        ->actingAs($owner)
        ->get($allocations['_embedded']['allocations'][0]['_links']['self']['href']);
    $fromResponse->assertJson(['from' => ['ideas' => []]]);
});

test('fails to provide more than 5 ideas', function () {
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
        ->put(
            $allocations['_embedded']['allocations'][0]['_links']['provide-ideas']['href'],
            ['ideas' => [
                'Idea 1',
                'Idea 2',
                'Idea 3',
                'Idea 4',
                'Idea 5',
                'Idea 6',  // Exceeds max of 5
            ]]
        );

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('ideas');
});

test('fails when individual idea exceeds 500 characters', function () {
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
        ->put(
            $allocations['_embedded']['allocations'][0]['_links']['provide-ideas']['href'],
            ['ideas' => [
                str_repeat('a', 501),  // Exceeds max of 500 characters
            ]]
        );

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('ideas.0');
});

test('sends email to Secret Santa when recipient provides gift ideas', function () {
    Mail::fake();
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

    $recipientAllocation = $allocations['_embedded']['allocations'][0];
    $recipientAccessToken = $recipientAllocation['from']['access_token'];
    $recipientName = $recipientAllocation['from']['name'];

    auth()->forgetGuards();

    $this
        ->put(
            $recipientAllocation['_links']['provide-ideas']['href'],
            ['ideas' => $ideas = ['Sample idea 1', 'Sample idea 2']],
            ['X-Access-Token' => $recipientAccessToken]
        );

    $recipientAllocationModel = Allocation::where('from_name', $recipientName)->first();
    $recipientEmail = $recipientAllocationModel->from_email;

    $santaAllocation = $recipientAllocationModel->secretSanta;
    $santaEmail = $santaAllocation->from_email;
    $santaName = $santaAllocation->from_name;
    $santaToken = $santaAllocation->from_access_token;

    Mail::assertQueued(\App\Mail\AllocationGiftIdeasProvided::class, function ($mail) use ($santaEmail, $santaName, $recipientName, $santaToken) {
        $content = $mail->content();
        $emailText = $content->htmlString;

        return $mail->hasTo($santaEmail) &&
               str_contains($emailText, "Hey {$santaName},") &&
               str_contains($emailText, "{$recipientName} has updated their gift ideas") &&
               str_contains($emailText, "token={$santaToken}");
    });
});

test('sends email to Secret Santa when recipient updates gift ideas', function () {
    Mail::fake();
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

    $recipientAllocation = $allocations['_embedded']['allocations'][0];
    $recipientAccessToken = $recipientAllocation['from']['access_token'];

    auth()->forgetGuards();

    $this
        ->put(
            $recipientAllocation['_links']['provide-ideas']['href'],
            ['ideas' => ['Initial idea']],
            ['X-Access-Token' => $recipientAccessToken]
        );

    Mail::assertQueued(\App\Mail\AllocationGiftIdeasProvided::class, 1);

    $this
        ->put(
            $recipientAllocation['_links']['provide-ideas']['href'],
            ['ideas' => ['Updated idea 1', 'Updated idea 2']],
            ['X-Access-Token' => $recipientAccessToken]
        );

    Mail::assertQueued(\App\Mail\AllocationGiftIdeasProvided::class, 2);
});

test('sends email when recipient clears gift ideas', function () {
    Mail::fake();
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

    $recipientAllocation = $allocations['_embedded']['allocations'][0];
    $recipientAccessToken = $recipientAllocation['from']['access_token'];
    $recipientName = $recipientAllocation['from']['name'];

    auth()->forgetGuards();

    $this
        ->put(
            $recipientAllocation['_links']['provide-ideas']['href'],
            ['ideas' => []],
            ['X-Access-Token' => $recipientAccessToken]
        );

    $recipientAllocationModel = Allocation::where('from_name', $recipientName)->first();
    $recipientEmail = $recipientAllocationModel->from_email;

    $santaAllocation = $recipientAllocationModel->secretSanta;
    $santaEmail = $santaAllocation->from_email;

    Mail::assertQueued(\App\Mail\AllocationGiftIdeasProvided::class, function ($mail) use ($santaEmail) {
        $content = $mail->content();
        $emailText = $content->htmlString;

        return $mail->hasTo($santaEmail) &&
               str_contains($emailText, 'has updated their gift ideas');
    });
});
