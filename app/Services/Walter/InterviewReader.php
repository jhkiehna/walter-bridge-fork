<?php

namespace App\Services\Walter;

use Carbon\Carbon;
use App\FailedItem;
use App\Services\Walter\Reader;
use Illuminate\Support\Facades\DB;

class InterviewReader extends Reader
{
    public function read()
    {
        $interviews = $this->getNewInterviews();

        if (!$interviews->isEmpty()) {
            $interviews->each(function ($interview) {
                $centralId = $this->translateWalterUserIdToCentralUserId($interview->consultant);

                $localInterview = $this->interviewModel->create([
                    'central_id' => $centralId ?? 1,
                    'walter_consultant_id' => (int) $interview->consultant,
                    'walter_interview_id' => $interview->id,
                    'date' => $interview->date
                ]);

                if (!$centralId) {
                    FailedItem::make()->failable()->associate($localInterview)->save();
                }
            });
        }
    }

    public function getNewInterviews()
    {
        $latestInterview = $this->interviewModel->orderBy('walter_interview_id', 'desc')->first();

        if ($latestInterview) {
            return collect(
                DB::connection($this->walterDriver)
                    ->table('jobOrder_interview')
                    ->select([
                        'intID as id',
                        'dateCreated as date',
                        'consultant'
                    ])
                    ->where('intID', '>', $latestInterview->walter_interview_id)
                    ->get()
            );
        }
    }
}
