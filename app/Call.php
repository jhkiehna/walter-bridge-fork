<?php

namespace App;

use App\User;
use App\Jobs\PublishKafkaJob;
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

    protected $dates = [
        'created_at',
        'updated_at',
        'date',
    ];

    protected $casts = [
        'id' => 'integer',
        'central_id' => 'integer',
        'intranet_user_id' => 'integer',
        'stats_call_id' => 'integer',
        'duration' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'central_id', 'central_id');
    }

    public function failedItems()
    {
        return $this->morphMany(FailedItem::class, 'failable');
    }

    public function getPhoneUtility()
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
            $callObject = (object) [
                'type' => 'call',
                'data' => (object) [
                    'id' => $this->stats_call_id,
                    'user_id' => $this->central_id,
                    'participant_number' => $this->getPhoneUtility()->format(
                        $libPhoneNumberObject,
                        PhoneNumberFormat::E164
                    ),
                    'incoming' => $this->type == 'Incoming' ? true : false,
                    'duration' => $this->duration,
                    'created_at' => $this->date->toISOString(),
                ]
            ];

            PublishKafkaJob::dispatch($callObject);

            return true;
        }
        return false;
    }

    private function validateCall($libPhoneNumberObject)
    {
        if ($this->duration == 0) {
            return false;
        }

        return $this->getPhoneUtility()->isValidNumber($libPhoneNumberObject);
    }

    private function parseNumber()
    {
        try {
            if ($this->international == true) {
                return $this->getPhoneUtility()->parse('+' . substr("{$this->dialed_number}", 2), "");
            }

            return $this->getPhoneUtility()->parse(substr("{$this->dialed_number}", 1), 'US');
        } catch (\Throwable $e) {
            logger()->error("Failed to parse number for call with ID $this->id");
            info($e->getMessage());
        }
    }
}
