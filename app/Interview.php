<?php

namespace App;

use App\User;
use App\FailedItem;
use App\WalterRecordTrait;
use App\Jobs\PublishKafkaJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interview extends Model
{
    use SoftDeletes;
    use WalterRecordTrait;

    protected $fillable = [
        'central_id',
        'walter_consultant_id',
        'walter_interview_id',
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
        'walter_interview_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'central_id', 'central_id');
    }

    public function failedItem()
    {
        return $this->morphOne(FailedItem::class, 'failable');
    }

    public static function writeWithForeignRecord($interview)
    {
        $centralId = self::translateWalterUserIdToCentralUserId($interview->consultant);

        $localInterview = self::updateOrCreate(
            ['walter_interview_id' => $interview->id,],
            [
                'central_id' => $centralId,
                'walter_consultant_id' => $interview->consultant,
                'date' => $interview->date
            ]
        );

        if ($centralId != 1 && ($localInterview->wasRecentlyCreated || !empty($localInterview->getChanges()))) {
            return $localInterview;
        }

        if ($centralId == 1) {
            FailedItem::make()->failable()->associate($localInterview)->save();
        }
    }

    public function publishToKafka()
    {
        $interviewObject = (object) [
            'type' => 'interview',
            'data' => (object) [
                'id' => $this->walter_interview_id,
                'user_id' => $this->central_id,
                'created_at' => $this->date->toISOString(),
            ]
        ];

        PublishKafkaJob::dispatch($interviewObject);
    }
}
