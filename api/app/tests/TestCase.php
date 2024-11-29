<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function participants(): array
    {
        return [
            [
                'name' => 'Participant 1',
                'email' => 'participant-1@test.com',
                'exclusions' => [
                    'participant-2@test.com',
                ],
            ],
            [
                'name' => 'Participant 2',
                'email' => 'participant-2@test.com',
                'exclusions' => [
                    'participant-3@test.com',
                ],
            ],
            [
                'name' => 'Participant 3',
                'email' => 'participant-3@test.com',
                'exclusions' => [
                    'participant-4@test.com',
                ],
            ],
            [
                'name' => 'Participant 4',
                'email' => 'participant-4@test.com',
                'exclusions' => [
                    'participant-1@test.com',
                ],
            ],
        ];
    }
}
