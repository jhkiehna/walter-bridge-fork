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
                    'Consultant' => rand(1, 10),
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
        for ($i = 1; $i <= 10; $i++) {
            factory(User::class)->create([
                'central_id' => $i * 2,
                'walter_id' => $i
            ]);
        }

        $sendoutReader = new SendoutReader;

        $sendoutReader->read();

        $localSendouts = Sendout::all();

        $this->assertFalse($localSendouts->isEmpty());
    }
}
