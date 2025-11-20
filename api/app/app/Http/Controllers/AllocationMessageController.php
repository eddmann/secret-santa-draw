<?php

namespace App\Http\Controllers;

use App\Events\AllocationMessageSent;
use App\Http\Resources\AllocationMessageCollection;
use App\Http\Resources\AllocationMessageResource;
use App\Models\Allocation;
use App\Models\AllocationMessage;
use App\Models\Draw;
use App\Models\Group;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllocationMessageController
{
    public function index(Request $request, Group $group, Draw $draw, Allocation $allocation)
    {
        if (! $allocation->canAccessMessages(auth()->id(), $request->header('X-Access-Token'))) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $isUserSecretSanta = $this->isUserSecretSanta($request, $allocation);

        $messages = $allocation
            ->messages()
            ->orderBy('created_at', 'DESC')
            ->get()
            ->map(fn ($message) => new AllocationMessageResource($isUserSecretSanta, $message))
            ->toArray();

        return new AllocationMessageCollection($allocation, $isUserSecretSanta, $messages);
    }

    public function create(Request $request, Group $group, Draw $draw, Allocation $allocation)
    {
        if (! $allocation->canAccessMessages(auth()->id(), $request->header('X-Access-Token'))) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $isUserSecretSanta = $this->isUserSecretSanta($request, $allocation);

        $message = AllocationMessage::create([
            'allocation_id' => $allocation->id,
            'is_from_secret_santa' => $isUserSecretSanta,
            'message' => $request->message,
        ]);

        AllocationMessageSent::dispatch($message);

        return response(new AllocationMessageResource($isUserSecretSanta, $message), status: Response::HTTP_CREATED);
    }

    public function show(Request $request, Group $group, Draw $draw, Allocation $allocation, AllocationMessage $message)
    {
        if (! $allocation->canAccessMessages(auth()->id(), $request->header('X-Access-Token'))) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $isUserSecretSanta = $this->isUserSecretSanta($request, $allocation);

        return new AllocationMessageResource($isUserSecretSanta, $message);
    }

    private function isUserSecretSanta(Request $request, Allocation $allocation): bool
    {
        if ($accessToken = $request->header('X-Access-Token')) {
            return $allocation->from_access_token === $accessToken;
        }

        $userId = auth()->id();

        return $userId !== null && $allocation->from_user_id === $userId;
    }
}
