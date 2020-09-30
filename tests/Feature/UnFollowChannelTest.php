<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Laravel\Sanctum\Sanctum;
use App\Models\Channel;
use App\Models\User;

class UnFollowChannelTest extends TestCase
{
    /** @test */
    public function 팔로우를_취소하라(): void
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

        $tryUnFollow = $this->postJson(route('unFollowChannel', [
            'channel' => $channel->id
        ]))->assertOk();
        $this->assertEquals([
            'ok' => true,
            'isValid' => true,
            'messages' => [
                'ok' => true,
                'isAlreadyUnFollow' => false,
            ]
        ], $tryUnFollow->original);
    }

    /** @test */
    public function 이미_언팔로우했는데_또_언팔로우시_실패하라()
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

        $tryUnFollow = $this->postJson(route('unFollowChannel', [
            'channel' => $channel->id
        ]))->assertOk();
        $this->assertEquals([
            'ok' => true,
            'isValid' => true,
            'messages' => [
                'ok' => true,
                'isAlreadyUnFollow' => false,
            ]
        ], $tryUnFollow->original);

        $tryUnFollowSecond = $this->postJson(route('unFollowChannel', [
            'channel' => $channel->id
        ]))->assertStatus(422);

        $this->assertEquals([
            'ok' => false,
            'isValid' => false,
            'messages' => [
                'ok' => false,
                'isAlreadyUnFollow' => true,
            ]
        ], $tryUnFollowSecond->original);
    }
}
