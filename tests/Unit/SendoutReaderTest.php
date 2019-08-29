<?php

namespace Tests\Unit;

use App\User;
use App\Sendout;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Services\Walter\SendoutReader;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendoutReaderTest extends TestCase
{
    use RefreshDatabase;

    public $connectionsToTransact = ['sqlite_testing', 'walter_test'];

    public function setUp(): void
    {
        parent::setUp();

        $users = User::all();

        for ($i = 2; $i <= 14; $i++) {
            DB::connection('walter_test')
                ->table('SendOut')
                ->insert([
                    'soid' => $i,
                    'DateSent' => Carbon::now()->subDays($i),
                    'Consultant' => $users[$i]->walter_id ?? 1,
                    'firstResume' => true
                ]);
        }
    }

    public function testItCanGetNewSendouts()
    {
        factory(Sendout::class)->create([
            'date' => Carbon::now()->subWeek(2),
        ]);

        $sendouts = (new SendoutReader)->getNewSendouts();

        $this->assertFalse($sendouts->isEmpty());
        $this->assertEquals($sendouts->first()->id, 2);
        $this->assertObjectHasAttribute('consultant', $sendouts->first());
    }

    public function testItCanUseTheReadMethodAndCreateSendoutsInLocalDB()
    {
        Artisan::call("db:seed", [
            "--database" => "sqlite_testing",
            "--env" => "testing"
        ]);
        factory(Sendout::class)->create([
            'central_id' => 1,
            'date' => Carbon::now()->subWeek(2),
        ]);

        (new SendoutReader)->read();
        $localSendouts = Sendout::all();

        $this->assertFalse($localSendouts->isEmpty());
        $this->assertTrue($localSendouts->first()->user != null);
        $this->assertTrue($localSendouts->first()->walter_consultant_id != null);
        $this->assertTrue($localSendouts->first()->walter_sendout_id != null);
    }
}
