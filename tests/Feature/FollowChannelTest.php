<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Channel;

class FollowChannelTest extends TestCase
{
    /** @test */
    public function 채널을_팔로우_하라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'hasFollower'
        ])->create();

        $tryFollow = $this->postJson(route('followChannel', [
            $channel->id
        ]))->assertCreated();

        $this->assertTrue($tryFollow['ok']);
        $this->assertTrue($tryFollow['isValid']);
        $this->assertTrue($tryFollow['messages']['ok']);
        $this->assertFalse($tryFollow['messages']['isAlreadyFollow']);

        $this->assertEquals($activeUser->id, $channel->followers->last()->id);
    }

    /** @test */
    public function 이미_채널을_팔로우_했는데_또다시_팔로우_시도에_실패하라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'hasFollower'
        ])->create();

        $tryFollow = $this->postJson(route('followChannel', [
            $channel->id
        ]))->assertCreated();

        $this->assertTrue($tryFollow['ok']);
        $this->assertTrue($tryFollow['isValid']);
        $this->assertTrue($tryFollow['messages']['ok']);
        $this->assertFalse($tryFollow['messages']['isAlreadyFollow']);

        $this->assertEquals($activeUser->id, $channel->followers->last()->id);

        $secondTry = $this->postJson(route('followChannel', [
            $channel->id
        ]))->assertStatus(422);

        $this->assertFalse($secondTry['ok']);
        $this->assertFalse($secondTry['isValid']);
        $this->assertEquals(['isAlreadyFollow' => true, 'ok' => false], $secondTry['messages']);
    }

    /** @test */
    public function 없는_채널_팔로우에_실패하라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'hasFollower'
        ])->create();

        $tryFollow = $this->postJson(route('followChannel', [
            -99
        ]))->assertNotFound();

        $this->assertEquals(['code' => 404], $tryFollow['messages']);
        $this->assertFalse($tryFollow['ok']);
        $this->assertFalse($tryFollow['isValid']);
    }
}
