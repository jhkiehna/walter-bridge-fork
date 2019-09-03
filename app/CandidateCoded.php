<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidateCoded extends Model
{
    use SoftDeletes;

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

        $localCandidateCoded = self::create([
            'central_id' => $centralId ?? 1,
            'walter_consultant_id' => (int) $candidateCoded->consultant,
            'walter_coded_id' => $candidateCoded->id,
            'date' => $candidateCoded->date
        ]);

        if (!$centralId) {
            FailedItem::make()->failable()->associate($localCandidateCoded)->save();
        }
    }

    protected static function translateWalterUserIdToCentralUserId($consultantId)
    {
        $user = User::where('walter_id', $consultantId)->first();

        return $user ? $user->central_id : null;
    }
}
