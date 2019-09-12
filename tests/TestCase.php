<?php

namespace Tests;

use Mockery;
use App\Services\KafkaProducer;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();

        $kafkaMock = Mockery::mock(KafkaProducer::class);
        $kafkaMock->shouldReceive('publish')->andReturn(null);

        $this->app->instance(KafkaProducer::class, $kafkaMock);
    }
}
