<?php

namespace Tests\Unit;

use App\Interview;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanBeCreated()
    {
        $interview = factory(Interview::class)->create();

        $this->assertTrue($interview->user != null);
    }
}
