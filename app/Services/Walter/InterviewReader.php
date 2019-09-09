<?php

namespace App\Services\Walter;

use Carbon\Carbon;
use App\Interview;
use App\Services\Reader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InterviewReader extends Reader
{
    protected $query;

    public function __construct()
    {
        parent::__construct();

        $this->localModel = new Interview;
        $this->primaryKey = 'intID';

        $this->query = DB::connection($this->walterDriver)
            ->table('jobOrder_interview')
            ->select([
                'intID as id',
                'dateCreated as date',
                'consultant',
                'updated_at'
            ]);
    }

    public function getNewRecords()
    {
        $latestInterview = Interview::orderBy('updated_at', 'desc')->first();
        $newRecords = new Collection();

        if (!empty($latestInterview)) {
            $newRecords = collect(
                $this->query
                    ->where('updated_at', '>=', $latestInterview->updated_at->subMinutes(5))
                    ->get()
            );
        }

        return $newRecords;
    }

    protected function getBetweenQuery(Carbon $startDate, Carbon $endDate)
    {
        return $this->query
            ->where('dateCreated', '>=', $startDate)
            ->where('dateCreated', '<=', $endDate);
    }
}
