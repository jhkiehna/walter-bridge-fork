<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\CandidateCoded;
use App\Jobs\PublishKafkaJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CandidateCodedTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testPublishToKafkaMethodCreatesTheCorrectObject()
    {
        Queue::fake();

        factory(CandidateCoded::class)->create();

        $candidateCoded = CandidateCoded::first();

        $expectedCandidateCodedObject = (object) [
            'type' => 'candidate-coded',
            'data' => (object) [
                'id' => $candidateCoded->walter_coded_id,
                'user_id' => $candidateCoded->central_id,
                'created_at' => $candidateCoded->date->toISOString(),
            ]
        ];
        $candidateCoded->publishToKafka();

        Queue::assertPushed(PublishKafkaJob::class, function ($job) use ($expectedCandidateCodedObject) {
            if (json_encode($job->objectToPublish) == json_encode($expectedCandidateCodedObject)) {
                return true;
            } else {
                return false;
            }
        });
    }

    public function testOnlyRecentlyCreatedOrUpdatedCodedesAreReturnedFromWriteWithForeignRecord()
    {
        Artisan::call("db:seed", [
            "--class" => "UserTableSeeder",
            "--database" => "sqlite_testing",
            "--env" => "testing"
        ]);
        DB::connection('sqlite_walter_test')
            ->table('person_codeDate')
            ->truncate();
        DB::connection('sqlite_walter_test')
            ->table('person_codeDate')
            ->insert([
                [
                    'cdid' => 1,
                    'DateCoded' => $codedDate = Carbon::now()->subDays(1),
                    'consultant' => 9,
                ],
                [
                    'cdid' => 2,
                    'DateCoded' => Carbon::now()->subDays(1),
                    'consultant' => 9,
                ], [
                    'cdid' => 3,
                    'DateCoded' => Carbon::now()->subDays(1),
                    'consultant' => 9,
                ]
            ]);

        factory(CandidateCoded::class)->create([
            'central_id' => 2,
            'walter_consultant_id' => 9,
            'walter_coded_id' => 1,
            'date' => $codedDate
        ]);
        factory(CandidateCoded::class)->create([
            'central_id' => 2,
            'walter_consultant_id' => 9,
            'walter_coded_id' => 2,
            'date' => Carbon::now()->subDays(30)
        ]);

        $foreignCodedes = DB::connection('sqlite_walter_test')
            ->table('person_codeDate')
            ->get();

        $foreignCodedes = $foreignCodedes->map(function ($coded) {
            return (object) [
                'id' => $coded->cdid,
                'date' => $coded->dateCoded,
                'consultant' => $coded->consultant,
                'updated_at' => $coded->updated_at
            ];
        });

        $localRecord1 = CandidateCoded::writeWithForeignRecord($foreignCodedes[0]);
        $localRecord2 = CandidateCoded::writeWithForeignRecord($foreignCodedes[1]);
        $localRecord3 = CandidateCoded::writeWithForeignRecord($foreignCodedes[2]);

        $this->assertNull($localRecord1);
        $this->assertNotNull($localRecord2);
        $this->assertTrue(!empty($localRecord2->getChanges()));
        $this->assertTrue(!$localRecord2->wasRecentlyCreated);
        $this->assertNotNull($localRecord3);
        $this->assertTrue($localRecord3->wasRecentlyCreated);
    }
}
