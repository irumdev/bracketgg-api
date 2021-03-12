<?php

namespace Tests\Feature\Channel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Channel\Channel;
use App\Models\Channel\Follower as ChannelFollower;
use Illuminate\Support\Str;
use Styde\Enlighten\Tests\EnlightenSetup;

class FollowTest extends TestCase
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
    public function successFollowChannel(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $channel = factory(Channel::class)->states([
            'addSlug'
        ])->create();

        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $tryFollow = $this->patchJson(route('channel.follow', [
            'slug' => $channel->slug
        ]))->assertCreated();


        $this->assertTrue($tryFollow['ok']);
        $this->assertTrue($tryFollow['isValid']);
        $this->assertEquals(ChannelFollower::FOLLOW_OK, $tryFollow['messages']['code']);


        $this->assertEquals($activeUser->id, $channel->followers->find($activeUser->id)->id);
        $this->assertEquals($activeUser->email, $channel->followers->find($activeUser->id)->email);
    }

    /**
     * @test
     * @enlighten
     */
    public function ownerFailFollowChannelWhenFollowMyChannel(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $channel = factory(Channel::class)->states([
            'hasFollower', 'addSlug'
        ])->create();

        $activeUser = Sanctum::actingAs(User::find($channel->owner));
        $tryFollow = $this->patchJson(route('channel.follow', [
            'slug' => $channel->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryFollow['ok']);
        $this->assertFalse($tryFollow['isValid']);
        $this->assertEquals(ChannelFollower::OWNER_FOLLOW_OWNER, $tryFollow['messages']['code']);
    }


    /**
     * @test
     * @enlighten
     */
    public function failFollowChannelWhenUserEmailIsNotVerified(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $channel = factory(Channel::class)->states([
            'hasFollower', 'addSlug'
        ])->create();

        $activeUser = Sanctum::actingAs(factory(User::class)->create([
            'email_verified_at' => null
        ]));

        $tryFollow = $this->patchJson(route('channel.follow', [
            'slug' => $channel->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryFollow['ok']);
        $this->assertFalse($tryFollow['isValid']);
        $this->assertEquals(ChannelFollower::AUTORIZE_FAIL, $tryFollow['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failFollowChannelWhelAlreadyChannelFollowed(): void
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

        $this->assertEquals($activeUser->id, $channel->followers->last()->id);

        $secondTry = $this->patchJson(route('channel.follow', [
            'slug' => $channel->slug
        ]))->assertForbidden();

        $this->assertFalse($secondTry['ok']);
        $this->assertFalse($secondTry['isValid']);
        $this->assertEquals(ChannelFollower::ALREADY_FOLLOW, $secondTry['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failFollowChannelWhenChannelNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $tryFollow = $this->patchJson(route('channel.follow', [
            'slug' => Str::random(10)
        ]))->assertNotFound();

        $this->assertNotFoundMessages($tryFollow['messages']);
        $this->assertFalse($tryFollow['ok']);
        $this->assertFalse($tryFollow['isValid']);
    }
}
