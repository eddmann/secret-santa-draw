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
                'from_name' => 'Oliver',
                'from_email' => 'oliver@example.com',
                'to_email' => 'amelia@example.com',
                'from_ideas' => [
                    'https://example.com/emma-bridgewater-polka-dot-mug',
                    "Mechanical keyboard with cherry MX switches - preferably something with RGB lighting if possible, hot-swappable switches would be a bonus\nBrown or blue switches preferred over red",
                    'Pair of woolly socks from M&S',
                    'Pret subscription card',
                    'This proper tea mug is brilliant: https://example.com/royal-worcester-bone-china-mug',
                ],
            ],
            [
                'from_name' => 'Amelia',
                'from_email' => 'amelia@example.com',
                'to_email' => 'george@example.com',
                'from_ideas' => [
                    'Board game - preferably strategy or cooperative games like Pandemic, Ticket to Ride, or Catan',
                    'https://example.com/nintendo-switch-pro-controller',
                    'Really anything related to gaming would be appreciated! I love both video games and tabletop games, so gift cards to Steam, Nintendo eShop, or Waterstones would all be great. Also into puzzle games and RPGs if you want to pick something specific.',
                ],
            ],
            [
                'from_name' => 'George',
                'from_email' => 'george@example.com',
                'to_email' => 'charlotte@example.com',
                'from_ideas' => [
                    "A really nice reusable water bottle - maybe one of those insulated Chilly's bottles that keeps drinks cold for 24 hours and hot for 12 hours. I've been trying to reduce single-use plastic and having a proper water bottle would help with that goal. Bonus points if it fits in a standard cup holder!",
                    'Yoga mat from Sweaty Betty',
                    'Something like this https://example.com/lululemon-reversible-yoga-mat-5mm would be perfect',
                    'https://example.com/gym-king-duffle-bag-with-shoe-compartment',
                    'Resistance bands set from Decathlon',
                ],
            ],
            [
                'from_name' => 'Charlotte',
                'from_email' => 'charlotte@example.com',
                'to_email' => 'harry@example.com',
                'from_ideas' => [
                    'John Lewis voucher',
                    'This book looks brilliant https://example.com/project-hail-mary-hardcover-book - heard amazing things about it!',
                    "Noise-canceling headphones or earbuds - does loads of video calls from home and the background noise can be distracting\nWould appreciate good audio quality for both calls and music\nOver-ear preferred but good in-ear buds would work too\nBudget flexible up to £250",
                    'https://example.com/berghaus-hiking-rucksack-20l-with-hydration-bladder-compatibility',
                    'Pair of proper walking gloves',
                ],
            ],
            [
                'from_name' => 'Harry',
                'from_email' => 'harry@example.com',
                'to_email' => 'oliver@example.com',
                'from_ideas' => [
                    'https://example.com/twinings-tea-selection-box-assorted-flavours',
                    'Book: "The Design of Everyday Things" by Don Norman - or really any book on UX design, psychology, or product design would be brilliant',
                    'Cosy blanket from Next',
                    'Essential oils like https://example.com/neals-yard-lavender-essential-oil are always appreciated',
                    'Scented candles from Jo Malone - loves vanilla, lavender, or eucalyptus scents',
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
                'from_name' => 'Oliver',
                'from_email' => 'oliver@example.com',
                'to_email' => 'isla@example.com',
            ],
            [
                'from_name' => 'Amelia',
                'from_email' => 'amelia@example.com',
                'to_email' => 'jack@example.com',
            ],
            [
                'from_name' => 'George',
                'from_email' => 'george@example.com',
                'to_email' => 'oliver@example.com',
            ],
            [
                'from_name' => 'Isla',
                'from_email' => 'isla@example.com',
                'to_email' => 'thomas@example.com',
            ],
            [
                'from_name' => 'Jack',
                'from_email' => 'jack@example.com',
                'to_email' => 'amelia@example.com',
            ],
            [
                'from_name' => 'Thomas',
                'from_email' => 'thomas@example.com',
                'to_email' => 'george@example.com',
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
