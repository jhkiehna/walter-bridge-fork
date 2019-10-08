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

    public function failedItem()
    {
        return $this->morphOne(FailedItem::class, 'failable');
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
                'type' => $call->type == 'Outgoing' ? 'Outgoing' : 'Incoming',
                'date' => $call->date,
                'duration' => $call->duration,
            ]
        );

        if ($centralId != 1 && ($localCall->wasRecentlyCreated || !empty($localCall->getChanges()))) {
            return $localCall;
        }

        if ($centralId == 1) {
            FailedItem::make()->failable()->associate($localCall)->save();
        }
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
                    'incoming' => $this->type == 'Outgoing' ? false : true,
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
                return $this->getPhoneUtility()->parse("+{$this->preparePhoneNumber(2)}", "");
            }

            return $this->getPhoneUtility()->parse("{$this->preparePhoneNumber(1)}", 'US');
        } catch (\libphonenumber\NumberParseException $e) {
            // \Sentry\configureScope(
            //     function (\Sentry\State\Scope $scope) use ($e): void {
            //         $scope->setExtra('CallModel', json_encode($this));
            //     }
            // );
            // app('sentry')->captureException($e);

            logger()->error("Failed to parse number for call with ID $this->id");
            info($e->getMessage());
        }
    }

    private function preparePhoneNumber(int $subString)
    {
        if ($this->dialed_number == 0) {
            return $this->concatenated_number;
        } else {
            if ($this->type == 'Incoming') {
                return $this->dialed_number;
            } else {
                return substr("{$this->dialed_number}", $subString);
            }
        }
    }
}
