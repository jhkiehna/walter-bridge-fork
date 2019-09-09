<?php

namespace App\Services;

use Kafka\Consumer;

class KafkaConsumer
{
    protected $consumer;

    public function __construct(Consumer $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * Starts the consuming for all topics and
     * messages.
     *
     * @param closure $handler function ($topic, $partition, $message)
     *
     * @return void
     */
    public function start(Closure $handler)
    {
        $this->consumer->start($handler);
    }
}
