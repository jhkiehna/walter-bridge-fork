<?php

namespace Tests\Unit;

use App\Interview;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InterviewTest extends TestCase
{
    use RefreshDatabase;

    public function testInterviewCanBeCreated()
    {
        $interview = factory(Interview::class)->create();

        $this->assertTrue($interview->user != null);
    }
}
