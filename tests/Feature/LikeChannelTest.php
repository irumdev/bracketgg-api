<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Channel;
use App\Models\ChannelFan;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class LikeChannelTest extends TestCase
{
    /** @test */
    public function 채널_좋아요_하라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states([
            'hasLike'
        ])->create();

        $beforeChannelLikeCount = $channel->like_count;

        $fan = ChannelFan::where([
            ['user_id', '=', $activeUser->id],
            ['channel_id', '=', $channel->id],
        ]);
        $this->assertFalse($fan->exists());

        $tryLikeToChannel = $this->postJson(route('likeChannel', [
            'channel' => $channel->id
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
            'hasLike'
        ])->create();

        $fan = ChannelFan::where([
            ['user_id', '=', $activeUser->id],
            ['channel_id', '=', $channel->id],
        ]);
        $this->assertFalse($fan->exists());

        $tryLikeToChannel = $this->postJson(route('likeChannel', [
            'channel' => $channel->id
        ]))->assertForbidden();

        $this->assertFalse($tryLikeToChannel['ok']);
        $this->assertFalse($tryLikeToChannel['isValid']);
    }

    /** @test */
    public function 채널_좋아요_취소_하라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->create();

        $beforeChannelLikeCount = $channel->like_count;
        $tryLikeToChannel = $this->postJson(route('likeChannel', [
            'channel' => $channel->id
        ]))->assertCreated();


        $this->assertTrue($tryLikeToChannel['ok']);
        $this->assertTrue($tryLikeToChannel['isValid']);
        $this->assertEquals(Channel::find($channel->id)->like_count, 1);

        $tryLikeToChannel = $this->postJson(route('unLikeChannel', [
            'channel' => $channel->id
        ]))->assertOk();

        $this->assertTrue($tryLikeToChannel['ok']);
        $this->assertTrue($tryLikeToChannel['isValid']);
        $this->assertEquals(Channel::find($channel->id)->like_count, 0);
    }

    /** @test */
    public function 채널_좋아요_0인데_취소에_실패_하라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->create();
        $tryLikeToChannel = $this->postJson(route('unLikeChannel', [
            'channel' => $channel->id
        ]))->assertOk();

        $this->assertTrue($tryLikeToChannel['ok']);
        $this->assertTrue($tryLikeToChannel['isValid']);
        $this->assertEquals(Channel::find($channel->id)->like_count, 0);
    }
}
