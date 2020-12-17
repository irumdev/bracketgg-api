<?php

namespace Tests\Feature\Channel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Channel\Channel;
use App\Models\Channel\Fan as ChannelFan;
use Laravel\Sanctum\Sanctum;

class LikeTest extends TestCase
{
    /** @test */
    public function likeChannel(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'hasLike', 'addSlug'
        ])->create();

        $beforeChannelLikeCount = $channel->like_count;

        $fan = ChannelFan::where([
            ['user_id', '=', $activeUser->id],
            ['channel_id', '=', $channel->id],
        ]);
        $this->assertFalse($fan->exists());

        $tryLikeToChannel = $this->patchJson(route('likeChannel', [
            'slug' => $channel->slug
        ]))->assertCreated();

        $this->assertTrue($tryLikeToChannel['ok']);
        $this->assertTrue($tryLikeToChannel['isValid']);
        $this->assertEquals(Channel::find($channel->id)->like_count, $beforeChannelLikeCount + 1);

        $fan = ChannelFan::where([
            ['user_id', '=', $activeUser->id],
            ['channel_id', '=', $channel->id],
        ]);

        $this->assertTrue($fan->exists());
        $this->assertEquals(ChannelFan::LIKE_OK, $tryLikeToChannel['messages']['code']);
    }

    /** @test */
    public function failLikeChannelWhenTryUserEmailIsNotVerified(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create([
            'email_verified_at' => null,
        ]));
        $channel = factory(Channel::class)->states([
            'hasLike', 'addSlug'
        ])->create();

        $fan = ChannelFan::where([
            ['user_id', '=', $activeUser->id],
            ['channel_id', '=', $channel->id],
        ]);
        $this->assertFalse($fan->exists());

        $tryLikeToChannel = $this->patchJson(route('likeChannel', [
            'slug' => $channel->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryLikeToChannel['ok']);
        $this->assertFalse($tryLikeToChannel['isValid']);
        $this->assertEquals(ChannelFan::AUTORIZE_FAIL, $tryLikeToChannel['messages']['code']);
    }

    /** @test */
    public function failLikeChannelWhenChannelIsNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create([
            'email_verified_at' => null,
        ]));
        $channel = factory(Channel::class)->states([
            'hasLike', 'addSlug'
        ])->create();

        $fan = ChannelFan::where([
            ['user_id', '=', $activeUser->id],
            ['channel_id', '=', $channel->id],
        ]);
        $this->assertFalse($fan->exists());

        $tryLikeToChannel = $this->patchJson(route('likeChannel', [
            'slug' => -99
        ]))->assertNotFound();

        $this->assertFalse($tryLikeToChannel['ok']);
        $this->assertFalse($tryLikeToChannel['isValid']);
        $this->assertEquals(404, $tryLikeToChannel['messages']['code']);
    }

    /** @test */
    public function failLikeChannelWhenTryUserAlreadyLike(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'hasLike', 'addSlug'
        ])->create();

        $beforeChannelLikeCount = $channel->like_count;

        $fan = ChannelFan::where([
            ['user_id', '=', $activeUser->id],
            ['channel_id', '=', $channel->id],
        ]);
        $this->assertFalse($fan->exists());

        $tryLikeToChannel = $this->patchJson(route('likeChannel', [
            'slug' => $channel->slug
        ]))->assertCreated();

        $this->assertTrue($tryLikeToChannel['ok']);
        $this->assertTrue($tryLikeToChannel['isValid']);
        $this->assertEquals(Channel::find($channel->id)->like_count, $beforeChannelLikeCount + 1);

        $fan = ChannelFan::where([
            ['user_id', '=', $activeUser->id],
            ['channel_id', '=', $channel->id],
        ]);

        $this->assertTrue($fan->exists());
        $this->assertEquals(ChannelFan::LIKE_OK, $tryLikeToChannel['messages']['code']);

        $tryLikeToChannelSecond = $this->patchJson(route('likeChannel', [
            'slug' => $channel->slug
        ]))->assertForbidden();

        $this->assertFalse($tryLikeToChannelSecond['ok']);
        $this->assertFalse($tryLikeToChannelSecond['isValid']);
        $this->assertEquals(ChannelFan::ALREADY_LIKE, $tryLikeToChannelSecond['messages']['code']);
    }
}
