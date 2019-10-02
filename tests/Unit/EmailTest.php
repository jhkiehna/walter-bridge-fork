<?php

namespace Tests\Unit;

use App\Email;
use Carbon\Carbon;
use Tests\TestCase;
use App\Jobs\PublishKafkaJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmailTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testPublishToKafkaMethodCreatesTheCorrectObject()
    {
        Queue::fake();

        factory(Email::class)->create([
            'participant_email' => 'test@testing.com',
            'action' => 1,
            'details' => '<p>Test Body content</p>',
            'date' => Carbon::now(),
        ]);

        $email = Email::first();

        $expectedEmailObject = (object) [
            'type' => 'email',
            'data' => (object) [
                'id' => $email->walter_email_id,
                'user_id' => $email->central_id,
                'participant_email' => 'test@testing.com',
                'incoming' => false,
                'body' => '<p>Test Body content</p>',
                'created_at' => $email->date->toISOString(),
            ]
        ];
        $email->publishToKafka();

        Queue::assertPushed(PublishKafkaJob::class, function ($job) use ($expectedEmailObject) {
            if (json_encode($job->objectToPublish) == json_encode($expectedEmailObject)) {
                return true;
            } else {
                return false;
            }
        });
    }
}
