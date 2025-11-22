<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            '_links' => [
                'self' => [
                    'href' => $this->getSelfHref(),
                ],
                'provide-ideas' => [
                    'href' => route(
                        'allocations.ideas',
                        [
                            'group' => $this->draw->group->id,
                            'draw' => $this->draw->id,
                            'allocation' => $this->id,
                        ]
                    ),
                ],
                'messages-to-recipient' => [
                    'href' => route(
                        'allocations.messages.index',
                        [
                            'group' => $this->draw->group->id,
                            'draw' => $this->draw->id,
                            'allocation' => $this->id,
                        ]
                    ),
                ],
                'messages-from-santa' => [
                    'href' => route(
                        'allocations.messages.index',
                        [
                            'group' => $this->draw->group->id,
                            'draw' => $this->draw->id,
                            'allocation' => $this->secretSanta->id,
                        ]
                    ),
                ],
            ],
            'from' => [
                'id' => $this->from_user_id,
                'name' => $this->from_name,
                'ideas' => $this->from_ideas,
                'access_token' => $this->from_access_token,
            ],
            'to' => [
                'id' => $this->to_user_id,
                'name' => $this->to_name,
                'ideas' => $this->to_ideas,
            ],
        ];
    }

    public function getSelfHref(): string
    {
        return route(
            'allocations.show',
            [
                'group' => $this->draw->group->id,
                'draw' => $this->draw->id,
                'allocation' => $this->id,
            ]
        );
    }
}
