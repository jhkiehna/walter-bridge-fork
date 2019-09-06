<?php

namespace Tests\Unit;

use App\User;
use App\Call;
use Carbon\Carbon;
use Tests\TestCase;
use App\Services\Stats\CallReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CallReaderTest extends TestCase
{
    use RefreshDatabase;

    public $connectionsToTransact = ['sqlite_testing', 'sqlite_stats_test'];

    public function setUp(): void
    {
        parent::setUp();

        $users = User::all();

        for ($i = 1; $i <= 14; $i++) {
            $type = rand(0, 2);

            DB::connection('sqlite_stats_test')
                ->table('calls')
                ->insert([
                    'user_id' => $users[$i]->intranet_id ?? 1,
                    'valid' => true,
                    'dialed_number' => rand(1111111111, 9999999999),
                    'type' => $type == 0 ? 'Incoming' : $type == 1 ? 'Outgoing' : 'Transfer',
                    'date' => Carbon::now()->subDays($i),
                    'duration' => rand(1, 1000),
                    'raw' => ''
                ]);
        }
    }

    public function testItCanGetNewCalls()
    {
        factory(Call::class)->create([
            'date' => Carbon::now()->subWeeks(2),
        ]);

        $calls = (new CallReader)->getNewRecords();

        $this->assertFalse($calls->isEmpty());
        $this->assertEquals($calls->first()->raw, '');
        $this->assertObjectHasAttribute('dialed_number', $calls->first());
    }

    public function testItCanUseTheReadMethodAndCreateCallsInLocalDB()
    {
        Artisan::call("db:seed", [
            "--database" => "sqlite_testing",
            "--env" => "testing"
        ]);
        factory(Call::class)->create([
            'central_id' => 1,
            'date' => Carbon::now()->subWeeks(2),
        ]);

        (new CallReader)->read();
        $localCalls = Call::all();

        $this->assertFalse($localCalls->isEmpty());
        $this->assertTrue($localCalls->first()->user != null);
        $this->assertTrue($localCalls->first()->intranet_user_id != null);
        $this->assertTrue($localCalls->first()->stats_call_id != null);
    }
}
