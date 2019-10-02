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

    public function testItReturnsFalseForUnspecifiedTopics()
    {
        Config::set("kafka.topics", ['test']);

        $result = (new KafkaEvent)->process('ignored', (object) ["fake message"]);

        $this->assertTrue(!$result);
    }

    public function testItCallsTheUserCreatedEventForUserEvents()
    {
        Config::set("kafka.topics", ['kimmel']);

        $event = (object) [
            "type" => "user",
            "data" => (object) [
                "id" => 1,
                "walter_id" => 1,
                "intranet_id" => 1,
                "email" => "fake@kimmel.com",
            ]
        ];

        $result = (new KafkaEvent())->process('kimmel', $event);

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
        Config::set("kafka.topics", ['kimmel']);

        $event = (object) [
            "type" => "user",
            "data" => (object) [
                "id" => 1,
                "walter_id" => 1,
                "intranet_id" => 1,
                "email" => "fake@kimmel.com",
            ]
        ];

        $result = (new KafkaEvent())->process('kimmel', $event);

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

    public function testItThrowsExceptionWithNullMessage()
    {
        Config::set("kafka.topics", ['kimmel']);

        $this->expectException(\App\Exceptions\NullMessageException::class);
        $result = (new KafkaEvent())->process('kimmel', null);
    }
}
