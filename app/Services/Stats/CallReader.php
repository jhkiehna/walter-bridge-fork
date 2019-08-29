<?php

namespace App\Services\Stats;

use Carbon\Carbon;
use App\FailedItem;
use App\Services\Reader;
use Illuminate\Support\Facades\DB;

class CallReader extends Reader
{
    public function read()
    {
        $calls = $this->getNewCalls();

        if (!$calls->isEmpty()) {
            $calls->each(function ($call) {
                $centralId = $this->translateIntranetUserIdToCentralUserId($call->user_id);

                $localCall = $this->callModel->create([
                    'central_id' => $centralId ?? 1,
                    'intranet_user_id' => $call->user_id,
                    'stats_call_id' => $call->id,
                    'valid' => $call->valid,
                    'dialed_number' => $call->dialed_number,
                    'type' => $call->type,
                    'date' => $call->date,
                    'duration' => $call->duration,
                ]);

                if (!$centralId) {
                    FailedItem::make()->failable()->associate($localCall)->save();
                }
            });
        }
    }

    public function getNewCalls()
    {
        $latestCall = $this->callModel->orderBy('date', 'desc')->first();

        if ($latestCall) {
            $lastReadDate = Carbon::parse($latestCall->date);

            return collect(
                DB::connection($this->statsDriver)
                    ->table('calls')
                    ->select([
                        'id',
                        'user_id',
                        'valid',
                        'dialed_number',
                        'type',
                        'date',
                        'duration',
                        'raw',
                    ])
                    ->whereNotNull('date')
                    ->where('date', '>', $lastReadDate)
                    ->where('raw', '!=', 'NEXUS')
                    ->get()
            );
        }
    }
}
