<?php

namespace Tests\Unit;

use App\Call;
use Carbon\Carbon;
use Tests\TestCase;
use App\Jobs\PublishKafkaJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;
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

    public function testPublishToKafkaMethodCreatesTheCorrectObjectForIncomingInternationalNumber()
    {
        Queue::fake();

        factory(Call::class)->states('international')->create([
            'dialed_number' => 441946695420,
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
                'participant_number' => '+441946695420',
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

    public function testPublishToKafkaMethodCreatesTheCorrectObjectForOutgoingInternationalNumber()
    {
        Queue::fake();

        factory(Call::class)->states('international')->create([
            'dialed_number' => 11441946695420,
            'type' => 'Outgoing',
            'duration' => 50,
            'date' => Carbon::now(),
        ]);

        $call = Call::first();

        $expectedCallObject = (object) [
            'type' => 'call',
            'data' => (object) [
                'id' => $call->stats_call_id,
                'user_id' => $call->central_id,
                'participant_number' => '+441946695420',
                'incoming' => false,
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

    public function testPublishToKafkaMethodCreatesTheCorrectObjectForIncomingNationalNumber()
    {
        Queue::fake();

        factory(Call::class)->states('national')->create([
            'dialed_number' => 8282519900,
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

    public function testPublishToKafkaMethodCreatesTheCorrectObjectForOutgoingNationalNumber()
    {
        Queue::fake();

        factory(Call::class)->states('national')->create([
            'dialed_number' => 18282519900,
            'type' => 'Outgoing',
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
                'incoming' => false,
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
            'dialed_number' => '',
            'concatenated_number' => 1
        ]);
        $goodCall = factory(Call::class)->states('international')->create([
            'dialed_number' => 1133140976300
        ]);
        $badLibPhoneNumberObject = $reflection_method->invoke($badCall, null);
        $goodLibPhoneNumberObject = $reflection_method->invoke($goodCall, null);

        $this->assertNull($badLibPhoneNumberObject);
        $this->assertNotNull($goodLibPhoneNumberObject);
    }

    public function testThatParseNumberUsesTheConcatenatedNumberWhenDialedNumberIsZero()
    {
        $reflection_class = new \ReflectionClass(Call::class);
        $reflection_method = $reflection_class->getMethod("parseNumber");
        $reflection_method->setAccessible(true);

        $call = factory(Call::class)->states('international')->create([
            'dialed_number' => 0,
            'concatenated_number' => 3140976300
        ]);
        $libPhoneNumberObject = $reflection_method->invoke($call, null);

        $this->assertNotNull($libPhoneNumberObject);
    }

    public function testThatNationalNumberParsesCorrectlyWhenUsingConcatenatedNumber()
    {
        $reflection_class = new \ReflectionClass(Call::class);
        $reflection_method = $reflection_class->getMethod("parseNumber");
        $reflection_method->setAccessible(true);

        $call = factory(Call::class)->states('national')->create([
            'dialed_number' => 0,
            'concatenated_number' => 8282519900
        ]);
        $libPhoneNumberObject = $reflection_method->invoke($call, null);

        $this->assertNotNull($libPhoneNumberObject);
    }

    public function testThatInternationalNumberParsesCorrectlyWhenUsingConcatenatedNumber()
    {
        $reflection_class = new \ReflectionClass(Call::class);
        $reflection_method = $reflection_class->getMethod("parseNumber");
        $reflection_method->setAccessible(true);

        $call = factory(Call::class)->states('international')->create([
            'dialed_number' => 0,
            'concatenated_number' => 2675805434
        ]);
        $libPhoneNumberObject = $reflection_method->invoke($call, null);

        $this->assertNotNull($libPhoneNumberObject);
    }

    public function testPublishToKafkaMethodCreatesTheCorrectObjectWithConcatenatedNumberForNationalCall()
    {
        Queue::fake();

        factory(Call::class)->states('national')->create([
            'dialed_number' => 0,
            'concatenated_number' => 8282519900,
            'type' => 'Incoming',
            'duration' => 50,
            'date' => Carbon::now(),
        ]);

        $nationalCall = Call::first();

        $expectedNationalCallObject = (object) [
            'type' => 'call',
            'data' => (object) [
                'id' => $nationalCall->stats_call_id,
                'user_id' => $nationalCall->central_id,
                'participant_number' => '+18282519900',
                'incoming' => true,
                'duration' => 50,
                'created_at' => $nationalCall->date->toISOString(),
            ]
        ];

        $nationalCall->publishToKafka();

        Queue::assertPushed(PublishKafkaJob::class, function ($job) use ($expectedNationalCallObject) {
            if (json_encode($job->objectToPublish) == json_encode($expectedNationalCallObject)) {
                return true;
            } else {
                return false;
            }
        });
    }

    public function testOnlyRecentlyCreatedOrUpdatedCallsAreReturnedFromWriteWithForeignRecord()
    {
        Artisan::call("db:seed", [
            "--class" => "UserTableSeeder",
            "--database" => "sqlite_testing",
            "--env" => "testing"
        ]);
        DB::connection('sqlite_stats_test')
            ->table('calls')
            ->truncate();
        DB::connection('sqlite_stats_test')
            ->table('calls')
            ->insert([
                [
                    'user_id' => 2,
                    'valid' => true,
                    'areacode' => 828,
                    'phone_number' => 1111111,
                    'dialed_number' => 18281111111,
                    'international' => false,
                    'type' => 'Incoming',
                    'date' => Carbon::now()->subDays(1),
                    'duration' => 50,
                    'raw' => '',
                    'updated_at' => Carbon::now()->subDays(1)
                ],
                [
                    'user_id' => 2,
                    'valid' => true,
                    'areacode' => rand(111, 999),
                    'phone_number' => rand(1111111, 9999999),
                    'dialed_number' => rand(1111111111, 9999999999),
                    'international' => false,
                    'type' => 'Incoming',
                    'date' => Carbon::now()->subDays(1),
                    'duration' => rand(1, 1000),
                    'raw' => '',
                    'updated_at' => Carbon::now()->subDays(1)
                ], [
                    'user_id' => 2,
                    'valid' => true,
                    'areacode' => rand(111, 999),
                    'phone_number' => rand(1111111, 9999999),
                    'dialed_number' => rand(1111111111, 9999999999),
                    'international' => false,
                    'type' => 'Incoming',
                    'date' => Carbon::now()->subDays(1),
                    'duration' => rand(1, 1000),
                    'raw' => '',
                    'updated_at' => Carbon::now()->subDays(1)
                ]
            ]);

        factory(Call::class)->create([
            'central_id' => 2,
            'stats_call_id' => 1,
            'intranet_user_id' => 2,
            'valid' => true,
            'dialed_number' => 18281111111,
            'concatenated_number' => 8281111111,
            'international' => false,
            'type' => 'Incoming',
            'duration' => 50,
            'date' => Carbon::now()->subDays(1),
        ]);
        factory(Call::class)->create([
            'central_id' => 2,
            'stats_call_id' => 2,
            'type' => 'Outgoing',
        ]);

        $foreignCalls = DB::connection('sqlite_stats_test')
            ->table('calls')
            ->get();

        $localRecord1 = Call::writeWithForeignRecord($foreignCalls[0]);
        $localRecord2 = Call::writeWithForeignRecord($foreignCalls[1]);
        $localRecord3 = Call::writeWithForeignRecord($foreignCalls[2]);

        $this->assertNull($localRecord1);
        $this->assertNotNull($localRecord2);
        $this->assertTrue(!empty($localRecord2->getChanges()));
        $this->assertTrue(!$localRecord2->wasRecentlyCreated);
        $this->assertNotNull($localRecord3);
        $this->assertTrue($localRecord3->wasRecentlyCreated);
    }
}
