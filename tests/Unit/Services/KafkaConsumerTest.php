<?php

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;

class KafkaConsumerTest extends \Tests\TestCase
{
    //use RefreshDatabase;

    public function testItCanConnectToKafka()
    {
        $this->markTestSkipped("This isn't really needed unless nmred/kafka-php changes.");

        $producer = app(\App\Services\KafkaConsumer::class);
    }
}
