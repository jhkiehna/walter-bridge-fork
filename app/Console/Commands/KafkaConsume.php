<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KafkaConsumer;

class KafkaConsume extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:consume';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process events in the kafka event stream';

    /**
     * The consumer instance.
     *
     * @var \App\Services\KafkaConsumer
     */
    protected $consumer;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\KafkaConsumer $consumer The kafka consumer.
     *
     * @return void
     */
    public function __construct(KafkaConsumer $consumer)
    {
        parent::__construct();

        $this->consumer = $consumer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->consumer->start(
            function ($topic, $partition, $message) {
                (new KafkaEvent($topic, $message)).process();
            }
        );
    }
}
