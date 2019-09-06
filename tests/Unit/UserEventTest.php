<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\UserEvent;
use App\User;

class UserEventTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesNewUsers()
    {
        $fakeMessage = json_encode(
            [
            'type' => 'create_user',
            'user' => [
                'origin_id' => 10,
                'email' => 'fake_user@kimmel.com',
                'walter_id' => 15,
                'intranet_id' => 19
            ]
            ]
        );

        (new UserEvent(json_decode($fakeMessage)))->process();

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

    public function testItUpdatesUsers()
    {
        User::create(
            [
                'central_id' => 10,
                'email' => 'old@email.com',
                'walter_id' => 1,
                'intranet_id' => 2
            ]
        );

        $this->assertDatabaseHas(
            'users',
            [
                'central_id' => 10,
                'email' => 'old@email.com',
                'walter_id' => 1,
                'intranet_id' => 2
            ]
        );

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

        $this->assertTrue(User::count() === 1);
    }
}
