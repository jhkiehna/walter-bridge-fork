<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use App\Jobs\PublishKafkaJob;

class PublishToKafkaJobTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateKeyCreatesTheRightKey()
    {
        $obj = new PublishKafkaJob((object) [
            'type' => 'email',
            'data' => (object) [
                'id' => 1,
            ]
        ]);

        $result = $obj->createKey();

        $this->assertEquals($result, "bridge-email-1");
    }
}
