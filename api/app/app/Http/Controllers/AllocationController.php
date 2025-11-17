<?php

namespace App\Http\Controllers;

use App\Http\Resources\AllocationCollection;
use App\Http\Resources\AllocationResource;
use App\Models\Allocation;
use App\Models\Draw;
use App\Models\Group;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllocationController
{
    public function index(Request $request, Group $group, Draw $draw)
    {
        if (! $group->isOwner(auth()->user())) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return new AllocationCollection($draw, $draw->allocations()->get());
    }

    public function show(Request $request, Group $group, Draw $draw, Allocation $allocation)
    {
        if (! $allocation->canAccess(auth()->id(), $request->header('X-Access-Token'))) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return new AllocationResource($allocation);
    }

    public function provideIdeas(Request $request, Group $group, Draw $draw, Allocation $allocation)
    {
        if (! $allocation->canAccess(auth()->id(), $request->header('X-Access-Token'))) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'ideas' => 'nullable|array|max:5',
            'ideas.*' => 'string|max:500',
        ]);

        $allocation->update([
            'from_ideas' => $request->ideas ?? [],
        ]);

        return response(status: Response::HTTP_ACCEPTED);
    }
}
