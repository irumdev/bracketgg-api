<?php

namespace Tests\Feature\Channel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Channel\Channel;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Properties\Paginate;
use Illuminate\Support\Carbon;

class ShowFollowerListTest extends TestCase
{
    /** @test */
    public function failLookUpFollowersWhenUserIsNotLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryLookUpFollowersList = $this->getJson(route('getFollower', [
            'slug' => '-1',
        ]))->assertUnauthorized();

        $this->assertFalse($tryLookUpFollowersList['ok']);
        $this->assertFalse($tryLookUpFollowersList['isValid']);

        $this->assertEquals(
            ['code' => 401],
            $tryLookUpFollowersList['messages']
        );
    }

    /** @test */
    public function failLookUpFollowersWhenChannelNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activedUser = Sanctum::actingAs(factory(User::class)->create());
        $tryLookUpFollowersList = $this->getJson(route('getFollower', [
            'slug' => '-1'
        ]))->assertNotFound();

        $this->assertFalse($tryLookUpFollowersList['ok']);
        $this->assertFalse($tryLookUpFollowersList['isValid']);

        $this->assertEquals(
            ['code' => 404],
            $tryLookUpFollowersList['messages']
        );
    }

    /** @test */
    public function successLookupChannelFollowersWithPaginate(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $channel = factory(Channel::class)->states([
            'addBannerImage','hasManyFollower','addBroadcasts', 'addSlug', 'hasLike'
        ])->create();

        $activedUser = Sanctum::actingAs(factory(User::class)->create());
        $current = 1;

        do {
            $requestUrl = route('getFollower', [
                'slug' => $channel->slug
            ]) . '?' . http_build_query([
                'page' => $current
            ]);

            $tryLookUpFollowersList = $this->getJson($requestUrl)->assertOk();

            $this->assertTrue($tryLookUpFollowersList['ok']);
            $this->assertTrue($tryLookUpFollowersList['isValid']);

            $responseFollowers = $tryLookUpFollowersList['messages']['followers'];

            $followerIds = collect($channel->followers->map(fn ($follower) => $follower->id));
            array_map(function ($follower) use ($followerIds) {
                $user = User::find($follower['id']);
                $this->assertNotNull($user);
                $userInfo = $followerIds->search($user->id);
                $this->assertTrue($userInfo !== false);
                $this->assertEquals(Carbon::parse($user->create_at)->format('Y-m-d H:i:s'), $follower['createdAt']);
            }, $responseFollowers);
            $current += 1;
        } while ($tryLookUpFollowersList['messages']['meta']['hasMorePage']);
    }

    /** @test */
    public function successLookupChannelFollowersButChannelDontHaveAnyFollower(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $channel = factory(Channel::class)->states([
            'addBannerImage', 'addBroadcasts', 'addSlug', 'hasLike'
        ])->create();

        $activedUser = Sanctum::actingAs(factory(User::class)->create());
        $requestUrl = route('getFollower', ['slug' => $channel->slug]);
        $tryLookUpFollowersList = $this->getJson($requestUrl);

        $this->assertTrue($tryLookUpFollowersList['ok']);
        $this->assertTrue($tryLookUpFollowersList['isValid']);

        $this->assertFalse($tryLookUpFollowersList['messages']['meta']['hasMorePage']);
        $this->isNull($tryLookUpFollowersList['messages']['meta']['next']);
        $this->isNull($tryLookUpFollowersList['messages']['meta']['prev']);
        $this->assertEquals([], $tryLookUpFollowersList['messages']['followers']);
    }
}
