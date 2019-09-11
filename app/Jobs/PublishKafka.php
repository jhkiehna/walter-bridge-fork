<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\KafkaProducer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PublishKafka implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $kafkaProducer;

    public function __construct(KafkaProducer $kafkaProducer)
    {
        $this->kafkaProducer = $kafkaProducer;
    }

    public function handle()
    {
        //app(KafkaProducer::class)->publish("testing", json_encode($this));
    }
}
