<?php

use App\Mail\AllocationMessageReceived;
use App\Models\Allocation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('send a message to recipient as authenticated Secret Santa user', function () {
    Mail::fake();
    $owner = User::factory()->createOne();

    $santa = User::create([
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

    $allocation = collect($allocations['_embedded']['allocations'])
        ->firstWhere('from.name', $santa->name);

    $response = $this
        ->actingAs($santa)
        ->post($allocation['_links']['messages-to-recipient']['href'], [
            'message' => 'Hey! What would you like for Secret Santa?',
        ]);

    $response->assertCreated();
    $response->assertJson([
        'message' => 'Hey! What would you like for Secret Santa?',
        'is_from_me' => true,
    ]);

    $santaAllocation = Allocation::where('from_email', $santa->email)->first();
    $recipientEmailAddress = $santaAllocation->to_email;
    $recipientName = Allocation::where('from_email', $recipientEmailAddress)->value('from_name');
    $recipientToken = Allocation::where('from_email', $recipientEmailAddress)->value('from_access_token');

    Mail::assertQueued(AllocationMessageReceived::class, function ($mail) use ($recipientEmailAddress, $recipientName, $recipientToken) {
        $content = $mail->content();
        $emailText = $content->htmlString;

        return $mail->hasTo($recipientEmailAddress) &&
               str_contains($emailText, "Hey {$recipientName},") &&
               str_contains($emailText, "token={$recipientToken}");
    });
});

test('send a message to Secret Santa as authenticated recipient user', function () {
    Mail::fake();
    $owner = User::factory()->createOne();

    $recipient = User::create([
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

    $allocation = collect($allocations['_embedded']['allocations'])
        ->firstWhere('from.name', $recipient->name);

    $response = $this
        ->actingAs($recipient)
        ->post($allocation['_links']['messages-from-santa']['href'], [
            'message' => 'I would love some books!',
        ]);

    $response->assertCreated();
    $response->assertJson([
        'message' => 'I would love some books!',
        'is_from_me' => true,
    ]);

    $santaAllocation = Allocation::where('to_email', $recipient->email)->first();
    $secretSantaEmailAddress = $santaAllocation->from_email;
    $secretSantaName = $santaAllocation->from_name;
    $secretSantaToken = $santaAllocation->from_access_token;

    Mail::assertQueued(AllocationMessageReceived::class, function ($mail) use ($secretSantaEmailAddress, $secretSantaName, $secretSantaToken) {
        $content = $mail->content();
        $emailText = $content->htmlString;

        return $mail->hasTo($secretSantaEmailAddress) &&
               str_contains($emailText, "Hey {$secretSantaName},") &&
               str_contains($emailText, "token={$secretSantaToken}");
    });
});

test('send a message to recipient as Secret Santa using access token', function () {
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

    $allocation = $allocations['_embedded']['allocations'][0];
    $secretSantaAccessToken = $allocation['from']['access_token'];

    auth()->forgetGuards();

    $response = $this
        ->post(
            $allocation['_links']['messages-to-recipient']['href'],
            ['message' => 'Hey! What would you like for Secret Santa?'],
            ['X-Access-Token' => $secretSantaAccessToken]
        );

    $response->assertCreated();
    $response->assertJson([
        'message' => 'Hey! What would you like for Secret Santa?',
        'is_from_me' => true,
    ]);

    $santaAllocation = Allocation::where('from_access_token', $secretSantaAccessToken)->first();
    $recipientEmailAddress = $santaAllocation->to_email;
    $recipientName = Allocation::where('from_email', $recipientEmailAddress)->value('from_name');
    $recipientToken = Allocation::where('from_email', $recipientEmailAddress)->value('from_access_token');

    Mail::assertQueued(AllocationMessageReceived::class, function ($mail) use ($recipientEmailAddress, $recipientName, $recipientToken) {
        $content = $mail->content();
        $emailText = $content->htmlString;

        return $mail->hasTo($recipientEmailAddress) &&
               str_contains($emailText, "Hey {$recipientName},") &&
               str_contains($emailText, "token={$recipientToken}");
    });
});

test('send a message to Secret Santa as recipient using access token', function () {
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

    $allocation = $allocations['_embedded']['allocations'][0];
    $recipientAccessToken = $allocation['from']['access_token'];

    auth()->forgetGuards();

    $response = $this
        ->post(
            $allocation['_links']['messages-from-santa']['href'],
            ['message' => 'Looking forward to getting you something nice!'],
            ['X-Access-Token' => $recipientAccessToken]
        );

    $response->assertCreated();
    $response->assertJson([
        'message' => 'Looking forward to getting you something nice!',
        'is_from_me' => true,
    ]);

    $recipientAllocation = Allocation::where('from_access_token', $recipientAccessToken)->first();
    $santaAllocation = Allocation::where('to_email', $recipientAllocation->from_email)->first();
    $secretSantaEmailAddress = $santaAllocation->from_email;
    $secretSantaName = $santaAllocation->from_name;
    $secretSantaToken = $santaAllocation->from_access_token;

    Mail::assertQueued(AllocationMessageReceived::class, function ($mail) use ($secretSantaEmailAddress, $secretSantaName, $secretSantaToken) {
        $content = $mail->content();
        $emailText = $content->htmlString;

        return $mail->hasTo($secretSantaEmailAddress) &&
               str_contains($emailText, "Hey {$secretSantaName},") &&
               str_contains($emailText, "token={$secretSantaToken}");
    });
});

test('show individual message as Secret Santa', function () {
    $owner = User::factory()->createOne();

    $santa = User::create([
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

    $allocation = collect($allocations['_embedded']['allocations'])
        ->firstWhere('from.name', $santa->name);

    $messageResponse = $this
        ->actingAs($santa)
        ->post($allocation['_links']['messages-to-recipient']['href'], [
            'message' => 'Test message',
        ]);

    $response = $this
        ->actingAs($santa)
        ->get($messageResponse['_links']['self']['href']);

    $response->assertOk();
    $response->assertJson([
        'message' => 'Test message',
        'is_from_me' => true,
    ]);
});

test('show individual message as recipient', function () {
    $owner = User::factory()->createOne();

    $recipient = User::create([
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

    $allocation = collect($allocations['_embedded']['allocations'])
        ->firstWhere('from.name', $recipient->name);

    $messageResponse = $this
        ->actingAs($recipient)
        ->post($allocation['_links']['messages-from-santa']['href'], [
            'message' => 'Test message',
        ]);

    $response = $this
        ->actingAs($recipient)
        ->get($messageResponse['_links']['self']['href']);

    $response->assertOk();
    $response->assertJson([
        'message' => 'Test message',
        'is_from_me' => true,
    ]);
});

test('show conversation as recipient', function () {
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
    $santaAllocation = collect($allocations['_embedded']['allocations'])
        ->firstWhere('to.name', $recipientAllocation['from']['name']);

    $recipientAccessToken = $recipientAllocation['from']['access_token'];
    $santaAccessToken = $santaAllocation['from']['access_token'];

    auth()->forgetGuards();

    $this
        ->post(
            $santaAllocation['_links']['messages-to-recipient']['href'],
            ['message' => 'Hey! What would you like for Secret Santa?'],
            ['X-Access-Token' => $santaAccessToken]
        );

    $this
        ->post(
            $recipientAllocation['_links']['messages-from-santa']['href'],
            ['message' => 'I would love some books!'],
            ['X-Access-Token' => $recipientAccessToken]
        );

    $this
        ->post(
            $santaAllocation['_links']['messages-to-recipient']['href'],
            ['message' => 'Great! Any particular genre?'],
            ['X-Access-Token' => $santaAccessToken]
        );

    $response = $this
        ->get(
            $recipientAllocation['_links']['messages-from-santa']['href'],
            ['X-Access-Token' => $recipientAccessToken]
        );

    $response->assertOk();
    $response->assertJson([
        'conversation_type' => 'from-santa',
        'participant_name' => 'Your Secret Santa',
        'total' => 3,
    ]);

    expect($response['_embedded']['messages'])->toHaveCount(3);
    expect($response['_embedded']['messages'][0]['message'])->toBe('Great! Any particular genre?');
    expect($response['_embedded']['messages'][0]['is_from_me'])->toBe(false);
    expect($response['_embedded']['messages'][1]['message'])->toBe('I would love some books!');
    expect($response['_embedded']['messages'][1]['is_from_me'])->toBe(true);
    expect($response['_embedded']['messages'][2]['message'])->toBe('Hey! What would you like for Secret Santa?');
    expect($response['_embedded']['messages'][2]['is_from_me'])->toBe(false);
});

test('show conversation as Secret Santa', function () {
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

    $santaAllocation = $allocations['_embedded']['allocations'][0];
    $recipientAllocation = collect($allocations['_embedded']['allocations'])
        ->firstWhere('from.name', $santaAllocation['to']['name']);

    $santaAccessToken = $santaAllocation['from']['access_token'];
    $recipientAccessToken = $recipientAllocation['from']['access_token'];

    auth()->forgetGuards();

    $this
        ->post(
            $santaAllocation['_links']['messages-to-recipient']['href'],
            ['message' => 'Hey! What would you like for Secret Santa?'],
            ['X-Access-Token' => $santaAccessToken]
        );

    $this
        ->post(
            $recipientAllocation['_links']['messages-from-santa']['href'],
            ['message' => 'I would love some books!'],
            ['X-Access-Token' => $recipientAccessToken]
        );

    $this
        ->post(
            $santaAllocation['_links']['messages-to-recipient']['href'],
            ['message' => 'Great! Any particular genre?'],
            ['X-Access-Token' => $santaAccessToken]
        );

    $response = $this
        ->get(
            $santaAllocation['_links']['messages-to-recipient']['href'],
            ['X-Access-Token' => $santaAccessToken]
        );

    $response->assertOk();
    $response->assertJson([
        'conversation_type' => 'to-recipient',
        'participant_name' => $santaAllocation['to']['name'],
        'total' => 3,
    ]);

    expect($response['_embedded']['messages'])->toHaveCount(3);
    expect($response['_embedded']['messages'][0]['message'])->toBe('Great! Any particular genre?');
    expect($response['_embedded']['messages'][0]['is_from_me'])->toBe(true);
    expect($response['_embedded']['messages'][1]['message'])->toBe('I would love some books!');
    expect($response['_embedded']['messages'][1]['is_from_me'])->toBe(false);
    expect($response['_embedded']['messages'][2]['message'])->toBe('Hey! What would you like for Secret Santa?');
    expect($response['_embedded']['messages'][2]['is_from_me'])->toBe(true);
});

test('fails to send message as a guest', function () {
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

    $allocation = $allocations['_embedded']['allocations'][0];

    auth()->forgetGuards();

    $response = $this
        ->post($allocation['_links']['messages-to-recipient']['href'], [
            'message' => 'Test message',
        ]);

    $response->assertForbidden();
});

test('fails to send message as a different authenticated user', function () {
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

    $allocation = $allocations['_embedded']['allocations'][0];
    $differentUser = User::factory()->createOne();

    $response = $this
        ->actingAs($differentUser)
        ->post($allocation['_links']['messages-to-recipient']['href'], [
            'message' => 'Test message',
        ]);

    $response->assertForbidden();
});

test('fails to send message as a different access token', function () {
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

    $allocation = $allocations['_embedded']['allocations'][0];
    $differentAllocation = $allocations['_embedded']['allocations'][1];
    $wrongAccessToken = $differentAllocation['from']['access_token'];

    auth()->forgetGuards();

    $response = $this
        ->post(
            $allocation['_links']['messages-to-recipient']['href'],
            ['message' => 'Test message'],
            ['X-Access-Token' => $wrongAccessToken]
        );

    $response->assertForbidden();
});

test('fails to send message with empty message field', function () {
    $owner = User::factory()->createOne();

    $santa = User::create([
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

    $santaAllocation = collect($allocations['_embedded']['allocations'])
        ->firstWhere('from.name', $santa->name);

    $response = $this
        ->actingAs($santa)
        ->post($santaAllocation['_links']['messages-to-recipient']['href'], [
            'message' => '',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('message');
});

test('fails when message exceeds 1000 characters', function () {
    $owner = User::factory()->createOne();

    $santa = User::create([
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

    $santaAllocation = collect($allocations['_embedded']['allocations'])
        ->firstWhere('from.name', $santa->name);

    $response = $this
        ->actingAs($santa)
        ->post($santaAllocation['_links']['messages-to-recipient']['href'], [
            'message' => str_repeat('a', 1001),
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('message');
});

test('fails to show conversation as an unrelated authenticated user', function () {
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

    $allocation = $allocations['_embedded']['allocations'][0];
    $unrelatedUser = User::factory()->createOne();

    $response = $this
        ->actingAs($unrelatedUser)
        ->get($allocation['_links']['messages-to-recipient']['href']);

    $response->assertForbidden();
});

test('fails to show conversation as a guest without token', function () {
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

    $allocation = $allocations['_embedded']['allocations'][0];

    auth()->forgetGuards();

    $response = $this
        ->get($allocation['_links']['messages-to-recipient']['href']);

    $response->assertForbidden();
});
