<?php

namespace Tests\Unit;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Walter\WalterBaseTestCase;

class InterviewReaderTest extends WalterBaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $users = User::all();

        for ($i = 2; $i <= 14; $i++) {
            DB::connection('walter_test')
                ->table('jobOrder_interview')
                ->insert([
                    'intID' => $i,
                    'DateCreated' => Carbon::now()->subDays($i),
                    'Consultant' => $users[$i]->walter_id ?? 1,
                ]);
        }

        DB::setDefaultConnection('sqlite_testing');
    }

    public function testTest()
    {
        $this->assertTrue(true);
    }

    // public function testItCanGetNewSendouts()
    // {
    //     factory(Sendout::class)->create([
    //         'central_id' => 1,
    //         'date' => Carbon::now()->subWeek(2),
    //     ]);

    //     $sendouts = (new SendoutReader)->getNewSendouts();

    //     $this->assertFalse($sendouts->isEmpty());
    //     $this->assertEquals($sendouts->first()->id, 2);
    //     $this->assertObjectHasAttribute('Consultant', $sendouts->first());
    // }

    // public function testItCanUseTheReadMethodAndCreateSendoutsInLocalDB()
    // {
    //     factory(Sendout::class)->create([
    //         'central_id' => 1,
    //         'date' => Carbon::now()->subWeek(2),
    //     ]);

    //     (new SendoutReader)->read();
    //     $localSendouts = Sendout::all();

    //     $this->assertFalse($localSendouts->isEmpty());
    //     $this->assertTrue($localSendouts->first()->user != null);
    // }
}
