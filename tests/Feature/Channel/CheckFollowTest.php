<?php

namespace Tests\Feature\Channel;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;


use App\Models\Channel\Channel;
use App\Models\Channel\Follower as ChannelFollower;

class CheckFollowTest extends TestCase
{
    /** @test */
    public function failFollowChannelWhenChannelIsNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $tryCheckChannelIsAlreadyFollow = $this->getJson(route('channelIsFollow', [
            'slug' => Str::random(40)
        ]))->assertNotFound();

        $this->assertFalse($tryCheckChannelIsAlreadyFollow['ok']);
        $this->assertFalse($tryCheckChannelIsAlreadyFollow['isValid']);
        $this->assertEquals(['code' => 404], $tryCheckChannelIsAlreadyFollow['messages']);
    }

    /** @test */
    public function failLookupChannelIsFollowingWhenUserIsNotLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCheckChannelIsAlreadyFollow = $this->getJson(route('channelIsFollow', [
            'slug' => Str::random(40)
        ]))->assertUnauthorized();

        $this->assertFalse($tryCheckChannelIsAlreadyFollow['ok']);
        $this->assertFalse($tryCheckChannelIsAlreadyFollow['isValid']);
        $this->assertEquals(['code' => 401], $tryCheckChannelIsAlreadyFollow['messages']);
    }

    /** @test */
    public function getTrueWhenChannelAlreadyFollow(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states(['addSlug'])->create();

        $tryFollowChannel = $this->patchJson(route('followChannel', [
            'slug' => $channel->slug
        ]));

        $this->assertTrue($tryFollowChannel['ok']);
        $this->assertTrue($tryFollowChannel['isValid']);
        $this->assertEquals(ChannelFollower::FOLLOW_OK, $tryFollowChannel['messages']['code']);

        $tryCheckChannelIsAlreadyFollow = $this->getJson(route('channelIsFollow', [
            'slug' => $channel->slug,
        ]))->assertOk();

        $this->assertTrue($tryCheckChannelIsAlreadyFollow['ok']);
        $this->assertTrue($tryCheckChannelIsAlreadyFollow['isValid']);
        $this->assertTrue($tryCheckChannelIsAlreadyFollow['messages']['isFollow']);
    }

    /** @test */
    public function getFalseWhenChannelUnFollow(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states(['addSlug'])->create();
        $tryCheckChannelIsAlreadyFollow = $this->getJson(route('channelIsFollow', [
            'slug' => $channel->slug,
        ]))->assertOk();

        $this->assertTrue($tryCheckChannelIsAlreadyFollow['ok']);
        $this->assertTrue($tryCheckChannelIsAlreadyFollow['isValid']);
        $this->assertFalse($tryCheckChannelIsAlreadyFollow['messages']['isFollow']);
    }
}
