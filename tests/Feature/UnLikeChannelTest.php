<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Channel;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\ChannelFan;

class UnLikeChannelTest extends TestCase
{
    /** @test */
    public function 채널_좋아요_취소_하라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->state('addSlug')->create();

        $beforeChannelLikeCount = $channel->like_count;
        $tryLikeToChannel = $this->postJson(route('likeChannel', [
            'channel' => $channel->slug
        ]))->assertCreated();


        $this->assertTrue($tryLikeToChannel['ok']);
        $this->assertTrue($tryLikeToChannel['isValid']);
        $this->assertEquals(Channel::find($channel->id)->like_count, 1);

        $tryLikeToChannel = $this->postJson(route('unLikeChannel', [
            'channel' => $channel->slug
        ]))->assertOk();

        $this->assertTrue($tryLikeToChannel['ok']);
        $this->assertTrue($tryLikeToChannel['isValid']);
        $this->assertEquals(ChannelFan::UNLIKE_OK, $tryLikeToChannel['messages']['code']);

        $this->assertEquals(Channel::find($channel->id)->like_count, 0);
    }

    /** @test */
    public function 채널_좋아요_0인데_취소에_실패_하라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->state('addSlug')->create();
        $tryLikeToChannel = $this->postJson(route('unLikeChannel', [
            'channel' => $channel->slug
        ]))->assertForbidden();

        $this->assertFalse($tryLikeToChannel['ok']);
        $this->assertFalse($tryLikeToChannel['isValid']);
        $this->assertEquals(Channel::find($channel->id)->like_count, 0);
        $this->assertEquals(ChannelFan::ALREADY_UNLIKE, $tryLikeToChannel['messages']['code']);
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

        $tryLikeToChannel = $this->postJson(route('unLikeChannel', [
            'channel' => $channel->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryLikeToChannel['ok']);
        $this->assertFalse($tryLikeToChannel['isValid']);
        $this->assertEquals(ChannelFan::AUTORIZE_FAIL, $tryLikeToChannel['messages']['code']);
    }
}
