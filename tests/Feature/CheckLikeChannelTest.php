<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;
use App\Models\Channel;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class CheckLikeChannelTest extends TestCase
{
    /** @test */
    public function 없는_채널_좋아요여부_조회에_실패하라(): void
    {
        $channel = factory(Channel::class)->states(['addSlug'])->create();
        $activeUser = Sanctum::actingAs(factory(User::class)->create());



        $tryCheckChannelIsLike = $this->getJson(route('isLikeChannel', [
            'channel' => Str::random(20),
        ]))->assertNotFound();

        $this->assertFalse($tryCheckChannelIsLike['ok']);
        $this->assertFalse($tryCheckChannelIsLike['isValid']);
        $this->assertEquals(['code' => 404], $tryCheckChannelIsLike['messages']);
    }

    /** @test */
    public function 로그인_안한채로_좋아요_여부_조회에_시도에_실패_하라(): void
    {
        $channel = factory(Channel::class)->states(['addSlug'])->create();

        $tryCheckChannelIsLike = $this->getJson(route('isLikeChannel', [
            'channel' => $channel->slug,
        ]))->assertUnauthorized();

        $this->assertFalse($tryCheckChannelIsLike['ok']);
        $this->assertFalse($tryCheckChannelIsLike['isValid']);
        $this->assertEquals(['code' => 401], $tryCheckChannelIsLike['messages']);
    }

    /** @test */
    public function 채널_좋아요_후_여부조회에_true_리턴을_받아라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states(['addSlug'])->create();


        $tryLikeToChannel = $this->postJson(route('likeChannel', [
            'channel' => $channel->slug
        ]))->assertCreated();

        $this->assertTrue($tryLikeToChannel['ok']);
        $this->assertTrue($tryLikeToChannel['isValid']);


        $tryCheckChannelIsLike = $this->getJson(route('isLikeChannel', [
            'channel' => $channel->slug,
        ]))->assertOk();

        $this->assertTrue($tryCheckChannelIsLike['ok']);
        $this->assertTrue($tryCheckChannelIsLike['isValid']);
        $this->assertTrue($tryCheckChannelIsLike['messages']['isLike']);
    }

    /** @test */
    public function 채널_좋아요안한채로_여부조회에_false_리턴을_받아라(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states(['addSlug'])->create();

        $tryCheckChannelIsLike = $this->getJson(route('isLikeChannel', [
            'channel' => $channel->slug,
        ]))->assertOk();

        $this->assertTrue($tryCheckChannelIsLike['ok']);
        $this->assertTrue($tryCheckChannelIsLike['isValid']);
        $this->assertFalse($tryCheckChannelIsLike['messages']['isLike']);
    }
}
