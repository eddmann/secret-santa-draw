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
        // These exercise various gift idea formats: links, text, multiline, short, long, mixed
        $allocations2024 = [
            [
                'from_name' => 'Alice Johnson',
                'from_email' => 'alice@example.com',
                'to_email' => 'bob@example.com',
                'from_ideas' => [
                    'https://www.amazon.com/Ember-Temperature-Control-Smart-Mug/dp/B07NQRM6ML',
                    "Mechanical keyboard with cherry MX switches\nPrefer something with RGB lighting if possible\nBudget around $150",
                    'Coffee subscription',
                    'https://www.etsy.com/listing/custom-desk-nameplate',
                ],
            ],
            [
                'from_name' => 'Bob Smith',
                'from_email' => 'bob@example.com',
                'to_email' => 'charlie@example.com',
                'from_ideas' => [
                    'Board game',
                    'https://www.target.com/p/nintendo-switch-pro-controller/-/A-52052007',
                ],
            ],
            [
                'from_name' => 'Charlie Brown',
                'from_email' => 'charlie@example.com',
                'to_email' => 'diana@example.com',
                'from_ideas' => [
                    "A really nice reusable water bottle - maybe one of those insulated ones that keeps drinks cold for 24 hours. I've been trying to reduce single-use plastic and having a great water bottle would help with that goal.",
                    'Yoga mat',
                    'https://www.lululemon.com/en-us/p/the-reversible-mat-5mm/prod8430206.html',
                    'Gym bag or athletic backpack',
                ],
            ],
            [
                'from_name' => 'Diana Prince',
                'from_email' => 'diana@example.com',
                'to_email' => 'ethan@example.com',
                'from_ideas' => [
                    'Movie tickets gift card',
                    'https://www.bookshop.org/books/project-hail-mary/9780593135204',
                    "Noise-canceling headphones\nDoes a lot of video calls\nWould appreciate good audio quality",
                    'https://www.rei.com/product/hiking-backpack-20l',
                ],
            ],
            [
                'from_name' => 'Ethan Hunt',
                'from_email' => 'ethan@example.com',
                'to_email' => 'alice@example.com',
                'from_ideas' => [
                    'https://www.uncommongoods.com/product/tea-forte-warming-joy-petite-presentation-box',
                    'Book: "The Design of Everyday Things" by Don Norman',
                    'Cozy blanket',
                    'https://www.sephora.com/product/lavender-essential-oil-P428053',
                    'Scented candles - loves vanilla or lavender scents',
                ],
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
