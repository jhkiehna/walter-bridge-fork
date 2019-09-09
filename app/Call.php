<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $fillable = [
        'central_id',
        'intranet_user_id',
        'stats_call_id',
        'valid',
        'dialed_number',
        'type',
        'duration',
        'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'central_id', 'central_id');
    }

    public function failedItems()
    {
        return $this->morphMany(FailedItem::class, 'failable');
    }

    public static function writeWithForeignRecord($call)
    {
        $centralId = self::translateIntranetUserIdToCentralUserId($call->user_id);

        $localCall = self::updateOrCreate(
            ['stats_call_id' => $call->id],
            [
                'central_id' => $centralId ?? 1,
                'intranet_user_id' => $call->user_id,
                'valid' => $call->valid,
                'dialed_number' => $call->dialed_number,
                'type' => $call->type,
                'date' => $call->date,
                'duration' => $call->duration,
                'updated_at' => $call->updated_at
            ]
        );

        if (!$centralId) {
            FailedItem::make()->failable()->associate($localCall)->save();
        }
    }

    protected static function translateIntranetUserIdToCentralUserId($intranetId)
    {
        $user = User::where('intranet_id', $intranetId)->first();

        return $user ? $user->central_id : null;
    }

    public function updateCentralId()
    {
        $user = User::where('intranet_id', $this->intranet_user_id)->first();

        if (empty($user)) {
            return;
        }
        if ($user->id == $this->user->id) {
            return;
        }

        $this->update([
            'central_id' => $user->central_id
        ]);
    }
}
