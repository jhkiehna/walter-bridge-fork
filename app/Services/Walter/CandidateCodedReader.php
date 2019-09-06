<?php

namespace App\Services\Walter;

use Carbon\Carbon;
use App\CandidateCoded;
use App\Services\Reader;
use Illuminate\Support\Facades\DB;

class CandidateCodedReader extends Reader
{
    public function __construct()
    {
        parent::__construct();

        $this->localModel = new CandidateCoded;
    }

    public function getNewRecords()
    {
        $latestCandidateCoded = $this->localModel::orderBy('walter_coded_id', 'desc')->first();

        if ($latestCandidateCoded) {
            return collect(
                $this->getQuery()
                    ->where('cdid', '>', $latestCandidateCoded->walter_coded_id)
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
                ->where('dateCoded', '>=', $startDate)
                ->where('dateCoded', '<=', $endDate)
                ->get()
        );
    }

    private function getQuery()
    {
        return DB::connection($this->walterDriver)
            ->table('person_codeDate')
            ->select([
                'cdid as id',
                'dateCoded as date',
                'consultant'
            ]);
    }
}
