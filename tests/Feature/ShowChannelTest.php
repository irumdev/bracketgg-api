<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Channel;
use App\Services\ChannelService;
use App\Repositories\ChannelRepository;
use App\Helpers\ResponseBuilder;

class ShowChannelTest extends TestCase
{
    private function reslover(): ChannelService
    {
        return (new ChannelService(new ChannelRepository(new Channel()), new ResponseBuilder()));
    }

    /** @test */
    public function 슬러그로_존재하는_채널정보_조회를_성공하라(): void
    {
        $channel = factory(Channel::class, random_int(1, 3))->states([
            'addBannerImage','hasFollower','addBroadcasts', 'addSlug'
        ])->create();

        $channelSlug = $channel->first()->slug;
        $channelId = $channel->first()->id;

        $testRequestUrl = route('findChannelById', [
            'slug' => $channelSlug,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();

        $service = $this->reslover()->findChannelById((string)$channelId);
        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $this->assertEquals(
            $service->toArray(),
            ($response['messages'])
        );
    }

    /** @test */
    public function 채널이름으로_존재하는_채널정보_조회를_성공하라(): void
    {
        $channel = factory(Channel::class, random_int(1, 3))->states([
            'addBannerImage','hasFollower','addBroadcasts', 'addSlug'
        ])->create()->first();

        $channelSlug = $channel->slug;
        $channelId = $channel->id;

        $channelName = $channel->name;

        $testRequestUrl = route('findChannelByName', [
            'channelName' => $channelName,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();

        $service = $this->reslover()->findChannelById((string)$channelId);
        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $message = $response['messages'];

        $this->assertEquals($channelId, $message['id']);
        $this->assertEquals($channelName, $message['channelName']);

        $this->assertEquals(
            $service->toArray(),
            ($response['messages'])
        );
    }

    /** @test */
    public function 존재하지않는_슬러그로_채널정보_조회를_실패하라(): void
    {
        $testRequestUrl = route('findChannelById', [
            'slug' => '-999',
        ]);

        $response = $this->getJson($testRequestUrl)->assertNotFound();

        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);

        $this->assertEquals(
            ['code' => 404],
            ($response['messages'])
        );
    }


    /** @test */
    public function 존재하지않는_채널이름으로_채널정보_조회를_실패하라(): void
    {
        $testRequestUrl = route('findChannelByName', [
            'channelName' => '-999',
        ]);

        $response = $this->getJson($testRequestUrl)->assertNotFound();

        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);

        $this->assertEquals(
            ['code' => 404],
            ($response['messages'])
        );
    }
}
