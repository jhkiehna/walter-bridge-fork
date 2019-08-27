<?php

namespace App\Services\Walter;

use Carbon\Carbon;
use App\FailedItem;
use App\Services\Walter\Reader;
use Illuminate\Support\Facades\DB;

class SendoutReader extends Reader
{
    public function read()
    {
        $sendouts = $this->getNewSendouts();

        if (!$sendouts->isEmpty()) {
            $sendouts->each(function ($sendout) {
                $centralId = $this->translateWalterUserIdToCentralUserId($sendout->Consultant);

                $localSendout = $this->sendoutModel->create([
                    'central_id' => $centralId ?? 1,
                    'walter_consultant_id' => (int) $sendout->Consultant,
                    'walter_sendout_id' => $sendout->id,
                    'date' => $sendout->date
                ]);

                if (!$centralId) {
                    FailedItem::make()->failable()->associate($localSendout)->save();
                }
            });
        }
    }

    public function getNewSendouts()
    {
        $latestSendout = $this->sendoutModel->orderBy('date', 'desc')->first();

        if ($latestSendout) {
            $lastReadDate = Carbon::parse($latestSendout->date);

            return collect(
                DB::connection($this->walterDriver)
                    ->table('SendOut')
                    ->select([
                        'soid as id',
                        'dateSent as date',
                        'consultant',
                        'firstResume'
                    ])
                    ->where('firstResume', 1)
                    ->whereNotNull('dateSent')
                    ->where('dateSent', '>', $lastReadDate)
                    ->get()
            );
        }
    }
}
