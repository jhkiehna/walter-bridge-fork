<?php

namespace Tests\Unit;

use Mockery;
use App\Call;
use Tests\TestCase;
use App\Services\KafkaProducer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;

class CallTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $kafkaMock = Mockery::mock(KafkaProducer::class);
        $kafkaMock->shouldReceive('publish')->andReturn(null);

        $this->app->instance(KafkaProducer::class, $kafkaMock);
    }

    public function testItCanPublishACallWithAnInternationalNumberToKafka()
    {
        $call = factory(Call::class)->states('international')->create([
            'dialed_number' => 11441946695420
        ]);

        $isSuccess = $call->publishToKafka();

        $this->assertTrue($isSuccess);
    }

    public function testItCanPublishACallWithANationalNumberToKafka()
    {
        $call = factory(Call::class)->states('national')->create([
            'dialed_number' => 18282519900
        ]);

        $isSuccess = $call->publishToKafka();

        $this->assertTrue($isSuccess);
    }

    public function testItCantPublishACallWithThatDoesntPassValidationOnCallModel()
    {
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
}
