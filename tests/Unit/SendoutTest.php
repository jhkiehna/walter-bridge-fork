<?php

namespace Tests\Unit;

use App\Sendout;
use Carbon\Carbon;
use Tests\TestCase;
use App\Jobs\PublishKafkaJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;
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

        factory(Sendout::class)->create();

        $sendout = Sendout::first();

        $expectedSendoutObject = (object) [
            'type' => 'sendout',
            'data' => (object) [
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

    public function testOnlyRecentlyCreatedOrUpdatedInterviewsAreReturnedFromWriteWithForeignRecord()
    {
        Artisan::call("db:seed", [
            "--class" => "UserTableSeeder",
            "--database" => "sqlite_testing",
            "--env" => "testing"
        ]);
        DB::connection('sqlite_walter_test')
            ->table('SendOut')
            ->truncate();
        DB::connection('sqlite_walter_test')
            ->table('SendOut')
            ->insert([
                [
                    'soid' => 1,
                    'DateSent' => $sentDate = Carbon::now()->subDays(1),
                    'consultant' => 9,
                    'firstResume' => true
                ],
                [
                    'soid' => 2,
                    'DateSent' => Carbon::now()->subDays(1),
                    'consultant' => 9,
                    'firstResume' => true
                ],
                [
                    'soid' => 3,
                    'DateSent' => Carbon::now()->subDays(1),
                    'consultant' => 9,
                    'firstResume' => true
                ]
            ]);

        factory(Sendout::class)->create([
            'central_id' => 2,
            'walter_consultant_id' => 9,
            'walter_sendout_id' => 1,
            'date' => $sentDate
        ]);
        factory(Sendout::class)->create([
            'central_id' => 2,
            'walter_consultant_id' => 9,
            'walter_sendout_id' => 2,
            'date' => Carbon::now()->subDays(30)
        ]);

        $foreignSendouts = DB::connection('sqlite_walter_test')
            ->table('SendOut')
            ->get();

        $foreignSendouts = $foreignSendouts->map(function ($sendout) {
            return (object) [
                'id' => $sendout->soid,
                'date' => $sendout->DateSent,
                'consultant' => $sendout->Consultant,
                'firstResume' => $sendout->firstResume,
            ];
        });

        $localRecord1 = Sendout::writeWithForeignRecord($foreignSendouts[0]);
        $localRecord2 = Sendout::writeWithForeignRecord($foreignSendouts[1]);
        $localRecord3 = Sendout::writeWithForeignRecord($foreignSendouts[2]);

        $this->assertNull($localRecord1);
        $this->assertNotNull($localRecord2);
        $this->assertTrue(!empty($localRecord2->getChanges()));
        $this->assertTrue(!$localRecord2->wasRecentlyCreated);
        $this->assertNotNull($localRecord3);
        $this->assertTrue($localRecord3->wasRecentlyCreated);
    }
}
