<?php

namespace Tests\Unit\Console\Commands;

use Mockery;
use Tests\TestCase;
use App\Services\KafkaConsumer;
use App\KafkaEvent;

class KafkaConsumeTest extends TestCase
{
    public function testItStartsConsumeing()
    {
        $this->mock(KafkaConsumer::class, function ($mock) {
            $mock->shouldReceive('start')->once();
        });

        $this->artisan('kafka:consume');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItCallsKafkaEvent()
    {
        $this->mock(KafkaEvent::class, function ($mock) {
            $mock->shouldReceive('process')->once();
        });

        $mock = Mockery::namedMock('App\Services\KafkaConsumer', 'Tests\Unit\Console\Commands\GoodKafkaConsumerEvent')
            ->makePartial();

        $this->app->instance('App\Services\KafkaConsumer', $mock);

        $this->artisan('kafka:consume');
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItHandlesJsonException()
    {
        $this->mock(KafkaEvent::class, function ($mock) {
            $mock->shouldReceive('process')->andThrow(\JsonException::class);
        });

        $mock = Mockery::namedMock('App\Services\KafkaConsumer', 'Tests\Unit\Console\Commands\GoodKafkaConsumerEvent')
            ->makePartial();

        $this->app->instance('App\Services\KafkaConsumer', $mock);

        $this->artisan('kafka:consume');

        // assert that a exception was not thrown
        $this->assertTrue(true);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItHandlesException()
    {
        $this->mock(KafkaEvent::class, function ($mock) {
            $mock->shouldReceive('process')->andThrow(\Exception::class);
        });

        $mock = Mockery::namedMock('App\Services\KafkaConsumer', 'Tests\Unit\Console\Commands\GoodKafkaConsumerEvent')
            ->makePartial();

        $this->app->instance('App\Services\KafkaConsumer', $mock);

        $this->artisan('kafka:consume');

        // assert that a exception was not thrown
        $this->assertTrue(true);
    }
}

// @codingStandardsIgnoreStart
class GoodKafkaConsumerEvent
{
    public function start($handler)
    {
        $message = [
            'offset' => 22,
            'size' => 255,
            'message' => [
                'crc' => 1809700285,
                'magic' => 1,
                'attr' => 0,
                'timestamp' => 1569876938578,
                'value' => '{\"type\":\"user\",\"published_at\":\"2019-09-30T20:55:37.862Z\",\"data\":{\"id\":123,\"walter     _id\":375,\"intranet_id\":181,\"email\":\"jzellner@kimmel.com\",\"first_name\":\"John\",\"last_name\":\"Zellner\",\"nickname\":null,\"preferred_name\":\"John\",\"abbreviation\":\"JGZ\",\"title\":\"Associate\",\"linkedin_url\":null,\"birth_date\":null,\"hire_date\":\"2019-08-27\"}}'
            ]
        ];

        $handler('kimmel', 1, $message);
    }
}
