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
        'concatenated_number',
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
        'dialed_number' => 'integer',
        'concatenated_number' => 'integer',
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

        $concatenated_number = $call->areacode . $call->phone_number;

        $localCall = self::updateOrCreate(
            ['stats_call_id' => $call->id],
            [
                'central_id' => $centralId,
                'intranet_user_id' => $call->user_id,
                'valid' => $call->valid,
                'concatenated_number' => empty($concatenated_number) ? 0 : $concatenated_number,
                'dialed_number' => $call->dialed_number,
                'international' => $call->international,
                'type' => $call->type,
                'date' => $call->date,
                'duration' => $call->duration,
            ]
        );

        if ($centralId != 1 && ($localCall->wasRecentlyCreated || !empty($localCall->getChanges()))) {
            return $localCall;
        }

        FailedItem::make()->failable()->associate($localCall)->save();
    }

    protected static function translateIntranetUserIdToCentralUserId($intranetId)
    {
        $user = User::where('intranet_id', $intranetId)->first();

        return $user ? $user->central_id : 1;
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
        if ($this->dialed_number == 0 && $this->concatenated_number == 0) {
            return;
        }

        try {
            if ($this->international == true) {
                if ($this->dialed_number == 0) {
                    $phoneNumber = $this->concatenated_number;
                } else {
                    $phoneNumber = substr("{$this->dialed_number}", 2);
                }

                return $this->getPhoneUtility()->parse("+$phoneNumber", "");
            }

            if ($this->dialed_number == 0) {
                $phoneNumber = $this->concatenated_number;
            } else {
                $phoneNumber = substr("{$this->dialed_number}", 1);
            }

            return $this->getPhoneUtility()->parse("$phoneNumber", 'US');
        } catch (\libphonenumber\NumberParseException $e) {
            \Sentry\configureScope(
                function (\Sentry\State\Scope $scope) use ($e): void {
                    $scope->setExtra('CallModel', json_encode($this));
                }
            );
            app('sentry')->captureException($e);

            logger()->error("Failed to parse number for call with ID $this->id");
            info($e->getMessage());
        }
    }
}
