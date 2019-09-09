<?php

namespace App;

use App\User;
use App\FailedItem;
use App\WalterRecordTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends Model
{
    use SoftDeletes;
    use WalterRecordTrait;

    protected $fillable = [
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'central_id', 'central_id');
    }

    public function failedItems()
    {
        return $this->morphMany(FailedItem::class, 'failable');
    }

    public static function writeWithForeignRecord($email)
    {
        $centralId = self::translateWalterUserIdToCentralUserId($email->walter_id);

        $localSendout = self::updateOrCreate(
            ['walter_email_id' => $email->id],
            [
                'central_id' => $centralId ?? 1,
                'person_email' => $email->participant_email,
                'user_email' => $email->user_email,
                'action' => $email->action,
                'details' => $email->details,
                'date' => $email->date
            ]
        );

        if (!$centralId) {
            FailedItem::make()->failable()->associate($localSendout)->save();
        }
    }
}
