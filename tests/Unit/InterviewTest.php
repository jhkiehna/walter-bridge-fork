<?php

namespace Tests\Unit;

use App\Interview;
use Tests\TestCase;
use App\Jobs\PublishKafkaJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InterviewTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testPublishToKafkaMethodCreatesTheCorrectObject()
    {
        Queue::fake();

        $interview = factory(Interview::class)->create();
        $expectedInterviewObject = (object) [
            'type' => 'interview',
            'interview' => (object) [
                'id' => $interview->walter_interview_id,
                'user_id' => $interview->central_id,
                'created_at' => $interview->date->toISOString(),
            ]
        ];
        $interview->publishToKafka();

        Queue::assertPushed(PublishKafkaJob::class, function ($job) use ($expectedInterviewObject) {
            if (json_encode($job->objectToPublish) == json_encode($expectedInterviewObject)) {
                return true;
            } else {
                return false;
            }
        });
    }
}
