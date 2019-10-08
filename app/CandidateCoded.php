<?php

namespace App;

use App\User;
use App\WalterRecordTrait;
use App\Jobs\PublishKafkaJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidateCoded extends Model
{
    use SoftDeletes;
    use WalterRecordTrait;

    protected $table = 'candidates_coded';

    protected $fillable = [
        'central_id',
        'walter_consultant_id',
        'walter_coded_id',
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
        'walter_coded_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'central_id', 'central_id');
    }

    public function failedItem()
    {
        return $this->morphOne(FailedItem::class, 'failable');
    }

    public static function writeWithForeignRecord($candidateCoded)
    {
        $centralId = self::translateWalterUserIdToCentralUserId($candidateCoded->consultant);

        $localCandidateCoded = self::updateOrCreate(
            ['walter_coded_id' => $candidateCoded->id],
            [
                'central_id' => $centralId,
                'walter_consultant_id' => $candidateCoded->consultant,
                'date' => $candidateCoded->date
            ]
        );

        if ($centralId != 1 && ($localCandidateCoded->wasRecentlyCreated || !empty($localCandidateCoded->getChanges()))) {
            return $localCandidateCoded;
        }

        if ($centralId == 1) {
            FailedItem::make()->failable()->associate($localCandidateCoded)->save();
        }
    }

    public function publishToKafka()
    {
        $candidateCodedObject = (object) [
            'type' => 'candidate-coded',
            'data' => (object) [
                'id' => $this->walter_coded_id,
                'user_id' => $this->central_id,
                'created_at' => $this->date->toISOString(),
            ]
        ];

        PublishKafkaJob::dispatch($candidateCodedObject);
    }
}
