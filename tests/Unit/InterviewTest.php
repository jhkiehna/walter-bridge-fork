<?php

namespace Tests\Unit;

use App\Interview;
use Tests\TestCase;
use App\Jobs\PublishKafkaJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;
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

        factory(Interview::class)->create();

        $interview = Interview::first();

        $expectedInterviewObject = (object) [
            'type' => 'interview',
            'data' => (object) [
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

    public function testOnlyRecentlyCreatedOrUpdatedInterviewsAreReturnedFromWriteWithForeignRecord()
    {
        Artisan::call("db:seed", [
            "--class" => "UserTableSeeder",
            "--database" => "sqlite_testing",
            "--env" => "testing"
        ]);
        DB::connection('sqlite_walter_test')
            ->table('jobOrder_interview')
            ->truncate();
        DB::connection('sqlite_walter_test')
            ->table('jobOrder_interview')
            ->insert([
                [
                    'intID' => 1,
                    'DateCreated' => $createdDate = Carbon::now()->subDays(1),
                    'consultant' => 9,
                ],
                [
                    'intID' => 2,
                    'DateCreated' => Carbon::now()->subDays(1),
                    'consultant' => 9,
                ],
                [
                    'intID' => 3,
                    'DateCreated' => Carbon::now()->subDays(1),
                    'consultant' => 9,
                ]
            ]);

        factory(Interview::class)->create([
            'central_id' => 2,
            'walter_consultant_id' => 9,
            'walter_interview_id' => 1,
            'date' => $createdDate
        ]);
        factory(Interview::class)->create([
            'central_id' => 2,
            'walter_consultant_id' => 9,
            'walter_interview_id' => 2,
            'date' => Carbon::now()->subDays(30)
        ]);

        $foreignInterviews = DB::connection('sqlite_walter_test')
            ->table('jobOrder_interview')
            ->get();

        $foreignInterviews = $foreignInterviews->map(function ($interview) {
            return (object) [
                'id' => $interview->intID,
                'date' => $interview->dateCreated,
                'consultant' => $interview->consultant,
            ];
        });

        $localRecord1 = Interview::writeWithForeignRecord($foreignInterviews[0]);
        $localRecord2 = Interview::writeWithForeignRecord($foreignInterviews[1]);
        $localRecord3 = Interview::writeWithForeignRecord($foreignInterviews[2]);

        $this->assertNull($localRecord1);
        $this->assertNotNull($localRecord2);
        $this->assertTrue(!empty($localRecord2->getChanges()));
        $this->assertTrue(!$localRecord2->wasRecentlyCreated);
        $this->assertNotNull($localRecord3);
        $this->assertTrue($localRecord3->wasRecentlyCreated);
    }
}
