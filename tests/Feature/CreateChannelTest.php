<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Http\Requests\CreateChannelRequest;

use App\Models\User;
use App\Models\Channel;

use Laravel\Sanctum\Sanctum;

class CreateChannelTest extends TestCase
{
    private string $testUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testUrl = route('createChannel');
    }

    /** @test */
    public function 이름을_안넣고_채널생성에_실패하라(): void
    {
        $user = Sanctum::actingAs(
            factory(User::class)->states(['addProfileImage'])->create()
        );
        $tryCreateChannel = $this->postJson($this->testUrl, [

        ])->assertStatus(422);
        $this->assertFalse($tryCreateChannel['ok']);
        $this->assertFalse($tryCreateChannel['isValid']);
        $this->assertEquals(1, $tryCreateChannel['messages']['code']);
    }

    /** @test */
    public function 이름_최대길이_넘겨서_채널생성에_실패하라(): void
    {
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

    /** @test */
    public function 이름이_중복되서_채널생성에_실패하라(): void
    {
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

    /** @test */
    public function 최대_체널_생성개수_제한에_의하여_채널생성에_실패하라(): void
    {
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

    /** @test */
    public function 채널생성에_성공하라(): void
    {
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
        $this->assertEquals($channel['id'], $dbChannel->id);
        $this->assertEquals($channel['channelName'], $dbChannel->name);


        $this->assertEquals($channel['bannerImages'], []);
        $this->assertEquals($channel['broadCastAddress'], []);

        $this->assertEquals($channel['likeCount'], 0);
        $this->assertEquals($channel['followerCount'], 0);

        $this->assertEquals($dbChannel->slug, $channel['slug']);
    }
}
