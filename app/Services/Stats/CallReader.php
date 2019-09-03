<?php

namespace App\Services\Stats;

use App\Call;
use Carbon\Carbon;
use App\Services\Reader;
use Illuminate\Support\Facades\DB;

class CallReader extends Reader
{
    public function __construct()
    {
        parent::__construct();

        $this->localModel = new Call;
    }

    public function getNewRecords()
    {
        $latestCall = $this->localModel::orderBy('date', 'desc')->first();

        if ($latestCall) {
            $lastReadDate = Carbon::parse($latestCall->date);

            return collect(
                $this->getQuery()
                    ->whereNotNull('date')
                    ->where('date', '>', $lastReadDate)
                    ->get()
            );
        }
    }

    public function getAll()
    {
        return collect($this->getQuery()->get());
    }

    public function getBetween(Carbon $startDate, Carbon $endDate)
    {
        return collect(
            $this->getQuery()
                ->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate)
                ->get()
        );
    }

    private function getQuery()
    {
        return DB::connection($this->statsDriver)
            ->table('calls')
            ->select([
                'id',
                'user_id',
                'valid',
                'dialed_number',
                'type',
                'date',
                'duration',
                'raw',
            ])
            ->where('raw', '!=', 'NEXUS');
    }
}
