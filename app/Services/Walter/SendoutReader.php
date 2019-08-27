<?php

namespace App\Services\Walter;

use Illuminate\Support\Carbon;
use App\Services\Walter\Reader;
use Illuminate\Support\Facades\DB;

class SendoutReader extends Reader
{
    public function read()
    {
        $sendouts = $this->getNewSendouts();

        if (!$sendouts->isEmpty()) {
            $sendouts->each(function ($sendout) {
                $this->sendoutModel->create([
                    'central_id' => $this->translateWalterUserIdToCentralUserId($sendout->Consultant),
                    'date' => $sendout->date
                ]);
            });
        }
    }

    public function getNewSendouts()
    {
        $sendout = $this->sendoutModel->orderBy('date', 'desc')->first();

        if ($sendout) {
            $lastReadDate = Carbon::parse($this->sendoutModel->orderBy('date', 'desc')->first()->date);

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
