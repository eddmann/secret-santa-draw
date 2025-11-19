<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function messages(): HasMany
    {
        return $this->hasMany(AllocationMessage::class);
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
        $isSantaUserId = $userId !== null && $this->from_user_id === $userId;
        $isSantaAuthenticatedToken = $accessToken !== null && $this->from_access_token === $accessToken;

        return $isOwner || $isSantaUserId || $isSantaAuthenticatedToken;
    }

    public function canAccessMessages(?int $userId, ?string $accessToken): bool
    {
        $isOwner = $this->draw->group->owner_id === $userId;
        $isPairingUserId = $userId !== null && ($this->from_user_id === $userId || $this->to_user_id === $userId);
        $recipientAccessToken = Allocation::where(['from_email' => $this->to_email, 'draw_id' => $this->draw_id])->value('from_access_token');
        $isPairingAuthenticatedToken = $accessToken !== null && ($this->from_access_token === $accessToken || $accessToken === $recipientAccessToken);

        return $isOwner || $isPairingUserId || $isPairingAuthenticatedToken;
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
