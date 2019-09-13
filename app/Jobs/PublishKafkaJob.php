<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\KafkaProducer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PublishKafkaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $objectToPublish;

    public function __construct($objectToPublish)
    {
        $this->objectToPublish = $objectToPublish;
    }

    public function handle()
    {
        app(KafkaProducer::class)->publish('walter-bridge', json_encode($this->objectToPublish));
    }
}
