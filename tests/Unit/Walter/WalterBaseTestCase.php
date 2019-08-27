<?php

namespace Tests\Walter;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

abstract class WalterBaseTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Artisan::call("migrate:fresh", [
            "--database" => "sqlite_testing",
            "--env" => "testing"
        ]);
        Artisan::call("db:seed", [
            "--database" => "sqlite_testing",
            "--env" => "testing"
        ]);

        Artisan::call("migrate:fresh", [
            "--path" => "tests/Unit/Walter/TestWalterDBMigration",
            "--database" => "walter_test",
            "--env" => "testing"
        ]);

        DB::setDefaultConnection('sqlite_testing');
    }
}
