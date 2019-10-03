<?php

namespace App\Services\Stats;

use App\Call;
use Carbon\Carbon;
use App\Services\Reader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CallReader extends Reader
{
    protected $query;

    public function __construct()
    {
        parent::__construct();

        $this->localModel = new Call;
        $this->primaryKey = 'id';

        $this->query = DB::connection($this->statsDriver)
            ->table('calls')
            ->select([
                'id',
                'user_id',
                'valid',
                'dialed_number',
                'CONCAT(areacode, phone_number) as concatenated_number',
                'international',
                'type',
                'date',
                'duration',
                'raw',
                'updated_at'
            ])
            ->where('raw', '!=', 'NEXUS')
            ->where('type', '!=', 'Transfer');
    }

    public function getNewRecords()
    {
        $latestCall = Call::orderBy('updated_at', 'desc')->first();
        $newRecords = new Collection();

        if (!empty($latestCall)) {
            $newRecords = collect(
                $this->query
                    ->where('updated_at', '>=', $latestCall->updated_at)
                    ->get()
            );
        }

        return $newRecords;
    }

    public function getBetweenQuery(Carbon $startDate, Carbon $endDate)
    {
        return $this->query
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate);
    }
}
