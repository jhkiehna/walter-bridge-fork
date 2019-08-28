<?php

namespace Tests\Unit;

use App\User;
use Carbon\Carbon;
use App\CandidateCoded;
use Illuminate\Support\Facades\DB;
use Tests\Walter\WalterBaseTestCase;
use App\Services\Walter\CandidateCodedReader;

class CandidateCodedReaderTest extends WalterBaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $users = User::all();

        for ($i = 2; $i <= 14; $i++) {
            DB::connection('walter_test')
                ->table('person_codeDate')
                ->insert([
                    'cdid' => $i,
                    'DateCoded' => Carbon::now()->subDays($i),
                    'consultant' => $users[$i]->walter_id ?? 1,
                ]);
        }

        DB::setDefaultConnection('sqlite_testing');
    }

    public function testItCanGetNewCandidatesCoded()
    {
        factory(CandidateCoded::class)->create([
            'central_id' => 1,
            'walter_coded_id' => 1,
            'date' => Carbon::now()->subWeek(2),
        ]);

        $candidatesCoded = (new CandidateCodedReader)->getNewCandidatesCoded();

        $this->assertFalse($candidatesCoded->isEmpty());
        $this->assertEquals($candidatesCoded->first()->id, 2);
        $this->assertObjectHasAttribute('consultant', $candidatesCoded->first());
    }

    public function testItCanUseTheReadMethodAndCreateCandidatesCodedInLocalDB()
    {
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
