<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Laravel\Sanctum\Sanctum;
use App\Models\Channel;
use App\Models\User;
use App\Models\ChannelFollower;

class UnFollowChannelTest extends TestCase
{
    /** @test */
    public function 팔로우를_취소하라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'hasFollower', 'addSlug'
        ])->create();


        $tryFollow = $this->patchJson(route('followChannel', [
            'slug' => $channel->slug
        ]))->assertCreated();


        $this->assertTrue($tryFollow['ok']);
        $this->assertTrue($tryFollow['isValid']);
        $this->assertEquals(ChannelFollower::FOLLOW_OK, $tryFollow['messages']['code']);

        $tryUnFollow = $this->patchJson(route('unFollowChannel', [
            'slug' => $channel->slug
        ]))->assertOk();

        $this->assertTrue($tryUnFollow['ok']);
        $this->assertTrue($tryUnFollow['isValid']);
        $this->assertEquals(ChannelFollower::UNFOLLOW_OK, $tryUnFollow['messages']['code']);


        // $this->assertEquals([
        //     'ok' => true,
        //     'isValid' => true,
        //     'messages' => [
        //         'ok' => true,
        //         'isAlreadyUnFollow' => false,
        //     ]
        // ], $tryUnFollow->original);
    }

    /** @test */
    public function 이메일_인증안받은_유저가_언팔로우_실패하라(): void
    {
        $activeUser = Sanctum::actingAs($user = factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'hasFollower', 'addSlug'
        ])->create();

        $tryFollow = $this->patchJson(route('followChannel', [
           'slug' => $channel->slug
        ]))->assertCreated();

        $this->assertTrue($tryFollow['ok']);
        $this->assertTrue($tryFollow['isValid']);
        $this->assertEquals(ChannelFollower::FOLLOW_OK, $tryFollow['messages']['code']);

        $user->email_verified_at = null;
        $user->save();
        $activeUser = Sanctum::actingAs($user);

        $tryUnFollow = $this->patchJson(route('unFollowChannel', [
            'slug' => $channel->slug
        ]))->assertForbidden();

        $this->assertFalse($tryUnFollow['ok']);
        $this->assertFalse($tryUnFollow['isValid']);
        $this->assertEquals(ChannelFollower::AUTORIZE_FAIL, $tryUnFollow['messages']['code']);
    }

    /** @test */
    public function 이미_언팔로우했는데_또_언팔로우시_실패하라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'hasFollower', 'addSlug'
        ])->create();

        $tryFollow = $this->patchJson(route('followChannel', [
            'slug' => $channel->slug
        ]))->assertCreated();

        $this->assertTrue($tryFollow['ok']);
        $this->assertTrue($tryFollow['isValid']);
        $this->assertEquals(ChannelFollower::FOLLOW_OK, $tryFollow['messages']['code']);

        $tryUnFollow = $this->patchJson(route('unFollowChannel', [
            'slug' => $channel->slug
        ]))->assertOk();
        $this->assertTrue($tryUnFollow['ok']);
        $this->assertTrue($tryUnFollow['isValid']);
        $this->assertEquals(ChannelFollower::UNFOLLOW_OK, $tryUnFollow['messages']['code']);

        $tryUnFollowSecond = $this->patchJson(route('unFollowChannel', [
            'slug' => $channel->slug
        ]))->assertForbidden();

        $this->assertFalse($tryUnFollowSecond['ok']);
        $this->assertFalse($tryUnFollowSecond['isValid']);
        $this->assertEquals(ChannelFollower::ALREADY_UNFOLLOW, $tryUnFollowSecond['messages']['code']);
    }
}
