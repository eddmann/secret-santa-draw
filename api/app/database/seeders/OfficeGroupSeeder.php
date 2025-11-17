<?php

namespace Database\Seeders;

use App\Models\Draw;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfficeGroupSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'test@example.com')->firstOrFail();

        // Create Office Secret Santa group
        $officeGroup = Group::create([
            'owner_id' => $admin->id,
            'title' => 'Office Secret Santa',
        ]);

        // Create 2024 draw with completed allocations and gift ideas
        $draw2024 = Draw::create([
            'group_id' => $officeGroup->id,
            'year' => 2024,
            'description' => '2024 Office Secret Santa',
        ]);

        // Create allocations for 2024 (completed with gift ideas)
        $allocations2024 = [
            [
                'from_name' => 'Alice Johnson',
                'from_email' => 'alice@example.com',
                'to_email' => 'bob@example.com',
                'from_ideas' => ['https://www.amazon.com/coffee-mug-set', 'Desk organizer'],
            ],
            [
                'from_name' => 'Bob Smith',
                'from_email' => 'bob@example.com',
                'to_email' => 'charlie@example.com',
                'from_ideas' => ['Wireless mouse', 'https://www.amazon.com/gaming-headset-wireless'],
            ],
            [
                'from_name' => 'Charlie Brown',
                'from_email' => 'charlie@example.com',
                'to_email' => 'diana@example.com',
                'from_ideas' => ['https://www.amazon.com/wonder-woman-comic-collection', 'Yoga mat'],
            ],
            [
                'from_name' => 'Diana Prince',
                'from_email' => 'diana@example.com',
                'to_email' => 'ethan@example.com',
                'from_ideas' => ['Spy gadget kit', 'https://www.amazon.com/action-movie-box-set'],
            ],
            [
                'from_name' => 'Ethan Hunt',
                'from_email' => 'ethan@example.com',
                'to_email' => 'alice@example.com',
                'from_ideas' => ['https://www.amazon.com/alice-wonderland-special-edition', 'Tea sampler set'],
            ],
        ];

        foreach ($allocations2024 as $allocation) {
            $draw2024->allocations()->create([
                'from_name' => $allocation['from_name'],
                'from_email' => $allocation['from_email'],
                'from_user_id' => User::findByEmail($allocation['from_email'])?->id,
                'from_access_token' => Str::random(64),
                'from_ideas' => $allocation['from_ideas'],
                'to_email' => $allocation['to_email'],
                'to_user_id' => User::findByEmail($allocation['to_email'])?->id,
            ]);
        }

        // Create 2025 draw with allocations but no gift ideas
        $draw2025 = Draw::create([
            'group_id' => $officeGroup->id,
            'year' => 2025,
            'description' => '2025 Office Secret Santa',
        ]);

        // Create allocations for 2025 (no gift ideas)
        $allocations2025 = [
            [
                'from_name' => 'Alice Johnson',
                'from_email' => 'alice@example.com',
                'to_email' => 'frank@example.com',
            ],
            [
                'from_name' => 'Bob Smith',
                'from_email' => 'bob@example.com',
                'to_email' => 'grace@example.com',
            ],
            [
                'from_name' => 'Charlie Brown',
                'from_email' => 'charlie@example.com',
                'to_email' => 'alice@example.com',
            ],
            [
                'from_name' => 'Frank Castle',
                'from_email' => 'frank@example.com',
                'to_email' => 'henry@example.com',
            ],
            [
                'from_name' => 'Grace Hopper',
                'from_email' => 'grace@example.com',
                'to_email' => 'bob@example.com',
            ],
            [
                'from_name' => 'Henry Ford',
                'from_email' => 'henry@example.com',
                'to_email' => 'charlie@example.com',
            ],
        ];

        foreach ($allocations2025 as $allocation) {
            $draw2025->allocations()->create([
                'from_name' => $allocation['from_name'],
                'from_email' => $allocation['from_email'],
                'from_user_id' => User::findByEmail($allocation['from_email'])?->id,
                'from_access_token' => Str::random(64),
                'from_ideas' => [],
                'to_email' => $allocation['to_email'],
                'to_user_id' => User::findByEmail($allocation['to_email'])?->id,
            ]);
        }
    }
}
