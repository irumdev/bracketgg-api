<?php

namespace Tests\Feature\Channel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Channel\Channel;
use App\Models\User;
use App\Models\Channel\Follower as ChannelFollower;
use Laravel\Sanctum\Sanctum;
use App\Properties\Paginate;
use Illuminate\Support\Carbon;
use Styde\Enlighten\Tests\EnlightenSetup;

class ShowFollowerListTest extends TestCase
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
    public function failLookUpFollowersWhenUserIsNotLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryLookUpFollowersList = $this->getJson(route('channel.getFollowers', [
            'slug' => '-1',
        ]))->assertUnauthorized();

        $this->assertFalse($tryLookUpFollowersList['ok']);
        $this->assertFalse($tryLookUpFollowersList['isValid']);
        $this->assertUnauthorizedMessages($tryLookUpFollowersList['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookUpFollowersWhenChannelNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activedUser = Sanctum::actingAs(factory(User::class)->create());
        $tryLookUpFollowersList = $this->getJson(route('channel.getFollowers', [
            'slug' => '-1'
        ]))->assertNotFound();

        $this->assertFalse($tryLookUpFollowersList['ok']);
        $this->assertFalse($tryLookUpFollowersList['isValid']);
        $this->assertNotFoundMessages($tryLookUpFollowersList['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookupChannelFollowersWithPaginate(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $channel = factory(Channel::class)->states([
            'addBannerImage','hasManyFollower','addBroadcasts', 'addSlug', 'hasLike'
        ])->create();

        ChannelFollower::where('channel_id', $channel->id)->get()->map(function (ChannelFollower $key): void {
            $key->created_at = $key->created_at->addDays(2);
            $key->updated_at = $key->updated_at->addDays(2);
            $key->save();
        });

        $activedUser = Sanctum::actingAs(factory(User::class)->create());
        $current = 1;

        do {
            $requestUrl = route('channel.getFollowers', [
                'slug' => $channel->slug
            ]) . '?' . http_build_query([
                'page' => $current
            ]);

            $tryLookUpFollowersList = $this->getJson($requestUrl)->assertOk();

            $this->assertTrue($tryLookUpFollowersList['ok']);
            $this->assertTrue($tryLookUpFollowersList['isValid']);

            $responseFollowers = $tryLookUpFollowersList['messages']['followers'];

            $followerIds = collect($channel->followers->map(fn (User $follower): int => $follower->id));
            array_map(function (array $follower) use ($followerIds, $channel): void {
                $user = User::find($follower['id']);
                $this->assertNotNull($user);
                $followedAt = \App\Models\Channel\Follower::where([
                    ['channel_id', '=', $channel->id],
                    ['user_id', '=', $user->id],
                ])->first()->created_at;
                $userInfo = $followerIds->search($user->id);
                $this->assertTrue($userInfo !== false);
                $this->assertEquals(Carbon::parse($user->created_at)->format('Y-m-d H:i:s'), $follower['createdAt']);
                $this->assertEquals(Carbon::parse($followedAt)->format('Y-m-d H:i:s'), $follower['followedAt']);
            }, $responseFollowers);
            $current += 1;
        } while ($tryLookUpFollowersList['messages']['meta']['hasMorePage']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookupChannelFollowersButChannelDontHaveAnyFollower(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $channel = factory(Channel::class)->states([
            'addBannerImage', 'addBroadcasts', 'addSlug', 'hasLike'
        ])->create();

        $activedUser = Sanctum::actingAs(factory(User::class)->create());
        $requestUrl = route('channel.getFollowers', ['slug' => $channel->slug]);
        $tryLookUpFollowersList = $this->getJson($requestUrl);

        $this->assertTrue($tryLookUpFollowersList['ok']);
        $this->assertTrue($tryLookUpFollowersList['isValid']);

        $this->assertFalse($tryLookUpFollowersList['messages']['meta']['hasMorePage']);
        $this->isNull($tryLookUpFollowersList['messages']['meta']['next']);
        $this->isNull($tryLookUpFollowersList['messages']['meta']['prev']);
        $this->assertEquals([], $tryLookUpFollowersList['messages']['followers']);
    }
}
