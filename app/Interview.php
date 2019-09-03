<?php

namespace App;

use App\User;
use App\FailedItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interview extends Model
{
    use SoftDeletes;

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

        $localInterview = self::create([
            'central_id' => $centralId ?? 1,
            'walter_consultant_id' => (int) $interview->consultant,
            'walter_interview_id' => $interview->id,
            'date' => $interview->date
        ]);

        if (!$centralId) {
            FailedItem::make()->failable()->associate($localInterview)->save();
        }
    }

    protected static function translateWalterUserIdToCentralUserId($consultantId)
    {
        $user = User::where('walter_id', $consultantId)->first();

        return $user ? $user->central_id : null;
    }
}
