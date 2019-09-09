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

    public $connectionsToTransact = ['sqlite_testing', 'sqlite_walter_test'];

    public function setUp(): void
    {
        parent::setUp();

        $users = User::all();

        for ($i = 1; $i <= 14; $i++) {
            DB::connection('sqlite_walter_test')
                ->table('SendOut')
                ->insert([
                    'soid' => $i,
                    'DateSent' => Carbon::now()->subDays($i),
                    'Consultant' => $users[$i]->walter_id ?? 1,
                    'firstResume' => true,
                    'updated_at' => Carbon::now()->subDays($i)
                ]);
        }
    }

    public function testItCanGetNewSendouts()
    {
        factory(Sendout::class)->create([
            'walter_sendout_id' => 1,
            'updated_at' => Carbon::now()->subWeeks(3)
        ]);

        $sendouts = (new SendoutReader)->getNewRecords();

        $this->assertFalse($sendouts->isEmpty());
        $this->assertEquals($sendouts->first()->id, 1);
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
