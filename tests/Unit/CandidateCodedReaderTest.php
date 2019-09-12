<?php

namespace Tests\Unit;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use App\CandidateCoded;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Services\Walter\CandidateCodedReader;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CandidateCodedReaderTest extends TestCase
{
    use RefreshDatabase;

    public $connectionsToTransact = ['sqlite_testing', 'sqlite_walter_test'];

    public function setUp(): void
    {
        parent::setUp();

        $users = User::all();

        for ($i = 1; $i <= 14; $i++) {
            DB::connection('sqlite_walter_test')
                ->table('person_codeDate')
                ->insert([
                    'cdid' => $i,
                    'DateCoded' => Carbon::now()->subDays($i),
                    'consultant' => $users[$i]->walter_id ?? 1,
                    'updated_at' => Carbon::now()->subDays($i)
                ]);
        }
    }

    public function testItCanGetNewCandidatesCoded()
    {
        factory(CandidateCoded::class)->create([
            'walter_coded_id' => 1,
            'updated_at' => Carbon::now()->subWeeks(3),
        ]);

        $candidatesCoded = (new CandidateCodedReader)->getNewRecords();

        $this->assertFalse($candidatesCoded->isEmpty());
        $this->assertEquals($candidatesCoded->first()->id, 1);
        $this->assertObjectHasAttribute('consultant', $candidatesCoded->first());
    }

    public function testItCanUseTheReadMethodAndCreateCandidatesCodedInLocalDB()
    {
        Artisan::call("db:seed", [
            "--class" => "UserTableSeeder",
            "--database" => "sqlite_testing",
            "--env" => "testing"
        ]);
        factory(CandidateCoded::class)->create([
            'central_id' => 1,
            'walter_coded_id' => 1,
            'date' => Carbon::now()->subWeek(2),
        ]);

        (new CandidateCodedReader)->read();
        $localCandidatesCoded = CandidateCoded::all();

        $this->assertFalse($localCandidatesCoded->isEmpty());
        $this->assertTrue($localCandidatesCoded->first()->user != null);
        $this->assertTrue($localCandidatesCoded->first()->walter_consultant_id != null);
        $this->assertTrue($localCandidatesCoded->first()->walter_coded_id != null);
    }
}
