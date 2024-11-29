<?php

namespace App\Http\Resources;

use App\Models\Draw;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AllocationCollection extends ResourceCollection
{
    public function __construct(private readonly Draw $draw, $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            '_links' => [
                'self' => [
                    'href' => route(
                        'allocations',
                        [
                            'group' => $this->draw->group->id,
                            'draw' => $this->draw->id,
                        ]
                    ),
                ],
                'allocations' => array_map(
                    fn ($allocation) => ['href' => $allocation->getSelfHref()],
                    iterator_to_array($this->collection)
                ),
            ],
            '_embedded' => [
                'allocations' => $this->collection,
            ],
            'total' => count($this->collection),
        ];
    }
}
