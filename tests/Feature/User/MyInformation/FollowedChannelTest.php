<?php

declare(strict_types=1);

namespace Tests\Feature\User\MyInformation;

use App\Models\Channel\Channel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Channel\Follower as ChannelFollower;
use Styde\Enlighten\Tests\EnlightenSetup;

class FollowedChannelTest extends TestCase
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
    public function failLookupWhenUserHasNotFollowedChannel(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());

        $requestUrl = route('getFollowedChannel');

        $tryLookupFollowedChannel = $this->getJson(
            $requestUrl . '?' . http_build_query([
                'page' => 1
            ])
        )->assertNotFound();

        $this->assertFalse($tryLookupFollowedChannel['ok']);
        $this->assertFalse($tryLookupFollowedChannel['isValid']);
        $this->assertNotFoundMessages($tryLookupFollowedChannel['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookupWhenUserIsNotLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = factory(User::class)->create();

        $requestUrl = route('getFollowedChannel');

        $tryLookupFollowedChannel = $this->getJson(
            $requestUrl . '?' . http_build_query([
                'page' => 1
            ])
        )->assertUnauthorized();

        $this->assertFalse($tryLookupFollowedChannel['ok']);
        $this->assertFalse($tryLookupFollowedChannel['isValid']);

        $this->assertUnauthorizedMessages($tryLookupFollowedChannel['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookupFollowedChannel(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());
        $channels = factory(Channel::class, 50)->states([
            'addSlug'
        ])->create();

        $channels->each(function (Channel $channel) use ($requestUser): void {
            ChannelFollower::create([
                'user_id' => $requestUser->id,
                'channel_id' => $channel->id,
            ]);

            $channel->follwer_count += 1;
            $channel->save();
        });
        $requestUrl = route('getFollowedChannel');

        $page = 1;
        do {
            $tryLookupFollowedChannel = $this->getJson(
                $requestUrl . '?' . http_build_query([
                    'page' => $page++
                ])
            )->assertOk();

            $this->assertTrue($tryLookupFollowedChannel['ok']);
            $this->assertTrue($tryLookupFollowedChannel['isValid']);

            collect($tryLookupFollowedChannel['messages']['followedChannels'])->each(function ($followedChannel) use ($channels): void {
                $this->assertTrue(
                    $channels->where('id', $followedChannel['id'])->count() >= 1
                );
            });
        } while ($tryLookupFollowedChannel['messages']['meta']['hasMorePage']);
    }
}
