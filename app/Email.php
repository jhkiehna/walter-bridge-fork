<?php

namespace App;

use App\User;
use App\FailedItem;
use App\WalterRecordTrait;
use App\Jobs\PublishKafkaJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends Model
{
    use SoftDeletes;
    use WalterRecordTrait;

    protected $fillable = [
        'walter_email_id',
        'central_id',
        'participant_email',
        'user_email',
        'action',
        'details',
        'date'
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

        $localEmail = self::updateOrCreate(
            ['walter_email_id' => $email->id],
            [
                'central_id' => $centralId ?? 1,
                'participant_email' => $email->participant_email,
                'user_email' => $email->user_email,
                'action' => $email->action,
                'details' => $email->details,
                'date' => $email->date
            ]
        );

        if ($centralId) {
            return $localEmail;
        }

        FailedItem::make()->failable()->associate($localEmail)->save();
    }

    public function publishToKafka()
    {
        $emailObject = (object) [
            'type' => 'email',
            'email' => (object) [
                'id' => $this->walter_email_id,
                'user_id' => $this->central_id,
                'participant_email' => $this->participant_email,
                'incoming' => $this->action == 2 ? true : false,
                'body' => $this->details,
                'created_at' => $this->date->toISOString(),
            ]
        ];

        PublishKafkaJob::dispatch($emailObject);
    }
}
