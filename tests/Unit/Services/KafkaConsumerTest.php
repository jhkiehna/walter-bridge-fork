<?php

namespace Tests\Unit\Services;

class KafkaConsumerTest extends \Tests\TestCase
{
    public function testItCanConnectToKafka()
    {
        $this->markTestSkipped("This isn't really needed unless nmred/kafka-php changes.");

        $producer = app(\App\Services\KafkaConsumer::class);
    }
}
