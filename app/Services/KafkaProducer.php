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

    /**
     * Publish a message to the event stream.
     *
     * Be sure to `json_encode` the message before passing the
     * message.
     *
     * @param string $topic The topic to publish the message to.
     * @param string $message JSON encoded object string.
     * @param string $key This isn't used right now.
     */
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
