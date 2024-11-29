<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupCollection;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupController
{
    public function index(Request $request)
    {
        return new GroupCollection(auth()->user()->groups);
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $group = Group::create([
            'owner_id' => auth()->id(),
            'title' => $request->title,
        ]);

        return response(new GroupResource($group), status: Response::HTTP_CREATED);
    }

    public function show(Request $request, Group $group)
    {
        if (! $group->isOwner($request->user())) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return new GroupResource($group);
    }

    public function update(Request $request, Group $group)
    {
        if (! $group->isOwner($request->user())) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $group->update([
            'title' => $request->title,
        ]);

        return response(new GroupResource($group), status: Response::HTTP_ACCEPTED);
    }
}
