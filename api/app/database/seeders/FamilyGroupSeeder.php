<?php

namespace Database\Seeders;

use App\Models\Draw;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FamilyGroupSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'test@example.com')->firstOrFail();

        $familyGroup = Group::create([
            'owner_id' => $admin->id,
            'title' => 'Family Secret Santa',
        ]);

        $draw2024 = Draw::create([
            'group_id' => $familyGroup->id,
            'year' => 2024,
            'description' => 'Family Christmas 2024 - £50 budget. Keep it thoughtful!',
        ]);

        $allocations2024 = [
            [
                'from_name' => 'Mum',
                'from_email' => 'mum@family.com',
                'to_email' => 'dad@family.com',
                'from_ideas' => [
                    'Gardening tools from Wilko',
                    'Mary Berry cookbook',
                    'Nice box of Thorntons chocolates',
                ],
            ],
            [
                'from_name' => 'Dad',
                'from_email' => 'dad@family.com',
                'to_email' => 'sister@family.com',
                'from_ideas' => [
                    'Golf accessories from Sports Direct',
                    'A proper bottle of whisky',
                ],
            ],
            [
                'from_name' => 'Sister',
                'from_email' => 'sister@family.com',
                'to_email' => 'brother@family.com',
                'from_ideas' => [
                    'Yoga mat from Decathlon',
                    'Running trainers',
                    'Fitness tracker',
                    'John Lewis voucher',
                ],
            ],
            [
                'from_name' => 'Brother',
                'from_email' => 'brother@family.com',
                'to_email' => 'mum@family.com',
                'from_ideas' => [
                    'PlayStation game',
                    'Proper wireless headphones',
                    'Amazon voucher',
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
    }
}
