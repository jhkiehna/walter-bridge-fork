<?php

namespace App\Services\Walter;

use Carbon\Carbon;
use App\FailedItem;
use App\Services\Walter\Reader;
use Illuminate\Support\Facades\DB;

class CandidateCodedReader extends Reader
{
    public function read()
    {
        $candidatesCoded = $this->getNewCandidatesCoded();

        if (!$candidatesCoded->isEmpty()) {
            $candidatesCoded->each(function ($candidateCoded) {
                $centralId = $this->translateWalterUserIdToCentralUserId($candidateCoded->consultant);

                $localCandidateCoded = $this->candidateCodedModel->create([
                    'central_id' => $centralId ?? 1,
                    'walter_consultant_id' => (int) $candidateCoded->consultant,
                    'walter_coded_id' => $candidateCoded->id,
                    'date' => $candidateCoded->date
                ]);

                if (!$centralId) {
                    FailedItem::make()->failable()->associate($localCandidateCoded)->save();
                }
            });
        }
    }

    public function getNewCandidatesCoded()
    {
        $latestCandidateCoded = $this->candidateCodedModel->orderBy('walter_coded_id', 'desc')->first();

        if ($latestCandidateCoded) {
            return collect(
                DB::connection($this->walterDriver)
                    ->table('person_codeDate')
                    ->select([
                        'cdid as id',
                        'dateCoded as date',
                        'consultant'
                    ])
                    ->where('cdid', '>', $latestCandidateCoded->walter_coded_id)
                    ->get()
            );
        }
    }
}
