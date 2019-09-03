<?php

namespace App\Services;

use Illuminate\Support\Facades\App;

abstract class Reader
{
    protected $walterDriver;
    protected $statsDriver;

    public $localModel;

    public function __construct()
    {
        $this->walterDriver = App::environment() == 'production' ? 'sqlsrv_walter' : 'sqlite_walter_test';
        $this->statsDriver = App::environment() == 'production' ? 'mysql_stats' : 'sqlite_stats_test';
    }

    public function read()
    {
        $records = $this->getNewRecords();

        if (!$records->isEmpty()) {
            $records->each(function ($record) {
                $this->localModel::writeWithForeignRecord($record);
            });
        }
    }
}
