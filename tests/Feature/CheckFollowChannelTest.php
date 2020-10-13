<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;


use App\Models\Channel;
use App\Models\ChannelFollower;

class CheckFollowChannelTest extends TestCase
{

    /** @test */
    public function 없는채널_팔로우_여부조회에_실패하라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $tryCheckChannelIsAlreadyFollow = $this->getJson(route('channelIsFollow', [
            'channel' => Str::random(40)
        ]))->assertNotFound();


        $this->assertFalse($tryCheckChannelIsAlreadyFollow['ok']);
        $this->assertFalse($tryCheckChannelIsAlreadyFollow['isValid']);
        $this->assertEquals(['code' => 404], $tryCheckChannelIsAlreadyFollow['messages']);
    }

    /** @test */
    public function 로그인_안한_유저가_팔로우_여부조회에_실패하라(): void
    {
        $tryCheckChannelIsAlreadyFollow = $this->getJson(route('channelIsFollow', [
            'channel' => Str::random(40)
        ]))->assertUnauthorized();

        $this->assertFalse($tryCheckChannelIsAlreadyFollow['ok']);
        $this->assertFalse($tryCheckChannelIsAlreadyFollow['isValid']);
        $this->assertEquals(['code' => 401], $tryCheckChannelIsAlreadyFollow['messages']);
    }

    /** @test */
    public function 팔로우_한_채널_조회에_true_리턴을_받아라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states(['addSlug'])->create();

        $tryFollowChannel = $this->postJson(route('followChannel', [
            'channel' => $channel->slug
        ]));

        $this->assertTrue($tryFollowChannel['ok']);
        $this->assertTrue($tryFollowChannel['isValid']);
        $this->assertEquals(ChannelFollower::FOLLOW_OK, $tryFollowChannel['messages']['code']);

        $tryCheckChannelIsAlreadyFollow = $this->getJson(route('channelIsFollow', [
            'channel' => $channel->slug,
        ]))->assertOk();

        $this->assertTrue($tryCheckChannelIsAlreadyFollow['ok']);
        $this->assertTrue($tryCheckChannelIsAlreadyFollow['isValid']);
        $this->assertTrue($tryCheckChannelIsAlreadyFollow['messages']['isFollow']);
    }

    /** @test */
    public function 팔로우_안한_채널_조화에_false_리턴을_받아라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states(['addSlug'])->create();
        $tryCheckChannelIsAlreadyFollow = $this->getJson(route('channelIsFollow', [
            'channel' => $channel->slug,
        ]))->assertOk();

        $this->assertTrue($tryCheckChannelIsAlreadyFollow['ok']);
        $this->assertTrue($tryCheckChannelIsAlreadyFollow['isValid']);
        $this->assertFalse($tryCheckChannelIsAlreadyFollow['messages']['isFollow']);
    }
}
