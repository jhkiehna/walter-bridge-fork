<?php

namespace Tests\Unit;

use App\Sendout;
use App\FailedItem;
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

    public function testFailedSendoutCanBeRetrieved()
    {
        $sendout = factory(Sendout::class)->create();

        FailedItem::make()->failable()->associate($sendout)->save();

        $this->assertTrue(!$sendout->failedItems->isEmpty());
    }
}
