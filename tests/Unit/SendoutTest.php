<?php

namespace Tests\Unit;

use App\Sendout;
use Carbon\Carbon;
use Tests\TestCase;
use App\Jobs\PublishKafkaJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendoutTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testPublishToKafkaMethodCreatesTheCorrectObject()
    {
        Queue::fake();

        $sendout = factory(Sendout::class)->create();
        $expectedSendoutObject = (object) [
            'type' => 'sendout',
            'sendout' => (object) [
                'id' => $sendout->walter_sendout_id,
                'user_id' => $sendout->central_id,
                'created_at' => $sendout->date->toISOString(),
            ]
        ];
        $sendout->publishToKafka();

        Queue::assertPushed(PublishKafkaJob::class, function ($job) use ($expectedSendoutObject) {
            if (json_encode($job->objectToPublish) == json_encode($expectedSendoutObject)) {
                return true;
            } else {
                return false;
            }
        });
    }
}
