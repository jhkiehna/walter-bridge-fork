<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use App\UserEvent;
use App\KafkaEvent;

class KafkaEventTest extends TestCase
{
    use RefreshDatabase;

    public function testItThrowsExceptionWithBadMessage()
    {
        $this->expectException(\Exception::class);

        Config::set("kafka.topics", ['user']);

        (new KafkaEvent('user', "{/This'': } is a bad message'}"))->process();
    }

    public function testItReturnsFalseForUnspecifiedTopics()
    {
        Config::set("kafka.topics", ['test']);

        $result = (new KafkaEvent('ignored', json_encode(["fake message"])))->process();

        $this->assertTrue(!$result);
    }

    public function testItCallsTheUserCreatedEventForUserEvents()
    {
        Config::set("kafka.topics", ['user']);

        $event = [
            "type" => "user_created",
            "user" => [
                "origin_id" => 1,
                "walter_id" => 1,
                "intranet_id" => 1,
                "email" => "fake@kimmel.com",
            ]
        ];

        $result = (new KafkaEvent('user', json_encode($event)))->process();

        $this->assertTrue($result);
        $this->assertDatabaseHas(
            'users',
            [
                "central_id" => 1,
                "walter_id" => 1,
                "intranet_id" => 1,
                "email" => "fake@kimmel.com",
            ]
        );
    }

    public function testItCallsTheUserUpdatedEventForUserEvents()
    {
        Config::set("kafka.topics", ['user']);

        $event = [
            "type" => "user_updated",
            "user" => [
                "origin_id" => 1,
                "walter_id" => 1,
                "intranet_id" => 1,
                "email" => "fake@kimmel.com",
            ]
        ];

        $result = (new KafkaEvent('user', json_encode($event)))->process();

        $this->assertTrue($result);
        $this->assertDatabaseHas(
            'users',
            [
                "central_id" => 1,
                "walter_id" => 1,
                "intranet_id" => 1,
                "email" => "fake@kimmel.com",
            ]
        );
    }
}
