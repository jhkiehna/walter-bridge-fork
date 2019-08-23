<?php

namespace Tests\Unit;

use App\User;
use App\Sendout;
use App\Interview;
use Tests\TestCase;
use App\CandidateCoded;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanBeCreated()
    {
        $user = factory(User::class)->create();

        factory(CandidateCoded::class)->create([
            'central_id' => $user->central_id
        ]);
        factory(Interview::class)->create([
            'central_id' => $user->central_id
        ]);
        factory(Sendout::class)->create([
            'central_id' => $user->central_id
        ]);

        $this->assertTrue($user->walter_id != null);
        $this->assertTrue($user->central_id != null);
        $this->assertTrue($user->candidatesCoded()->first() != null);
        $this->assertTrue($user->interviews()->first() != null);
        $this->assertTrue($user->sendouts()->first() != null);
    }
}
