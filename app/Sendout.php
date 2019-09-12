<?php

namespace App;

use App\User;
use App\FailedItem;
use App\WalterRecordTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sendout extends Model
{
    use SoftDeletes;
    use WalterRecordTrait;

    protected $fillable = [
        'central_id',
        'walter_consultant_id',
        'walter_sendout_id',
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

    public static function writeWithForeignRecord($sendout)
    {
        $centralId = self::translateWalterUserIdToCentralUserId($sendout->consultant);

        $localSendout = self::updateOrCreate(
            ['walter_sendout_id' => $sendout->id],
            [
                'central_id' => $centralId ?? 1,
                'walter_consultant_id' => (int) $sendout->consultant,
                'date' => $sendout->date,
                'updated_at' => $sendout->updated_at
            ]
        );

        if ($centralId) {
            return $localSendout;
        }

        FailedItem::make()->failable()->associate($localSendout)->save();
    }

    public function publishToKafka()
    {
        $sendoutObject = (object) [
            'type' => 'sendout',
            'sendout' => (object) [
                'id' => $this->walter_sendout_id,
                'user_id' => $this->central_id,
                'created_at' => $this->date->toISOString(),
            ]
        ];

        PublishKafka::dispatch($sendoutObject);
    }
}
