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

    public function user()
    {
        return $this->belongsTo(User::class, 'central_id', 'central_id');
    }

    public function failedItems()
    {
        return $this->morphMany(FailedItem::class, 'failable');
    }

    public static function writeWithForeignRecord($interview)
    {
        $centralId = self::translateWalterUserIdToCentralUserId($interview->consultant);

        $localInterview = self::updateOrCreate(
            ['walter_interview_id' => $interview->id,],
            [
                'central_id' => $centralId ?? 1,
                'walter_consultant_id' => (int) $interview->consultant,
                'date' => $interview->date,
                'updated_at' => $interview->updated_at
            ]
        );

        if ($centralId) {
            return $localInterview;
        }

        FailedItem::make()->failable()->associate($localInterview)->save();
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
