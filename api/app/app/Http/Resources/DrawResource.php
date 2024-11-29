<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class DrawResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isOwner = $this->group->owner_id === Auth::id();
        $allocation = ($allocation = $this->allocationFor(userId: Auth::id(), accessToken: $request->header('X-Access-Token')))
            ? new AllocationResource($allocation)
            : null;

        return [
            '_links' => array_filter([
                'self' => [
                    'href' => $this->getSelfHref(),
                ],
                'allocation' => $allocation ? [
                    'href' => $allocation->getSelfHref(),
                ] : null,
                'remove-draw' => $isOwner ? [
                    'href' => $this->getSelfHref(),
                ] : null,
                'draws' => $isOwner ? [
                    'href' => route('draws', ['group' => $this->group->id]),
                ] : null,
                'group' => $isOwner ? [
                    'href' => route('groups.show', ['group' => $this->group->id]),
                ] : null,
                'allocations' => $isOwner ? [
                    'href' => route(
                        'allocations',
                        [
                            'group' => $this->group->id,
                            'draw' => $this->id,
                        ]
                    ),
                ] : null,
            ]),
            '_embedded' => \array_filter([
                'allocation' => $allocation,
                'allocations' => $isOwner ?
                    new AllocationCollection($this->resource, $this->allocations)
                    : null,
            ]),
            'title' => "{$this->group->title} ({$this->year})",
            'year' => $this->year,
            'description' => $this->description,
        ];
    }

    public function getSelfHref(): string
    {
        return route(
            'draws.show',
            [
                'group' => $this->group->id,
                'draw' => $this->id,
            ]
        );
    }
}
