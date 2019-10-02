<?php

namespace Tests\Unit;

use App\Call;
use Carbon\Carbon;
use Tests\TestCase;
use App\Jobs\PublishKafkaJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;

class CallTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testItCanPublishACallWithAnInternationalNumberToKafka()
    {
        $this->expectsJobs(PublishKafkaJob::class);
        $call = factory(Call::class)->states('international')->create([
            'dialed_number' => 11441946695420
        ]);

        $isSuccess = $call->publishToKafka();

        $this->assertTrue($isSuccess);
    }

    public function testItCanPublishACallWithANationalNumberToKafka()
    {
        $this->expectsJobs(PublishKafkaJob::class);
        $call = factory(Call::class)->states('national')->create([
            'dialed_number' => 18282519900
        ]);

        $isSuccess = $call->publishToKafka();

        $this->assertTrue($isSuccess);
    }

    public function testItCantPublishACallThatDoesntPassValidationOnCallModel()
    {
        $this->doesntExpectJobs(PublishKafkaJob::class);
        $invalidCall1 = factory(Call::class)->states('national')->create([
            'dialed_number' => 11111111111
        ]);
        $invalidCall2 = factory(Call::class)->states('national')->create([
            'dialed_number' => 18282519900,
            'duration' => 0
        ]);

        $isSuccess1 = $invalidCall1->publishToKafka();
        $isSuccess2 = $invalidCall2->publishToKafka();

        $this->assertFalse($isSuccess1);
        $this->assertFalse($isSuccess2);
    }

    public function testCallsParseNumberAccuratelyGetsTheLocation()
    {
        $geocoder = PhoneNumberOfflineGeocoder::getInstance();
        $reflection_class = new \ReflectionClass(Call::class);
        $reflection_method = $reflection_class->getMethod("parseNumber");
        $reflection_method->setAccessible(true);

        $call1 = factory(Call::class)->states('international')->create([
            'dialed_number' => 11441946695420
        ]);
        $call2 = factory(Call::class)->states('international')->create([
            'dialed_number' => 1133140976300
        ]);
        $libPhoneNumberObject1 = $reflection_method->invoke($call1, null);
        $libPhoneNumberObject2 = $reflection_method->invoke($call2, null);

        $countryName1 = $geocoder->getDescriptionForNumber($libPhoneNumberObject1, 'en_US', 'US');
        $countryName2 = $geocoder->getDescriptionForNumber($libPhoneNumberObject2, 'en_US', 'US');

        $this->assertEquals($countryName1, "United Kingdom");
        $this->assertEquals($countryName2, "France");
    }

    public function testPublishToKafkaMethodCreatesTheCorrectObject()
    {
        Queue::fake();

        factory(Call::class)->states('national')->create([
            'dialed_number' => 18282519900,
            'type' => 'Incoming',
            'duration' => 50,
            'date' => Carbon::now(),
        ]);

        $call = Call::first();

        $expectedCallObject = (object) [
            'type' => 'call',
            'data' => (object) [
                'id' => $call->stats_call_id,
                'user_id' => $call->central_id,
                'participant_number' => '+18282519900',
                'incoming' => true,
                'duration' => 50,
                'created_at' => $call->date->toISOString(),
            ]
        ];

        $call->publishToKafka();

        Queue::assertPushed(PublishKafkaJob::class, function ($job) use ($expectedCallObject) {
            if (json_encode($job->objectToPublish) == json_encode($expectedCallObject)) {
                return true;
            } else {
                return false;
            }
        });
    }

    public function testThatWhenPhoneNumberParserThrowsExceptionItIsCaught()
    {
        $reflection_class = new \ReflectionClass(Call::class);
        $reflection_method = $reflection_class->getMethod("parseNumber");
        $reflection_method->setAccessible(true);

        $badCall = factory(Call::class)->states('international')->create([
            'dialed_number' => ''
        ]);
        $goodCall = factory(Call::class)->states('international')->create([
            'dialed_number' => 1133140976300
        ]);
        $badLibPhoneNumberObject = $reflection_method->invoke($badCall, null);
        $goodLibPhoneNumberObject = $reflection_method->invoke($goodCall, null);

        $this->assertNull($badLibPhoneNumberObject);
        $this->assertNotNull($goodLibPhoneNumberObject);
    }
}
