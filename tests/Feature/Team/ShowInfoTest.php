<?php

declare(strict_types=1);

namespace Tests\Feature\Team;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Team\Team;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Models\Team\Member as TeamMember;
use App\Models\Team\Broadcast as TeamBroadCast;
use App\Models\Team\BannerImage as TeamBannerImages;
use App\Models\Team\Board\Article;

use Carbon\Carbon;
use Illuminate\Testing\TestResponse;
use Styde\Enlighten\Tests\EnlightenSetup;
use App\Repositories\Team\BoardRespository;

class ShowInfoTest extends TestCase
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
    public function failLookupTeamInfoWhenTeamIsPrivateButUserIsNotLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $team = factory(Team::class)->states(['addSlug'])->create();
        $team->is_public = false;
        $team->save();

        $tryLookupTeam = $this->getJson(route('getTeamInfoBySlug', [
            'teamSlug' => $team->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryLookupTeam['ok']);
        $this->assertFalse($tryLookupTeam['isValid']);
        $this->assertUnauthorizedMessages($tryLookupTeam['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookupTeamInfoWhenTeamIsPrivateButUseIsNotTeamMember(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create();
        $team->is_public = false;
        $team->save();

        $tryLookupTeam = $this->getJson(route('getTeamInfoBySlug', [
            'teamSlug' => $team->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryLookupTeam['ok']);
        $this->assertFalse($tryLookupTeam['isValid']);
        $this->assertUnauthorizedMessages($tryLookupTeam['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookupTeamInfoWhenTeamIsNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $tryLookupTeam = $this->getJson(route('getTeamInfoBySlug', [
            'teamSlug' => '-1213'
        ]))->assertNotFound();

        $this->assertFalse($tryLookupTeam['ok']);
        $this->assertFalse($tryLookupTeam['isValid']);
        $this->assertNotFoundMessages($tryLookupTeam['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookupPrivateTeamInfo(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug', 'addBannerImage', 'addBroadcasts', ])->create();
        $team->owner = $activeUser->id;

        $team->is_public = false;
        $team->save();

        $team = Team::find($team->id);
        TeamMember::factory()->create([
            'user_id' => $team->owner,
            'team_id' => $team->id
        ]);

        $tryLookupTeam = $this->getJson(route('getTeamInfoBySlug', [
            'teamSlug' => $team->slug
        ]))->assertOk();

        $this->assertTrue($tryLookupTeam['ok']);
        $this->assertTrue($tryLookupTeam['isValid']);

        $message = $tryLookupTeam['messages'];

        $this->assertEquals($team->id, $message['id']);
        $this->assertEquals($team->name, $message['name']);
        $this->assertEquals(route('teamLogoImage', [
            'logoImage' => $team->logo_image
        ]), $message['logoImage']);


        $this->assertEquals($team->broadcastAddress->map(fn (TeamBroadCast $teamBroadcast): array => [
            'broadcastAddress' => $teamBroadcast->broadcast_address,
            'platform' => $teamBroadcast->platform,
            'platformKr' => TeamBroadCast::$platforms[$teamBroadcast->platform],
            'broadcastId' => $teamBroadcast->id,
        ])->toArray(), $message['broadCastAddress']);
        $this->assertEquals(
            $team->bannerImages->map(fn (TeamBannerImages $image): array => [
                'id' => $image->id,
                'imageUrl' => route('teamBannerImage', [
                    'bannerImage' => $image->banner_image,
                ])
             ])->toArray(),
            $message['bannerImages']
        );

        $this->assertEquals($activeUser->id, $message['owner']);
        $this->assertEquals($team->is_public, $message['isPublic']);

        $this->assertTrue(
            $team->members->map(fn (User $member): int => $member->id)->contains(
                $activeUser->id
            )
        );

        $this->assertTrue(
            TeamMember::where([
                ['user_id', '=', $team->owner],
                ['team_id', '=', $team->id],

            ])->exists()
        );
        $this->assertEquals($team->slug, $message['slug']);
        $this->assertEquals(Carbon::parse($team->created_at)->format('Y-m-d H:i:s'), $message['createdAt']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookupPublicTeamInfo(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug', 'addBannerImage', 'addBroadcasts', 'addTeamBoardArticles'])->create();

        $team->is_public = true;
        $team->save();

        $team = Team::find($team->id);
        TeamMember::factory()->create([
            'user_id' => $team->owner,
            'team_id' => $team->id
        ]);

        $tryLookupTeam = $this->getJson(route('getTeamInfoBySlug', [
            'teamSlug' => $team->slug
        ]))->assertOk();

        $this->assertTrue($tryLookupTeam['ok']);
        $this->assertTrue($tryLookupTeam['isValid']);

        $message = $tryLookupTeam['messages'];

        $this->assertEquals($team->id, $message['id']);
        $this->assertEquals($team->name, $message['name']);
        $this->assertEquals(route('teamLogoImage', [
            'logoImage' => $team->logo_image
        ]), $message['logoImage']);


        $this->assertEquals($team->broadcastAddress->map(fn (TeamBroadCast $teamBroadcast): array => [
            'broadcastAddress' => $teamBroadcast->broadcast_address,
            'platform' => $teamBroadcast->platform,
            'platformKr' => TeamBroadCast::$platforms[$teamBroadcast->platform],
            'broadcastId' => $teamBroadcast->id,
        ])->toArray(), $message['broadCastAddress']);

        $this->assertEquals(
            $team->bannerImages->map(fn (TeamBannerImages $image): array => [
                'id' => $image->id,
                'imageUrl' => route('teamBannerImage', [
                    'bannerImage' => $image->banner_image,
                ])
             ])->toArray(),
            $message['bannerImages']
        );

        $this->assertEquals($team->owner, $message['owner']);
        $this->assertEquals($team->is_public, $message['isPublic']);

        $this->assertTrue(
            $team->members->map(fn (User $member): int => $member->id)->contains(
                $team->owner
            )
        );

        $this->assertTrue(
            TeamMember::where([
                ['user_id', '=', $team->owner],
                ['team_id', '=', $team->id],

            ])->exists()
        );
        $this->assertIsString($team->slug, $message['slug']);
        $this->assertEquals(Carbon::parse($team->created_at)->format('Y-m-d H:i:s'), $message['createdAt']);


        $articles = $team->articles()->whereBetween(Team::CREATED_AT, [
            Carbon::now()->format('Y-m-d 00:00:00'),
            Carbon::now()->format('Y-m-d 23:59:59'),
        ])->with('category')
          ->orderBy((new Team())->getKeyName(), 'desc')
          ->limit(BoardRespository::DEFAULT_ARTICLE_LATEST_COUNT)
          ->get()
          ->map(fn (Article $article): array => [
            'id' => $article->id,
            'title' => $article->title,
            'categoryName' => $article->category->name,
            'createdAt' => Carbon::parse($article->created_at)->format('Y-m-d H:i:s'),
        ])->toArray();


        $this->assertEquals(
            $message['latestArticles'],
            $articles
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookupPublicTeamInfoWithoutLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $team = factory(Team::class)->states(['addSlug', 'addBannerImage', 'addBroadcasts'])->create();

        $team->is_public = true;
        $team->save();

        $team = Team::find($team->id);
        TeamMember::factory()->create([
            'user_id' => $team->owner,
            'team_id' => $team->id
        ]);

        $tryLookupTeam = $this->getJson(route('getTeamInfoBySlug', [
            'teamSlug' => $team->slug
        ]))->assertOk();

        $this->assertTrue($tryLookupTeam['ok']);
        $this->assertTrue($tryLookupTeam['isValid']);

        $message = $tryLookupTeam['messages'];

        $this->assertEquals($team->id, $message['id']);
        $this->assertEquals($team->name, $message['name']);
        $this->assertEquals(route('teamLogoImage', [
            'logoImage' => $team->logo_image
        ]), $message['logoImage']);


        $this->assertEquals($team->broadcastAddress->map(fn (TeamBroadCast $teamBroadcast): array => [
            'broadcastAddress' => $teamBroadcast->broadcast_address,
            'platform' => $teamBroadcast->platform,
            'platformKr' => TeamBroadCast::$platforms[$teamBroadcast->platform],
            'broadcastId' => $teamBroadcast->id,
        ])->toArray(), $message['broadCastAddress']);

        $this->assertEquals(
            $team->bannerImages->map(fn (TeamBannerImages $image): array => [
                'id' => $image->id,
                'imageUrl' => route('teamBannerImage', [
                    'bannerImage' => $image->banner_image,
                ]),
             ])->toArray(),
            $message['bannerImages']
        );

        $this->assertEquals($team->owner, $message['owner']);
        $this->assertEquals($team->is_public, $message['isPublic']);

        $this->assertTrue(
            $team->members->map(fn (User $member): int => $member->id)->contains(
                $team->owner
            )
        );

        $this->assertTrue(
            TeamMember::where([
                ['user_id', '=', $team->owner],
                ['team_id', '=', $team->id],

            ])->exists()
        );
        $this->assertIsString($team->slug, $message['slug']);
        $this->assertEquals(Carbon::parse($team->created_at)->format('Y-m-d H:i:s'), $message['createdAt']);

        if (config('app.test.useRealImage')) {
            $this->get($message['logoImage'])->assertOk();
            collect($message['bannerImages'])->map(fn (array $bannerImage): TestResponse => $this->get($bannerImage['imageUrl']));
        }
    }
}
