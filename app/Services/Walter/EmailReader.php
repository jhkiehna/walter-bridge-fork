<?php

namespace App\Services\Walter;

use Carbon\Carbon;
use App\Email;
use App\Services\Reader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmailReader extends Reader
{
    protected $query;

    public function __construct()
    {
        parent::__construct();

        $this->localModel = new Email;
        $this->primaryKey = 'RcID';

        $this->query = DB::connection($this->walterDriver)
            ->table("recordCard")
            ->join("person_recordCard", function ($join) {
                $join->on("recordCard.RcID", "=", "person_recordCard.record")
                    ->whereIn("recordCard.action", [1, 2]);
            })
            ->join("person_email", "person_recordCard.person", "=", "person_email.person")
            ->join("emailAddress", "emailAddress.emid", "=", "person_email.email")
            ->join("users", "users.uID", "=", "recordCard.userID")
            ->select([
                'recordCard.RcID as id',
                'recordCard.date as date',
                'recordCard.details as details',
                'recordCard.action as action',
                'emailAddress.emailAddress as participant_email',
                'users.emailAddress as user_email',
                'users.uID as walter_id'
            ]);
    }

    public function getNewRecords()
    {
        $latestEmail = Email::orderBy('updated_at', 'desc')->first();
        $newRecords = new Collection();

        if (!empty($latestEmail)) {
            $newRecords = collect(
                $this->query
                    ->where('recordCard.updated_at', '>=', $latestEmail->updated_at->subMinutes(5))
                    ->get()
            );
        }

        return $newRecords;
    }

    protected function getBetweenQuery(Carbon $startDate, Carbon $endDate)
    {
        return $this->query
            ->where('recordCard.date', '>=', $startDate)
            ->where('recordCard.date', '<=', $endDate);
    }
}
