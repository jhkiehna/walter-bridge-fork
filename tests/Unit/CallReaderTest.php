<?php

namespace Tests\Unit;

use App\User;
use App\Call;
use Carbon\Carbon;
use Tests\TestCase;
use App\FailedItem;
use App\Services\Stats\CallReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
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
            $type = rand(0, 1);

            DB::connection('sqlite_stats_test')
                ->table('calls')
                ->insert([
                    'user_id' => $users[$i]->intranet_id ?? 1,
                    'valid' => true,
                    'areacode' => rand(111, 999),
                    'phone_number' => rand(1111111, 9999999),
                    'dialed_number' => rand(1111111111, 9999999999),
                    'international' => false,
                    'type' => $type == 0 ? 'Incoming' : 'Outgoing',
                    'date' => Carbon::now()->subDays($i),
                    'duration' => rand(1, 1000),
                    'raw' => '',
                    'updated_at' => Carbon::now()->subDays($i)
                ]);
        }
    }

    public function testItCanGetNewCalls()
    {
        factory(Call::class)->create([
            'updated_at' => Carbon::now()->subWeeks(3),
        ]);

        $calls = (new CallReader)->getNewRecords();

        $this->assertFalse($calls->isEmpty());
        $this->assertEquals($calls->first()->raw, '');
        $this->assertObjectHasAttribute('dialed_number', $calls->first());
    }

    public function testItCanUseTheReadMethodAndCreateCallsInLocalDB()
    {
        Artisan::call("db:seed", [
            "--class" => "UserTableSeeder",
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

    public function testItCanUpdateExistingLocalCallsWhenTheyAreUpdatedInStats()
    {
        Artisan::call("db:seed", [
            "--class" => "UserTableSeeder",
            "--database" => "sqlite_testing",
            "--env" => "testing"
        ]);
        factory(Call::class)->create([
            'central_id' => 1,
            'stats_call_id' => 1,
            'updated_at' => Carbon::now()->subWeeks(2),
        ]);
        DB::connection('sqlite_stats_test')
            ->table('calls')
            ->where('id', 1)
            ->update([
                'dialed_number' => 5555555555,
                'type' => 'Incoming',
                'duration' => 0,
                'updated_at' => Carbon::today()
            ]);

        (new CallReader)->read();
        $localUpdatedCall = Call::where('stats_call_id', 1)->first();

        $this->assertEquals($localUpdatedCall->dialed_number, 5555555555);
        $this->assertEquals($localUpdatedCall->type, 'Incoming');
        $this->assertEquals($localUpdatedCall->duration, 0);
    }

    public function testItCanCreateFailedItems()
    {
        factory(User::class)->create([
            'central_id' => 1
        ]);
        factory(Call::class)->create([
            'central_id' => 1,
            'updated_at' => Carbon::now()->subWeeks(3),
        ]);

        (new CallReader)->read();

        $failed = FailedItem::where('failable_type', 'App\Call')->get();

        $this->assertFalse($failed->isEmpty());
    }

    public function testOnlyRecentlyCreatedOrUpdatedCallsAreReturnedFromWriteWithForeignRecord()
    {
        Artisan::call("db:seed", [
            "--class" => "UserTableSeeder",
            "--database" => "sqlite_testing",
            "--env" => "testing"
        ]);
        DB::connection('sqlite_stats_test')
            ->table('calls')
            ->truncate();
        DB::connection('sqlite_stats_test')
            ->table('calls')
            ->insert([
                [
                    'user_id' => 2,
                    'valid' => true,
                    'areacode' => 828,
                    'phone_number' => 1111111,
                    'dialed_number' => 18281111111,
                    'international' => false,
                    'type' => 'Incoming',
                    'date' => Carbon::now()->subDays(1),
                    'duration' => 50,
                    'raw' => '',
                    'updated_at' => Carbon::now()->subDays(1)
                ],
                [
                    'user_id' => 2,
                    'valid' => true,
                    'areacode' => rand(111, 999),
                    'phone_number' => rand(1111111, 9999999),
                    'dialed_number' => rand(1111111111, 9999999999),
                    'international' => false,
                    'type' => 'Incoming',
                    'date' => Carbon::now()->subDays(1),
                    'duration' => rand(1, 1000),
                    'raw' => '',
                    'updated_at' => Carbon::now()->subDays(1)
                ], [
                    'user_id' => 2,
                    'valid' => true,
                    'areacode' => rand(111, 999),
                    'phone_number' => rand(1111111, 9999999),
                    'dialed_number' => rand(1111111111, 9999999999),
                    'international' => false,
                    'type' => 'Incoming',
                    'date' => Carbon::now()->subDays(1),
                    'duration' => rand(1, 1000),
                    'raw' => '',
                    'updated_at' => Carbon::now()->subDays(1)
                ]
            ]);

        factory(Call::class)->create([
            'central_id' => 2,
            'stats_call_id' => 1,
            'intranet_user_id' => 2,
            'valid' => true,
            'dialed_number' => 18281111111,
            'concatenated_number' => 8281111111,
            'international' => false,
            'type' => 'Incoming',
            'duration' => 50,
            'date' => Carbon::now()->subDays(1),
        ]);
        factory(Call::class)->create([
            'central_id' => 2,
            'stats_call_id' => 2,
            'type' => 'Outgoing',
        ]);

        $foreignCalls = DB::connection('sqlite_stats_test')
            ->table('calls')
            ->get();

        $localRecord1 = Call::writeWithForeignRecord($foreignCalls[0]);
        $localRecord2 = Call::writeWithForeignRecord($foreignCalls[1]);
        $localRecord3 = Call::writeWithForeignRecord($foreignCalls[2]);

        $this->assertNull($localRecord1);
        $this->assertNotNull($localRecord2);
        $this->assertTrue(!empty($localRecord2->getChanges()));
        $this->assertTrue(!$localRecord2->wasRecentlyCreated);
        $this->assertNotNull($localRecord3);
        $this->assertTrue($localRecord3->wasRecentlyCreated);
    }
}
