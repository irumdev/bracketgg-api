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

use Styde\Enlighten\Tests\EnlightenSetup;

class CheckFollowTest extends TestCase
{
    use EnlightenSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
    }

    /**
     * @test
     * @enlighten
     */
    public function failFollowChannelWhenChannelIsNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $tryCheckChannelIsAlreadyFollow = $this->getJson(route('channel.isFollow', [
            'slug' => Str::random(40)
        ]))->assertNotFound();

        $this->assertFalse($tryCheckChannelIsAlreadyFollow['ok']);
        $this->assertFalse($tryCheckChannelIsAlreadyFollow['isValid']);
        $this->assertNotFoundMessages($tryCheckChannelIsAlreadyFollow['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookupChannelIsFollowingWhenUserIsNotLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCheckChannelIsAlreadyFollow = $this->getJson(route('channel.isFollow', [
            'slug' => Str::random(40)
        ]))->assertUnauthorized();

        $this->assertFalse($tryCheckChannelIsAlreadyFollow['ok']);
        $this->assertFalse($tryCheckChannelIsAlreadyFollow['isValid']);
        $this->assertUnauthorizedMessages($tryCheckChannelIsAlreadyFollow['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function getTrueWhenChannelAlreadyFollow(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states(['addSlug'])->create();

        $tryFollowChannel = $this->patchJson(route('channel.follow', [
            'slug' => $channel->slug
        ]))->assertCreated();

        $this->assertTrue($tryFollowChannel['ok']);
        $this->assertTrue($tryFollowChannel['isValid']);
        $this->assertEquals(ChannelFollower::FOLLOW_OK, $tryFollowChannel['messages']['code']);

        $tryCheckChannelIsAlreadyFollow = $this->getJson(route('channel.isFollow', [
            'slug' => $channel->slug,
        ]))->assertOk();

        $this->assertTrue($tryCheckChannelIsAlreadyFollow['ok']);
        $this->assertTrue($tryCheckChannelIsAlreadyFollow['isValid']);
        $this->assertTrue($tryCheckChannelIsAlreadyFollow['messages']['isFollow']);
    }

    /**
     * @test
     * @enlighten
     */
    public function getFalseWhenChannelUnFollow(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states(['addSlug'])->create();
        $tryCheckChannelIsAlreadyFollow = $this->getJson(route('channel.isFollow', [
            'slug' => $channel->slug,
        ]))->assertOk();

        $this->assertTrue($tryCheckChannelIsAlreadyFollow['ok']);
        $this->assertTrue($tryCheckChannelIsAlreadyFollow['isValid']);
        $this->assertFalse($tryCheckChannelIsAlreadyFollow['messages']['isFollow']);
    }
}
