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

        $officeGroup = Group::create([
            'owner_id' => $admin->id,
            'title' => 'Office Secret Santa',
        ]);

        $draw2024 = Draw::create([
            'group_id' => $officeGroup->id,
            'year' => 2024,
            'description' => 'Ho ho ho! Our 2024 Christmas Secret Santa - £30 budget per gift. Happy holidays everyone!',
        ]);

        $allocations2024 = [
            [
                'from_name' => 'Alice',
                'from_email' => 'alice@example.com',
                'to_email' => 'bob@example.com',
                'from_ideas' => [
                    'https://example.com/ember-temperature-control-smart-mug',
                    "Mechanical keyboard with cherry MX switches - preferably something with RGB lighting if possible, hot-swappable switches would be a bonus\nBudget around $150-200\nBrown or blue switches preferred over red",
                    'Socks',
                    'Coffee subscription',
                    'This smart mug is amazing: https://example.com/coffee-grinder-burr-type',
                ],
            ],
            [
                'from_name' => 'Bob',
                'from_email' => 'bob@example.com',
                'to_email' => 'charlie@example.com',
                'from_ideas' => [
                    'Board game - preferably strategy or cooperative games like Pandemic, Ticket to Ride, or Azul',
                    'https://example.com/nintendo-switch-pro-controller',
                    'Really anything related to gaming would be appreciated! I love both video games and tabletop games, so gift cards to Steam, Nintendo eShop, or local board game stores would all be great. Also into puzzle games and RPGs if you want to pick something specific.',
                ],
            ],
            [
                'from_name' => 'Charlie',
                'from_email' => 'charlie@example.com',
                'to_email' => 'diana@example.com',
                'from_ideas' => [
                    "A really nice reusable water bottle - maybe one of those insulated ones that keeps drinks cold for 24 hours and hot for 12 hours. I've been trying to reduce single-use plastic and having a great water bottle would help with that goal. Bonus points if it fits in a standard cup holder!",
                    'Mat',
                    'Something like this https://example.com/reversible-yoga-mat-5mm would be perfect',
                    'https://example.com/gym-duffle-bag-with-shoe-compartment',
                    'Resistance bands set',
                ],
            ],
            [
                'from_name' => 'Diana',
                'from_email' => 'diana@example.com',
                'to_email' => 'ethan@example.com',
                'from_ideas' => [
                    'Gift card',
                    'This book looks great https://example.com/project-hail-mary-hardcover-book - heard amazing things about it!',
                    "Noise-canceling headphones or earbuds - does a lot of video calls from home and the background noise can be distracting\nWould appreciate good audio quality for both calls and music\nOver-ear preferred but good in-ear buds would work too\nBudget flexible up to $250",
                    'https://example.com/hiking-backpack-20l-with-hydration-bladder-compatibility',
                    'Gloves',
                ],
            ],
            [
                'from_name' => 'Ethan',
                'from_email' => 'ethan@example.com',
                'to_email' => 'alice@example.com',
                'from_ideas' => [
                    'https://example.com/tea-forte-warming-joy-petite-presentation-box-assorted-flavors',
                    'Book: "The Design of Everyday Things" by Don Norman - or really any book on UX design, psychology, or product design would be great',
                    'Blanket',
                    'Essential oils like https://example.com/lavender-essential-oil are always appreciated',
                    'Scented candles - loves vanilla, lavender, or eucalyptus scents',
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

        $draw2025 = Draw::create([
            'group_id' => $officeGroup->id,
            'year' => 2025,
            'description' => 'Christmas 2025 Secret Santa! £30 budget. Let the festive gifting begin!',
        ]);

        $allocations2025 = [
            [
                'from_name' => 'Alice',
                'from_email' => 'alice@example.com',
                'to_email' => 'frank@example.com',
            ],
            [
                'from_name' => 'Bob',
                'from_email' => 'bob@example.com',
                'to_email' => 'grace@example.com',
            ],
            [
                'from_name' => 'Charlie',
                'from_email' => 'charlie@example.com',
                'to_email' => 'alice@example.com',
            ],
            [
                'from_name' => 'Frank',
                'from_email' => 'frank@example.com',
                'to_email' => 'henry@example.com',
            ],
            [
                'from_name' => 'Grace',
                'from_email' => 'grace@example.com',
                'to_email' => 'bob@example.com',
            ],
            [
                'from_name' => 'Henry',
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
