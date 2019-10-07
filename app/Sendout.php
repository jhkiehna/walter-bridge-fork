<?php

namespace App;

use App\User;
use App\FailedItem;
use App\WalterRecordTrait;
use App\Jobs\PublishKafkaJob;
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

    protected $dates = [
        'created_at',
        'updated_at',
        'date',
    ];

    protected $casts = [
        'id' => 'integer',
        'central_id' => 'integer',
        'walter_consultant_id' => 'integer',
        'walter_sendout_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'central_id', 'central_id');
    }

    public function failedItem()
    {
        return $this->morphOne(FailedItem::class, 'failable');
    }

    public static function writeWithForeignRecord($sendout)
    {
        $centralId = self::translateWalterUserIdToCentralUserId($sendout->consultant);

        $localSendout = self::updateOrCreate(
            ['walter_sendout_id' => $sendout->id],
            [
                'central_id' => $centralId,
                'walter_consultant_id' => $sendout->consultant,
                'date' => $sendout->date
            ]
        );

        if ($centralId != 1 && ($localSendout->wasRecentlyCreated || !empty($localSendout->getChanges()))) {
            return $localSendout;
        }

        if ($centralId == 1) {
            FailedItem::make()->failable()->associate($localSendout)->save();
        }
    }

    public function publishToKafka()
    {
        $sendoutObject = (object) [
            'type' => 'sendout',
            'data' => (object) [
                'id' => $this->walter_sendout_id,
                'user_id' => $this->central_id,
                'created_at' => $this->date->toISOString(),
            ]
        ];

        PublishKafkaJob::dispatch($sendoutObject);
    }
}
