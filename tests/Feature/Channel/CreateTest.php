<?php

namespace Tests\Feature\Channel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Models\Channel\Channel;
use Styde\Enlighten\Tests\EnlightenSetup;

class CreateTest extends TestCase
{
    use EnlightenSetup;
    private string $testUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
        $this->testUrl = route('createChannel');
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateChannelWithoutChannelName(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(
            factory(User::class)->states(['addProfileImage'])->create()
        );
        $tryCreateChannel = $this->postJson($this->testUrl, [

        ])->assertStatus(422);
        $this->assertFalse($tryCreateChannel['ok']);
        $this->assertFalse($tryCreateChannel['isValid']);
        $this->assertEquals(1, $tryCreateChannel['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateChannelWhenChannelNameIsLong(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(
            factory(User::class)->states(['addProfileImage'])->create()
        );
        $tryCreateChannel = $this->postJson($this->testUrl, [
            'name' => \Illuminate\Support\Str::random(21)
        ])->assertStatus(422);

        $this->assertFalse($tryCreateChannel['ok']);
        $this->assertFalse($tryCreateChannel['isValid']);
        $this->assertEquals(4, $tryCreateChannel['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateChannelWhenChannelNameIsDuplicate(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(
            factory(User::class)->states(['addProfileImage'])->create()
        );

        $alreadyExistsChannel = factory(Channel::class)->states([
            'addSlug'
        ])->create();

        $tryCreateChannel = $this->postJson($this->testUrl, [
            'name' => $alreadyExistsChannel->name,
        ])->assertStatus(422);

        $this->assertFalse($tryCreateChannel['ok']);
        $this->assertFalse($tryCreateChannel['isValid']);
        $this->assertEquals(5, $tryCreateChannel['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateChannelWhenChannelCreateCountIsExceed(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(
            factory(User::class)->states(['addProfileImage'])->create()
        );

        $alreadyExistsChannel = factory(Channel::class, 5)->states([
            'addSlug'
        ])->create();

        $alreadyExistsChannel->map(function (Channel $channel) use ($user) {
            $channel->owner = $user->id;
            $channel->save();
        });

        $tryCreateChannel = $this->postJson($this->testUrl, [
            'name' => \Illuminate\Support\Str::random(20),
        ])->assertUnauthorized();

        $this->assertFalse($tryCreateChannel['ok']);
        $this->assertFalse($tryCreateChannel['isValid']);
        $this->assertEquals(1, $tryCreateChannel['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successCreateChannel(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(
            factory(User::class)->states(['addProfileImage'])->create()
        );

        $tryCreateChannel = $this->postJson($this->testUrl, [
            'name' => \Illuminate\Support\Str::random(20),
        ])->assertOk();

        $dbChannel = User::find($user->id)->channels->first();

        $channel = $tryCreateChannel['messages'];

        $this->assertTrue($tryCreateChannel['ok']);
        $this->assertTrue($tryCreateChannel['isValid']);

        $this->assertEquals($dbChannel->id, $channel['id']);
        $this->assertEquals($dbChannel->name, $channel['name']);
        $this->assertEquals($dbChannel->owner, $channel['owner']);
        $this->assertEquals([], $channel['bannerImages']);
        $this->assertEquals([], $channel['broadCastAddress']);
        $this->assertEquals(0, $channel['likeCount']);
        $this->assertEquals(0, $channel['followerCount']);
        $this->assertEquals($dbChannel->slug, $channel['slug']);
    }
}
