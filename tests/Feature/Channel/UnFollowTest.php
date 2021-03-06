<?php

namespace Tests\Feature\Channel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Laravel\Sanctum\Sanctum;
use App\Models\Channel\Channel;
use App\Models\User;
use App\Models\Channel\Follower as ChannelFollower;
use Styde\Enlighten\Tests\EnlightenSetup;

class UnFollowTest extends TestCase
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
    public function successUnFollowChannel(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'hasFollower', 'addSlug'
        ])->create();


        $tryFollow = $this->patchJson(route('channel.follow', [
            'slug' => $channel->slug
        ]))->assertCreated();


        $this->assertTrue($tryFollow['ok']);
        $this->assertTrue($tryFollow['isValid']);
        $this->assertEquals(ChannelFollower::FOLLOW_OK, $tryFollow['messages']['code']);

        $tryUnFollow = $this->patchJson(route('channel.unFollow', [
            'slug' => $channel->slug
        ]))->assertOk();

        $this->assertTrue($tryUnFollow['ok']);
        $this->assertTrue($tryUnFollow['isValid']);
        $this->assertEquals(ChannelFollower::UNFOLLOW_OK, $tryUnFollow['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUnFolllowChannelWhenUserEmailIsNotVerified(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs($user = factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'hasFollower', 'addSlug'
        ])->create();

        $tryFollow = $this->patchJson(route('channel.follow', [
           'slug' => $channel->slug
        ]))->assertCreated();

        $this->assertTrue($tryFollow['ok']);
        $this->assertTrue($tryFollow['isValid']);
        $this->assertEquals(ChannelFollower::FOLLOW_OK, $tryFollow['messages']['code']);

        $user->email_verified_at = null;
        $user->save();
        $activeUser = Sanctum::actingAs($user);

        $tryUnFollow = $this->patchJson(route('channel.unFollow', [
            'slug' => $channel->slug
        ]))->assertForbidden();

        $this->assertFalse($tryUnFollow['ok']);
        $this->assertFalse($tryUnFollow['isValid']);
        $this->assertEquals(ChannelFollower::AUTORIZE_FAIL, $tryUnFollow['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUnFolllowChannelWhenUserAlreadyUnfollowChannel(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'hasFollower', 'addSlug'
        ])->create();

        $tryFollow = $this->patchJson(route('channel.follow', [
            'slug' => $channel->slug
        ]))->assertCreated();

        $this->assertTrue($tryFollow['ok']);
        $this->assertTrue($tryFollow['isValid']);
        $this->assertEquals(ChannelFollower::FOLLOW_OK, $tryFollow['messages']['code']);

        $tryUnFollow = $this->patchJson(route('channel.unFollow', [
            'slug' => $channel->slug
        ]))->assertOk();
        $this->assertTrue($tryUnFollow['ok']);
        $this->assertTrue($tryUnFollow['isValid']);
        $this->assertEquals(ChannelFollower::UNFOLLOW_OK, $tryUnFollow['messages']['code']);

        $tryUnFollowSecond = $this->patchJson(route('channel.unFollow', [
            'slug' => $channel->slug
        ]))->assertForbidden();

        $this->assertFalse($tryUnFollowSecond['ok']);
        $this->assertFalse($tryUnFollowSecond['isValid']);
        $this->assertEquals(ChannelFollower::ALREADY_UNFOLLOW, $tryUnFollowSecond['messages']['code']);
    }
}
