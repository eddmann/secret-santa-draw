<?php

namespace App\Http\Resources;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class DrawCollection extends ResourceCollection
{
    public function __construct(private readonly Group $group, $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            '_links' => array_filter([
                'self' => [
                    'href' => $this->getSelfHref(),
                ],
                'conduct-draw' => $this->group->canConductDraw(Auth::id()) ? [
                    'href' => route('draws.create', ['group' => $this->group->id]),
                ] : null,
                'group' => [
                    'href' => route('groups.show', ['group' => $this->group->id]),
                ],
                'draws' => array_map(
                    fn ($draw) => ['href' => $draw->getSelfHref()],
                    iterator_to_array($this->collection)
                ),
            ]),
            '_embedded' => [
                'draws' => $this->collection,
            ],
            'total' => count($this->collection),
        ];
    }

    public function getSelfHref(): string
    {
        return route('draws', ['group' => $this->group->id]);
    }
}
