<?php

namespace App;

use App\User;

trait WalterRecordTrait
{
    protected static function translateWalterUserIdToCentralUserId($consultantId)
    {
        $user = User::where('walter_id', $consultantId)->first();

        return $user ? $user->central_id : null;
    }

    public function updateCentralId()
    {
        $user = User::where('walter_id', $this->walter_consultant_id)->first();

        if (empty($user)) {
            return;
        }
        if ($user->id == $this->user->id) {
            return;
        }

        $this->update([
            'central_id' => $user->central_id
        ]);
    }
}
