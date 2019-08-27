<?php

namespace Tests\Unit;

use App\User;
use App\Sendout;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\Walter\SendoutReader;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendoutReaderTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call("db:seed", [
            "--database" => "sqlite_testing",
            "--env" => "testing"
        ]);
        $users = User::all();

        Artisan::call("migrate:fresh", [
            "--path" => "tests/TestWalterDBMigration",
            "--database" => "walter_test",
            "--env" => "testing"
        ]);

        for ($i = 1; $i <= 14; $i++) {
            DB::connection('walter_test')
                ->table('SendOut')
                ->insert([
                    'soid' => $i,
                    'soType' => 2,
                    'DateSent' => Carbon::now()->subDays($i + 1),
                    'Consultant' => $users[$i]->walter_id ?? 1,
                    'firstResume' => true
                ]);
        }

        DB::setDefaultConnection('sqlite_testing');
    }

    public function testItCanGetNewSendouts()
    {
        $sendouts = (new SendoutReader)->getNewSendouts();

        $this->assertFalse($sendouts->isEmpty());
        $this->assertEquals($sendouts->first()->id, 1);
        $this->assertObjectHasAttribute('Consultant', $sendouts->first());
    }

    public function testItCanUseTheReadMethodAndCreateSendoutsInLocalDB()
    {
        (new SendoutReader)->read();
        $localSendouts = Sendout::all();

        $this->assertFalse($localSendouts->isEmpty());
        $this->assertTrue($localSendouts->first()->user != null);
    }
}
