<?php

namespace App\Services;

use Kafka\Producer;

class KafkaProducer
{
    protected $producer;

    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    public function publish($topic, $message, $key = '')
    {
        return $this->producer->send(
            [
                [
                    "topic" => $topic,
                    "value" => $message,
                    "key" => $key
                ]
            ]
        );
    }
}
