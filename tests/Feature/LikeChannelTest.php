<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Channel;
use Laravel\Sanctum\Sanctum;
use App\Models\ChannelFan;
use App\Models\User;

class LikeChannelTest extends TestCase
{
    /** @test */
    public function 채널_좋아요_하라(): void
    {
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

        $tryLikeToChannel = $this->postJson(route('likeChannel', [
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
    public function 이메일_인증받지_않은_유저가_채널_좋아요_실패하라(): void
    {
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

        $tryLikeToChannel = $this->postJson(route('likeChannel', [
            'slug' => $channel->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryLikeToChannel['ok']);
        $this->assertFalse($tryLikeToChannel['isValid']);
        $this->assertEquals(ChannelFan::AUTORIZE_FAIL, $tryLikeToChannel['messages']['code']);
    }

    /** @test */
    public function 없는_채널에_좋아요_실패하라(): void
    {
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

        $tryLikeToChannel = $this->postJson(route('likeChannel', [
            'slug' => -99
        ]))->assertNotFound();

        $this->assertFalse($tryLikeToChannel['ok']);
        $this->assertFalse($tryLikeToChannel['isValid']);
        $this->assertEquals(404, $tryLikeToChannel['messages']['code']);
    }

    /** @test */
    public function 이미_좋아요_중복에_실패하라(): void
    {
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

        $tryLikeToChannel = $this->postJson(route('likeChannel', [
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

        $tryLikeToChannelSecond = $this->postJson(route('likeChannel', [
            'slug' => $channel->slug
        ]))->assertForbidden();

        $this->assertFalse($tryLikeToChannelSecond['ok']);
        $this->assertFalse($tryLikeToChannelSecond['isValid']);
        $this->assertEquals(ChannelFan::ALREADY_LIKE, $tryLikeToChannelSecond['messages']['code']);
    }
}
