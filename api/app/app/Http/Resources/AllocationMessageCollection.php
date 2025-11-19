<?php

namespace App\Http\Resources;

use App\Models\Allocation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AllocationMessageCollection extends ResourceCollection
{
    public function __construct(
        private readonly Allocation $allocation,
        private readonly bool $isUserSecretSanta,
        private readonly array $messages,
    ) {}

    public function toArray(Request $request): array
    {
        return [
            '_links' => [
                'self' => [
                    'href' => route(
                        'allocations.messages.index',
                        [
                            'group' => $this->allocation->draw->group->id,
                            'draw' => $this->allocation->draw->id,
                            'allocation' => $this->allocation->id,
                        ]
                    ),
                ],
                'send-message' => [
                    'href' => route(
                        'allocations.messages.create',
                        [
                            'group' => $this->allocation->draw->group->id,
                            'draw' => $this->allocation->draw->id,
                            'allocation' => $this->allocation->id,
                        ]
                    ),
                ],
                'messages' => array_map(
                    fn ($message) => ['href' => $message->getSelfHref()],
                    $this->messages
                ),
            ],
            '_embedded' => [
                'messages' => $this->messages,
            ],
            'participant_name' => $this->isUserSecretSanta
                ? $this->allocation->to_name
                : 'Your Secret Santa',
            'conversation_type' => $this->isUserSecretSanta
                ? 'to-recipient'
                : 'from-santa',
            'total' => count($this->messages),
        ];
    }
}
