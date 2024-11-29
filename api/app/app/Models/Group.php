<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Group extends Model
{
    protected $fillable = [
        'owner_id',
        'title',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOwner(?User $user): bool
    {
        return $this->owner_id === $user?->id;
    }

    public function draws(): HasMany
    {
        return $this->hasMany(Draw::class);
    }

    public function canConductDraw(?int $userId): bool
    {
        $hasDrawForCurrentYear = collect($this->draws)->some(fn (Draw $draw) => $draw->year === (int) date('Y'));

        return $userId === $this->owner_id && ! $hasDrawForCurrentYear;
    }

    public function draw(int $year, string $description, array $participants): Draw
    {
        DB::beginTransaction();

        $draw = $this->draws()->create([
            'year' => $year,
            'description' => $description,
        ]);

        foreach ($this->allocate($participants) as [$from, $to]) {
            $draw->allocations()->create([
                'from_name' => $from['name'],
                'from_email' => $from['email'],
                'from_user_id' => User::findByEmail($from['email'])?->id,
                'from_access_token' => Str::random(64),
                'from_ideas' => '',
                'to_email' => $to['email'],
                'to_user_id' => User::findByEmail($to['email'])?->id,
            ]);
        }

        DB::commit();

        return $draw;
    }

    private function allocate(array $participants, int $attempts = 250): array
    {
        if ($attempts === 0) {
            throw new \RuntimeException('Failed to allocate participants');
        }

        $from = [...$participants];
        $to = [...$participants];

        shuffle($to);

        $allocations = array_map(null, $from, $to);

        foreach ($allocations as [$from, $to]) {
            if ($from['email'] === $to['email'] || in_array($to['email'], $from['exclusions'])) {
                return $this->allocate($participants, $attempts - 1);
            }
        }

        return $allocations;
    }
}
