<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\UserEvent;

class UserEventTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesNewUsers()
    {
        $fakeMessage = [
            'type' => 'create_user',
            'user' => [
                'origin_id' => 10,
                'email' => 'fake_user@kimmel.com',
                'walter_id' => 15,
                'intranet_id' => 19
            ]
        ];

        (new UserEvent($fakeMessage))->process();

        $this->assertDatabaseHas(
            'users',
            [
                'central_id' => 10,
                'email' => 'fake_user@kimmel.com',
                'walter_id' => 15,
                'intranet_id' => 19
            ]
        );
    }
}
