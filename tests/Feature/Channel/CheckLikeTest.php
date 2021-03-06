<?php

namespace Tests\Feature\Channel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;
use App\Models\Channel\Channel;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Styde\Enlighten\Tests\EnlightenSetup;

class CheckLikeTest extends TestCase
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
    public function failLookUpChannelIsFanWhenChannelNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $channel = factory(Channel::class)->states(['addSlug'])->create();
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $tryCheckChannelIsLike = $this->getJson(route('channel.isLike', [
            'slug' => Str::random(20),
        ]))->assertNotFound();

        $this->assertFalse($tryCheckChannelIsLike['ok']);
        $this->assertFalse($tryCheckChannelIsLike['isValid']);
        $this->assertNotFoundMessages($tryCheckChannelIsLike['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookUpChannelIsFanWhenUserisNotLogined(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $channel = factory(Channel::class)->states(['addSlug'])->create();

        $tryCheckChannelIsLike = $this->getJson(route('channel.isLike', [
            'slug' => $channel->slug,
        ]))->assertUnauthorized();

        $this->assertFalse($tryCheckChannelIsLike['ok']);
        $this->assertFalse($tryCheckChannelIsLike['isValid']);
        $this->assertUnauthorizedMessages($tryCheckChannelIsLike['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function getTrueWhenLikeChannelAndLookupChannelIsLike(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $channel = factory(Channel::class)->states(['addSlug'])->create();


        $tryLikeToChannel = $this->patchJson(route('channel.like', [
            'slug' => $channel->slug
        ]))->assertCreated();

        $this->assertTrue($tryLikeToChannel['ok']);
        $this->assertTrue($tryLikeToChannel['isValid']);


        $tryCheckChannelIsLike = $this->getJson(route('channel.isLike', [
            'slug' => $channel->slug,
        ]))->assertOk();

        $this->assertTrue($tryCheckChannelIsLike['ok']);
        $this->assertTrue($tryCheckChannelIsLike['isValid']);
        $this->assertTrue($tryCheckChannelIsLike['messages']['isLike']);
    }

    /**
     * @test
     * @enlighten
     */
    public function getFalseWhenUnLikeChannelAndLookupChannelIsLike(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $channel = factory(Channel::class)->states(['addSlug'])->create();

        $tryCheckChannelIsLike = $this->getJson(route('channel.isLike', [
            'slug' => $channel->slug,
        ]))->assertOk();

        $this->assertTrue($tryCheckChannelIsLike['ok']);
        $this->assertTrue($tryCheckChannelIsLike['isValid']);
        $this->assertFalse($tryCheckChannelIsLike['messages']['isLike']);
    }
}
