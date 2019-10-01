<?php

namespace App;

class UserEvent
{
    /**
     * JSON Decoded message.
     *
     * @var []
     */
    protected $message;

    /**
     *
     * @param [] $message JSON Decoded message.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Process the event data.
     *
     * @return void
     */
    public function process()
    {
        $data = $this->message->data;

        $user = User::firstOrNew([ 'central_id' => $data->origin_id]);

        $user->email = $data->email;
        $user->walter_id = $data->walter_id;
        $user->intranet_id = $data->intranet_id;

        $user->save();
    }
}
