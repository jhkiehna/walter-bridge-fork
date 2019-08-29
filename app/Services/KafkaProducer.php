<?php

namespace App\Services;

class KafkaProducer
{
    protected $producer;

    public function __construct($producer)
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
