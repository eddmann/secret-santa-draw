<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allocation extends Model
{
    protected $fillable = [
        'draw_id',
        'from_name',
        'from_email',
        'from_user_id',
        'from_access_token',
        'from_ideas',
        'to_email',
        'to_user_id',
    ];

    protected $casts = [
        'from_ideas' => 'array',
    ];

    public function draw(): BelongsTo
    {
        return $this->belongsTo(Draw::class);
    }

    protected function toName(): Attribute
    {
        return Attribute::make(fn () => $this->to()->from_name);
    }

    protected function toIdeas(): Attribute
    {
        return Attribute::make(fn () => $this->to()->from_ideas);
    }

    public function canAccess(?int $userId, ?string $accessToken): bool
    {
        $isOwner = $this->draw->group->owner_id === $userId;
        $isAuthenticatedUser = $userId !== null && $this->from_user_id === $userId;
        $isAuthenticatedToken = $accessToken !== null && $this->from_access_token === $accessToken;

        return $isOwner || $isAuthenticatedUser || $isAuthenticatedToken;
    }

    private function to(): Allocation
    {
        return self::where(
            fn ($query) => $query
                ->where('draw_id', $this->draw_id)
                ->where('from_email', $this->to_email)
        )->firstOrFail();
    }
}
