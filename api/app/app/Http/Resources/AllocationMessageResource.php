<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllocationMessageResource extends JsonResource
{
    public function __construct(
        private readonly bool $isUserSecretSanta,
        $resource
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            '_links' => [
                'self' => [
                    'href' => $this->getSelfHref(),
                ],
            ],
            'message' => $this->message,
            'is_from_me' => $this->is_from_secret_santa === $this->isUserSecretSanta,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }

    public function getSelfHref(): string
    {
        return route(
            'allocations.messages.show',
            [
                'group' => $this->allocation->draw->group->id,
                'draw' => $this->allocation->draw->id,
                'allocation' => $this->allocation->id,
                'message' => $this->id,
            ]
        );
    }
}
