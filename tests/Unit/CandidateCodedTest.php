<?php

namespace Tests\Unit;

use App\CandidateCoded;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CandidateCodedTest extends TestCase
{
    use RefreshDatabase;

    public function testCandidateCodedCanBeCreated()
    {
        $candidateCoded = factory(CandidateCoded::class)->create();

        $this->assertTrue($candidateCoded->user != null);
    }
}
