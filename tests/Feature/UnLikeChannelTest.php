<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Channel\Channel;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Channel\Fan as ChannelFan;

class UnLikeChannelTest extends TestCase
{
    /** @test */
    public function successUnLikeChannel(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->state('addSlug')->create();

        $beforeChannelLikeCount = $channel->like_count;
        $tryLikeToChannel = $this->patchJson(route('likeChannel', [
            'slug' => $channel->slug
        ]))->assertCreated();


        $this->assertTrue($tryLikeToChannel['ok']);
        $this->assertTrue($tryLikeToChannel['isValid']);
        $this->assertEquals(Channel::find($channel->id)->like_count, 1);

        $tryLikeToChannel = $this->patchJson(route('unLikeChannel', [
            'slug' => $channel->slug
        ]))->assertOk();

        $this->assertTrue($tryLikeToChannel['ok']);
        $this->assertTrue($tryLikeToChannel['isValid']);
        $this->assertEquals(ChannelFan::UNLIKE_OK, $tryLikeToChannel['messages']['code']);

        $this->assertEquals(Channel::find($channel->id)->like_count, 0);
    }

    /** @test */
    public function failUnLikeChannelWhenChannelHasNotLikeUser(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->state('addSlug')->create();
        $tryLikeToChannel = $this->patchJson(route('unLikeChannel', [
            'slug' => $channel->slug
        ]))->assertForbidden();

        $this->assertFalse($tryLikeToChannel['ok']);
        $this->assertFalse($tryLikeToChannel['isValid']);
        $this->assertEquals(Channel::find($channel->id)->like_count, 0);
        $this->assertEquals(ChannelFan::ALREADY_UNLIKE, $tryLikeToChannel['messages']['code']);
    }

    /** @test */
    public function failUnLikeChannelWhenUserEmailIsNotVerified(): void
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

        $tryLikeToChannel = $this->patchJson(route('unLikeChannel', [
            'slug' => $channel->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryLikeToChannel['ok']);
        $this->assertFalse($tryLikeToChannel['isValid']);
        $this->assertEquals(ChannelFan::AUTORIZE_FAIL, $tryLikeToChannel['messages']['code']);
    }
}
