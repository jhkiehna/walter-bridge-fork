<?php

namespace Tests\Unit;

use App\Call;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CallTest extends TestCase
{
    use RefreshDatabase;

    public function testCallCanBeCreated()
    {
        $call = factory(Call::class)->create();

        $this->assertTrue($call->user != null);
    }
}
