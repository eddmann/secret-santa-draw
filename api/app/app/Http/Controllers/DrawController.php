<?php

namespace App\Http\Controllers;

use App\Events\DrawConducted;
use App\Http\Resources\DrawCollection;
use App\Http\Resources\DrawResource;
use App\Models\Draw;
use App\Models\Group;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DrawController
{
    public function index(Request $request, Group $group)
    {
        if (! $group->isOwner($request->user())) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return new DrawCollection($group, $group->draws()->orderBy('year')->get());
    }

    public function create(Request $request, Group $group)
    {
        if (! $group->isOwner($request->user())) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'description' => 'required|string|max:2000',
            'participants' => 'required|array',
            'participants.*.name' => 'required|string|max:255',
            'participants.*.email' => 'required|email|distinct|max:255',
            'participants.*.exclusions' => 'array',
            'participants.*.exclusions.*' => 'required|email',
        ]);

        $draw = $group->draw((int) date('Y'), $request->description, $request->participants);

        event(new DrawConducted($draw));

        return response(new DrawResource($draw), status: Response::HTTP_CREATED);
    }

    public function show(Request $request, Group $group, Draw $draw)
    {
        $allocation = $draw->allocationFor(
            userId: auth()->id(),
            accessToken: $request->header('X-Access-Token')
        );

        if (! $group->isOwner($request->user()) && ! $allocation) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return new DrawResource($draw);
    }

    public function delete(Request $request, Group $group, Draw $draw)
    {
        if (! $group->isOwner($request->user())) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $draw->delete();

        return response(status: Response::HTTP_ACCEPTED);
    }
}
