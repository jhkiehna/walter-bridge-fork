<?php

namespace Tests\Unit\Services;

class KafkaProducerTest extends \Tests\TestCase
{
    public function testItCanConnectToKafka()
    {
        $this->markTestSkipped("This isn't really needed unless nmred/kafka-php changes.");

        $producer = app(\App\Services\KafkaProducer::class);

        $producer->publish("testing", "message");
    }
}
