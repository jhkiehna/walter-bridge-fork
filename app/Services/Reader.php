<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;

abstract class Reader
{
    protected $walterDriver;
    protected $statsDriver;

    public $localModel;
    public $primaryKey;

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
                $localRecord = $this->localModel::writeWithForeignRecord($record);

                if ($localRecord) {
                    $localRecord->publishToKafka();
                }
            });
        }
    }

    public function getQuery(?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        if ($startDate && $endDate) {
            return $this->getBetweenQuery($startDate, $endDate);
        }

        return $this->query;
    }
}
