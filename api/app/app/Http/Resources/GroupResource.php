<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            '_links' => array_filter([
                'self' => [
                    'href' => $this->getSelfHref(),
                ],
                'update-group' => [
                    'href' => $this->getSelfHref(),
                ],
                'conduct-draw' => $this->canConductDraw(Auth::id()) ? [
                    'href' => route('draws.create', ['group' => $this->id]),
                ] : null,
                'draws' => [
                    'href' => route('draws', ['group' => $this->id]),
                ],
            ]),
            '_embedded' => [
                'draws' => new DrawCollection($this->resource, $this->draws),
            ],
            'title' => $this->title,
            'previous_years_draw_prefill' => $this->getPreviousYearsDrawPrefillData(),
        ];
    }

    public function getSelfHref(): string
    {
        return route('groups.show', ['group' => $this->id]);
    }
}
