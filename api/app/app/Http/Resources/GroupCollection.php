<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GroupCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            '_links' => [
                'self' => [
                    'href' => route('groups'),
                ],
                'add-group' => [
                    'href' => route('groups'),
                ],
                'groups' => array_map(
                    fn ($groups) => ['href' => $groups->getSelfHref()],
                    iterator_to_array($this->collection)
                ),
            ],
            '_embedded' => [
                'groups' => $this->collection,
            ],
            'total' => count($this->collection),
        ];
    }
}
