<?php

namespace App\Services;

use App\Call;
use App\User;
use App\Sendout;
use App\Interview;
use App\CandidateCoded;

abstract class Reader
{
    protected $walterDriver;
    protected $statsDriver;

    private $userModel;
    protected $sendoutModel;
    protected $interviewModel;
    protected $candidateCodedModel;
    protected $callModel;

    public function __construct()
    {
        $this->walterDriver = env('APP_ENV') == 'production' ? 'walter_sqlsrv' : 'sqlite_walter_test';
        $this->statsDriver = env('APP_ENV') == 'production' ? 'mysql_stats' : 'sqlite_stats_test';
        $this->userModel = new User;
        $this->sendoutModel = new Sendout;
        $this->interviewModel = new Interview;
        $this->candidateCodedModel = new CandidateCoded;
        $this->callModel = new Call;
    }

    protected function translateWalterUserIdToCentralUserId($consultantId)
    {
        $user = $this->userModel->where('walter_id', $consultantId)->first();

        return $user ? $user->central_id : null;
    }

    protected function translateIntranetUserIdToCentralUserId($intranetId)
    {
        $user = $this->userModel->where('intranet_id', $intranetId)->first();

        return $user ? $user->central_id : null;
    }
}
