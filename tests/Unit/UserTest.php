<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanBeCreated()
    {
        $user = factory(User::class)->create();

        $this->assertTrue($user->walter_id != null);
        $this->assertTrue($user->central_id != null);
    }
}
