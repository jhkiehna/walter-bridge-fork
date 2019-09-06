<?php

namespace App;

class KafkaEvent
{
    /**
     * The topic the event was received from.
     *
     * @var string
     */
    protected $topic;

    /**
     * The message or event.
     *
     * @var []
     */
    protected $message;

    public function __construct($topic, $message)
    {
        $this->topic = $topic;
        $this->message = json_decode($message);

        if (is_null($this->message)) {
            throw Exception("Failed to decode message.");
        }
    }

    /**
     * Process the event with the approiate
     * event class.
     *
     * @return void
     */
    public function process()
    {
        // we are only currently interested in the expected topics
        if (!in_array($this->topic, config("kafka.topics"))) {
            return false;
        }

        switch ($this->message->type) {
        case 'user_created':
        case 'user_updated':
            (new UserEvent($this->message))->process();
            break;
        }

        return true;
    }
}
