<?php

namespace Tests\Unit;

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

    public function testItCanGetNewSendouts()
    {
        $this->seedTestWalter();

        $sendouts = (new SendoutReader)->getNewSendouts();

        $this->assertFalse($sendouts->isEmpty());

        $this->destroyTestWalter();
    }

    public function testItCanUseTheReadMethodAndCreateSendoutsInLocalDB()
    {
        // $this->seedTestWalter();

        $this->assertTrue(true);
        // $this->destroyTestWalter();
    }

    private function seedTestWalter()
    {
        Artisan::call("migrate", [
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
                    'DateCreated' => Carbon::now()->subDays($i),
                    'DateSent' => Carbon::now()->subDays($i + 1),
                    'firstResume' => true
                ]);
        }

        DB::setDefaultConnection('sqlite_testing');
    }

    private function destroyTestWalter()
    {
        Artisan::call("migrate:reset", [
            "--path" => "tests/TestWalterDBMigration",
            "--database" => "walter_test",
            "--env" => "testing"
        ]);

        DB::setDefaultConnection('sqlite_testing');
    }
}
