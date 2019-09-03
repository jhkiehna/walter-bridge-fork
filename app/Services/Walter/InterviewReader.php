<?php

namespace App\Services\Walter;

use Carbon\Carbon;
use App\Interview;
use App\Services\Reader;
use Illuminate\Support\Facades\DB;

class InterviewReader extends Reader
{
    public function __construct()
    {
        parent::__construct();

        $this->localModel = new Interview;
    }

    public function getNewRecords()
    {
        $latestInterview = $this->localModel::orderBy('walter_interview_id', 'desc')->first();

        if ($latestInterview) {
            return collect(
                $this->getQuery()
                    ->where('intID', '>', $latestInterview->walter_interview_id)
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
                ->where('dateCreated', '>=', $startDate)
                ->where('dateCreated', '<=', $endDate)
                ->get()
        );
    }

    private function getQuery()
    {
        return DB::connection($this->walterDriver)
            ->table('jobOrder_interview')
            ->select([
                'intID as id',
                'dateCreated as date',
                'consultant'
            ]);
    }
}
