<?php

namespace App\Services\Walter;

use App\Sendout;
use Carbon\Carbon;
use App\Services\Reader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SendoutReader extends Reader
{
    protected $query;

    public function __construct()
    {
        parent::__construct();

        $this->localModel = new Sendout;
        $this->primaryKey = 'soid';

        $this->query = DB::connection($this->walterDriver)
            ->table('SendOut')
            ->select([
                'soid as id',
                'dateSent as date',
                'dateCreated',
                'Consultant as consultant',
                'firstResume'
            ])
            ->where('firstResume', 1)
            ->whereNotNull('dateSent');
    }

    public function getNewRecords()
    {
        $latestSendout = Sendout::orderBy('updated_at', 'desc')->first();
        $newRecords = new Collection();

        if (!empty($latestSendout)) {
            $newRecords = collect(
                $this->query
                    ->where('updated_at', '>=', $latestSendout->updated_at->subMinutes(5))
                    ->get()
            );
        }

        return $newRecords;
    }

    public function getBetweenQuery(Carbon $startDate, Carbon $endDate)
    {
        return $this->query
            ->where('dateSent', '>=', $startDate)
            ->where('dateSent', '<=', $endDate);
    }
}
