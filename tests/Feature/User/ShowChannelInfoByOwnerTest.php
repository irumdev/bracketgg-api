<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Channel\Channel;
use App\Services\ChannelService;
use App\Repositories\ChannelRepository;
use App\Helpers\ResponseBuilder;
use App\Repositories\Channel\BoardRespository;
use Styde\Enlighten\Tests\EnlightenSetup;

class ShowChannelInfoByOwnerTest extends TestCase
{
    use EnlightenSetup;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
    }

    private function serviceResolver(): ChannelService
    {
        return new ChannelService(new BoardRespository(), new ChannelRepository(new Channel()), new ResponseBuilder());
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookUpChannelWhenChannelHasBannerImageAndFollowers(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );

        $channel = factory(Channel::class, random_int(1, 3))->states([
            'addBannerImage','hasFollower','addSlug'
        ])->create();

        $channelOwner = $channel->first()->owner;

        $testRequestUrl = route('showChannelByOwnerId', [
            'user' => $channelOwner,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();
        $service = $this->serviceResolver()->findChannelsByUserId((string)$channelOwner);
        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $this->assertJsonStringNotEqualsJsonString(
            $service->toJson(),
            json_encode($response['messages']['channels'])
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookUpChannelWhenChannelHasBannerImageAndFollowersAndBroadcastAddress(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );
        $channel = factory(Channel::class, random_int(1, 3))->states([
            'addBannerImage','hasFollower', 'addBroadcasts', 'addSlug'
        ])->create();

        $channelOwner = $channel->first()->owner;
        $testRequestUrl = route('showChannelByOwnerId', [
            'user' => $channelOwner,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();

        $service = $this->serviceResolver()->findChannelsByUserId((string)$channelOwner);
        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $this->assertJsonStringNotEqualsJsonString(
            $service->toJson(),
            json_encode($response['messages']['channels'])
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookUpChannelWhenChannelHasBannerImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );

        $channel = factory(Channel::class, random_int(1, 3))->states([
            'addBannerImage', 'addSlug'
        ])->create();

        $channelOwner = $channel->first()->owner;
        $testRequestUrl = route('showChannelByOwnerId', [
            'user' => $channelOwner,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();
        $service = $this->serviceResolver()->findChannelsByUserId((string)$channelOwner);
        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $this->assertJsonStringNotEqualsJsonString(
            $service->toJson(),
            json_encode($response['messages']['channels'])
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookUpChannelWhenUserDontHaveChannel(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );

        $channel = factory(Channel::class)->states([
            'addBannerImage', 'addSlug'
        ])->create();

        $testRequestUrl = route('showChannelByOwnerId', [
            'user' => '-9999',
        ]);

        $response = $this->getJson($testRequestUrl)->assertNotFound();
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(['code' => 404], $response['messages']);
    }
}
