<?php

namespace App;

use App\User;
use App\Jobs\PublishKafka;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $fillable = [
        'central_id',
        'intranet_user_id',
        'stats_call_id',
        'valid',
        'dialed_number',
        'international',
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

    public function getPhoneUtilityAttribute()
    {
        return PhoneNumberUtil::getInstance();
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
                'international' => $call->international,
                'type' => $call->type,
                'date' => $call->date,
                'duration' => $call->duration,
                'updated_at' => $call->updated_at
            ]
        );

        if ($centralId) {
            return $localCall;
        }

        FailedItem::make()->failable()->associate($localCall)->save();
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

    public function publishToKafka()
    {
        $libPhoneNumberObject = $this->parseNumber();

        if ($libPhoneNumberObject && $this->validateCall($libPhoneNumberObject)) {
            $callStruct = (object) [
                'type' => 'call',
                'call' => [
                    'id' => $this->stats_call_id,
                    'user_id' => $this->central_id,
                    'participant_number' => $this->phoneUtility->format(
                        $libPhoneNumberObject,
                        PhoneNumberFormat::E164
                    ),
                    'incoming' => $this->type == 'Incoming' ? true : false,
                    'duration' => $this->duration,
                    //translate date or created_at to UTC, from probably EDT
                    'created_at' => $this->date,
                ]
            ];

            PublishKafka::dispatch($callStruct);

            return true;
        }
        return false;
    }

    private function validateCall($libPhoneNumberObject)
    {
        if ($this->duration == 0) {
            return false;
        }

        return $this->phoneUtility->isValidNumber($libPhoneNumberObject);
    }

    private function parseNumber()
    {
        if ($this->international == true) {
            return $this->phoneUtility->parse('+' . substr("{$this->dialed_number}", 2), "");
        }

        return $this->phoneUtility->parse(substr("{$this->dialed_number}", 1), 'US');
    }
}
