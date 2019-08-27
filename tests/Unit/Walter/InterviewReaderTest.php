<?php

namespace Tests\Unit;

use App\User;
use App\Interview;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Walter\WalterBaseTestCase;
use App\Services\Walter\InterviewReader;

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

    public function testItCanGetNewInterviews()
    {
        factory(Interview::class)->create([
            'central_id' => 1,
            'walter_interview_id' => 1,
            'date' => Carbon::now()->subWeek(2),
        ]);

        $interviews = (new InterviewReader)->getNewInterviews();

        $this->assertFalse($interviews->isEmpty());
        $this->assertEquals($interviews->first()->id, 2);
        $this->assertObjectHasAttribute('consultant', $interviews->first());
    }

    public function testItCanUseTheReadMethodAndCreateInterviewsInLocalDB()
    {
        factory(Interview::class)->create([
            'central_id' => 1,
            'walter_interview_id' => 1,
            'date' => Carbon::now()->subWeek(2),
        ]);

        (new InterviewReader)->read();
        $localInterviews = Interview::all();

        $this->assertFalse($localInterviews->isEmpty());
        $this->assertTrue($localInterviews->first()->user != null);
    }
}
