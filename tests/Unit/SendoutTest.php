<?php

namespace Tests\Unit;

use App\Sendout;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendoutTest extends TestCase
{
    use RefreshDatabase;

    public function testSendoutCanBeCreated()
    {
        $sendout = factory(Sendout::class)->create();

        $this->assertTrue($sendout->user != null);
    }
}
