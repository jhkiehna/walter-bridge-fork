<?php

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;

class CallReaderTest extends \Tests\TestCase
{
    //use RefreshDatabase;

    public function testItCanConnectToKafka()
    {
        $this->markTestSkipped("This isn't really needed unless nmred/kafka-php changes.");

        $producer = app(\App\Services\KafkaProducer::class);

        $producer->publish("testing", "message");
    }
}
