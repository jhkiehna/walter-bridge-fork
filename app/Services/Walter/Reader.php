<?php

namespace App\Services\Walter;

use App\User;
use App\Sendout;

abstract class Reader
{
    protected $walterDriver;

    private $userModel;
    protected $sendoutModel;

    public function __construct()
    {
        $this->walterDriver = env('APP_ENV') == 'production' ? 'walter_sqlsrv' : 'walter_test';
        $this->userModel = new User;
        $this->sendoutModel = new Sendout;
    }

    protected function translateWalterUserIdToCentralUserId($walterId)
    {
        $user = $this->userModel->where('walter_id', $walterId)->first();

        return $user->central_id;
    }
}
