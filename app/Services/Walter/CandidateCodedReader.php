<?php

namespace App\Services\Walter;

use Carbon\Carbon;
use App\CandidateCoded;
use App\Services\Reader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CandidateCodedReader extends Reader
{
    protected $query;

    public function __construct()
    {
        parent::__construct();

        $this->localModel = new CandidateCoded;
        $this->primaryKey = 'cdid';

        $this->query = DB::connection($this->walterDriver)
            ->table('person_codeDate')
            ->select([
                'cdid as id',
                'dateCoded as date',
                'consultant',
                'updated_at'
            ]);
    }

    public function getNewRecords()
    {
        $latestCandidateCoded = CandidateCoded::orderBy('walter_coded_id', 'desc')->first();
        $newRecords = new Collection();

        if (!empty($latestCandidateCoded)) {
            $newRecords = collect(
                $this->query
                    ->where('updated_at', '>=', $latestCandidateCoded->updated_at->subMinutes(5))
                    ->get()
            );
        }

        return $newRecords;
    }

    public function getBetweenQuery(Carbon $startDate, Carbon $endDate)
    {
        return $this->query
            ->where('dateCoded', '>=', $startDate)
            ->where('dateCoded', '<=', $endDate);
    }
}
