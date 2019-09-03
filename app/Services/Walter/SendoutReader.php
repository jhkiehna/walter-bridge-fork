<?php

namespace App\Services\Walter;

use App\Sendout;
use Carbon\Carbon;
use App\Services\Reader;
use Illuminate\Support\Facades\DB;

class SendoutReader extends Reader
{
    public function __construct()
    {
        parent::__construct();

        $this->localModel = new Sendout;
    }

    public function getNewRecords()
    {
        $latestSendout = $this->localModel::orderBy('date', 'desc')->first();

        if ($latestSendout) {
            $lastReadDate = Carbon::parse($latestSendout->date);

            return collect(
                $this->getQuery()
                    ->where('dateSent', '>', $lastReadDate)
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
                ->where('dateSent', '>=', $startDate)
                ->where('dateSent', '<=', $endDate)
                ->get()
        );
    }

    private function getQuery()
    {
        return DB::connection($this->walterDriver)
            ->table('SendOut')
            ->select([
                'soid as id',
                'dateSent as date',
                'Consultant as consultant',
                'firstResume'
            ])
            ->where('firstResume', 1)
            ->whereNotNull('dateSent');
    }
}
