<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Channel;
use App\Models\ChannelFollower;
use Illuminate\Support\Str;

class FollowChannelTest extends TestCase
{
    /** @test */
    public function 채널을_팔로우_하라(): void
    {
        $channel = factory(Channel::class)->states([
            'addSlug'
        ])->create();

        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $tryFollow = $this->postJson(route('followChannel', [
            'slug' => $channel->slug
        ]))->assertCreated();


        $this->assertTrue($tryFollow['ok']);
        $this->assertTrue($tryFollow['isValid']);
        $this->assertEquals(ChannelFollower::FOLLOW_OK, $tryFollow['messages']['code']);


        $this->assertEquals($activeUser->id, $channel->followers->find($activeUser->id)->id);
        $this->assertEquals($activeUser->email, $channel->followers->find($activeUser->id)->email);
    }

    /** @test */
    public function 채널장이_내_채널을_팔로우에_실패_하라(): void
    {
        $channel = factory(Channel::class)->states([
            'hasFollower', 'addSlug'
        ])->create();

        $activeUser = Sanctum::actingAs(User::find($channel->owner));


        $tryFollow = $this->postJson(route('followChannel', [
            'slug' => $channel->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryFollow['ok']);
        $this->assertFalse($tryFollow['isValid']);
        $this->assertEquals(ChannelFollower::OWNER_FOLLOW_OWNER, $tryFollow['messages']['code']);
    }


    /** @test */
    public function 이메일_인증_인받은_유저가_채널을_팔로우를_실패하라(): void
    {
        $channel = factory(Channel::class)->states([
            'hasFollower', 'addSlug'
        ])->create();

        $activeUser = Sanctum::actingAs(factory(User::class)->create([
            'email_verified_at' => null
        ]));

        $tryFollow = $this->postJson(route('followChannel', [
            'slug' => $channel->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryFollow['ok']);
        $this->assertFalse($tryFollow['isValid']);
        $this->assertEquals(ChannelFollower::AUTORIZE_FAIL, $tryFollow['messages']['code']);
    }

    /** @test */
    public function 이미_채널을_팔로우_했는데_또다시_팔로우_시도에_실패하라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'hasFollower', 'addSlug'
        ])->create();

        $tryFollow = $this->postJson(route('followChannel', [
            'slug' => $channel->slug
        ]))->assertCreated();

        $this->assertTrue($tryFollow['ok']);
        $this->assertTrue($tryFollow['isValid']);
        $this->assertEquals(ChannelFollower::FOLLOW_OK, $tryFollow['messages']['code']);

        $this->assertEquals($activeUser->id, $channel->followers->last()->id);

        $secondTry = $this->postJson(route('followChannel', [
            'slug' => $channel->slug
        ]))->assertForbidden();

        $this->assertFalse($secondTry['ok']);
        $this->assertFalse($secondTry['isValid']);
        $this->assertEquals(ChannelFollower::ALREADY_FOLLOW, $secondTry['messages']['code']);
    }

    /** @test */
    public function 없는_채널_팔로우에_실패하라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $tryFollow = $this->postJson(route('followChannel', [
            'slug' => Str::random(10)
        ]))->assertNotFound();

        $this->assertEquals(['code' => 404], $tryFollow['messages']);
        $this->assertFalse($tryFollow['ok']);
        $this->assertFalse($tryFollow['isValid']);
    }
}
