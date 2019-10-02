<?php

namespace App;

use App\Exceptions\NullMessageException;

class KafkaEvent
{
    /**
     * Process the event with the approiate
     * event class.
     *
     * @return void
     */
    public function process($topic, $message)
    {
        if (is_null($message)) {
            throw new NullMessageException("Failed to decode message.");
        }

        // we are only currently interested in the expected topics
        if (!in_array($topic, config("kafka.topics"))) {
            return false;
        }

        switch ($message->type) {
            case 'user':
                (new UserEvent($message))->process();
                break;
        }

        return true;
    }
}
