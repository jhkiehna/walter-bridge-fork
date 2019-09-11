<?php

namespace App;

use App\User;
use App\WalterRecordTrait;
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

    public function user()
    {
        return $this->belongsTo(User::class, 'central_id', 'central_id');
    }

    public function failedItems()
    {
        return $this->morphMany(FailedItem::class, 'failable');
    }

    public static function writeWithForeignRecord($candidateCoded)
    {
        $centralId = self::translateWalterUserIdToCentralUserId($candidateCoded->consultant);

        $localCandidateCoded = self::updateOrCreate(
            ['walter_coded_id' => $candidateCoded->id],
            [
                'central_id' => $centralId ?? 1,
                'walter_consultant_id' => (int) $candidateCoded->consultant,
                'date' => $candidateCoded->date,
                'updated_at' => $candidateCoded->updated_at
            ]
        );

        if ($centralId) {
            return $localCandidateCoded;
        }

        FailedItem::make()->failable()->associate($localCandidateCoded)->save();
    }
}
