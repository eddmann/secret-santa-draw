<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

class FamilyGroupSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'test@example.com')->firstOrFail();

        // Create Family Secret Santa group (no draws yet)
        Group::create([
            'owner_id' => $admin->id,
            'title' => 'Family Secret Santa',
        ]);
    }
}
