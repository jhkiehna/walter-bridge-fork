<?php

namespace Tests\Unit;

use App\User;
use App\Interview;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Services\Walter\InterviewReader;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InterviewReaderTest extends TestCase
{
    use RefreshDatabase;

    public $connectionsToTransact = ['sqlite_testing', 'sqlite_walter_test'];

    public function setUp(): void
    {
        parent::setUp();

        $users = User::all();

        for ($i = 1; $i <= 14; $i++) {
            DB::connection('sqlite_walter_test')
                ->table('jobOrder_interview')
                ->insert([
                    'intID' => $i,
                    'DateCreated' => Carbon::now()->subDays($i),
                    'Consultant' => $users[$i]->walter_id ?? 1,
                    'updated_at' => Carbon::now()->subDays($i)
                ]);
        }
    }

    public function testItCanGetNewInterviews()
    {
        factory(Interview::class)->create([
            'walter_interview_id' => 1,
            'updated_at' => Carbon::now()->subWeeks(3),
        ]);

        $interviews = (new InterviewReader)->getNewRecords();

        $this->assertFalse($interviews->isEmpty());
        $this->assertEquals($interviews->first()->id, 1);
        $this->assertObjectHasAttribute('consultant', $interviews->first());
    }

    public function testItCanUseTheReadMethodAndCreateInterviewsInLocalDB()
    {
        Artisan::call("db:seed", [
            "--class" => "UserTableSeeder",
            "--database" => "sqlite_testing",
            "--env" => "testing"
        ]);
        factory(Interview::class)->create([
            'central_id' => 1,
            'walter_interview_id' => 1,
            'date' => Carbon::now()->subWeek(2),
        ]);

        (new InterviewReader)->read();
        $localInterviews = Interview::all();

        $this->assertFalse($localInterviews->isEmpty());
        $this->assertTrue($localInterviews->first()->user != null);
        $this->assertTrue($localInterviews->first()->walter_consultant_id != null);
        $this->assertTrue($localInterviews->first()->walter_interview_id != null);
    }
}
