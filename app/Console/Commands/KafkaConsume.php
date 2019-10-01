<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KafkaConsumer;
use App\KafkaEvent;
use App\Exceptions\NullMessageException;

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
    public function __construct(KafkaConsumer $consumer, KafkaEvent $kafkaEvent)
    {
        parent::__construct();

        $this->consumer = $consumer;
        $this->kafkaEvent = $kafkaEvent;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->consumer->start(
            function ($topic, $partition, $event) {
                info("Received Kafka Event", [$topic, $partition, $event]);
                try {
                    $value = json_decode($event['message']['value'], false, JSON_THROW_ON_ERROR);
                    $this->kafkaEvent->process($topic, $value);
                } catch (\JsonException | NullMessageException $e) {
                    \Log::error("Failed to decode Kafka message!", [$e, $event['message']['value']]);
                }
            }
        );
    }
}
