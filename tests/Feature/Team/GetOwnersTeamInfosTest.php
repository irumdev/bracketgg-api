<?php

declare(strict_types=1);

namespace Tests\Feature\Team;

use App\Models\GameType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Team\Team;
use App\Models\Team\BannerImage;
use App\Models\Team\Board\Article;
use App\Models\Team\Broadcast;
use Illuminate\Support\Carbon;
use Styde\Enlighten\Tests\EnlightenSetup;

class GetOwnersTeamInfosTest extends TestCase
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
    public function successLookupTeamInfoWhenLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $teams = collect(range(0, $activeUser->create_team_limit -1))->map(fn (int $_): Team => factory(Team::class)->states([
            'addSlug', 'addSignedMembers', 'addBannerImage',
            'addBroadcasts', 'addOperateGame', 'addSmallTeamArticlesWithSavedImages'
        ])->create([
            'owner' => $activeUser->id
        ]));


        $tryLookupTeamInfo = $this->getJson(route('team.showInfoByOwnerId', [
            'owner' => $activeUser->id
        ]))->assertOk();


        $this->assertTrue($tryLookupTeamInfo['ok']);
        $this->assertTrue($tryLookupTeamInfo['isValid']);


        $teams->each(function (Team $team) use ($tryLookupTeamInfo, $activeUser): void {
            $messages = collect($tryLookupTeamInfo['messages']);
            $teamId = $team->id;

            $searchReslt = $messages->search(fn (array $message): bool => $message['id'] === $teamId);
            $this->assertTrue($searchReslt !== false);

            $compareTeam = $messages->get($searchReslt);

            $teamLogoImage = $team->logo_image ? route('teamLogoImage', [
                'logoImage' => $team->logo_image
            ]) : null;


            $teamBannerImages = $team->bannerImages->map(fn (BannerImage $image): array => [
                'id' => $image->id,
                'imageUrl' => route('teamBannerImage', [
                    'bannerImage' => $image->banner_image,
                ]),
            ])->toArray();


            $latestArticles = $team->articles()->whereBetween('created_at', [
                Carbon::now()->format('Y-m-d 00:00:00'),
                Carbon::now()->format('Y-m-d 23:59:59'),
            ]);




            $this->assertEquals($latestArticles->count(), $compareTeam['latestArticlesCount']);

            $latestArticles = $latestArticles->with('category')
                                             ->orderBy('id', 'desc')
                                             ->limit(10)
                                             ->get()
                                             ->map(fn (Article $article): array => [
                                                 'id' => $article->id,
                                                 'title' => $article->title,
                                                 'categoryName' => $article->category->name,
                                                 'createdAt' => Carbon::parse($article->created_at)->format('Y-m-d H:i:s'),
                                             ])
                                             ->toArray();

            $this->assertEquals(
                $latestArticles,
                $compareTeam['latestArticles']
            );
            $broadCasts = $team->broadcastAddress->map(fn (Broadcast $teamBroadcast): array => [
                'broadcastAddress' => $teamBroadcast->broadcast_address,
                'platform' => $teamBroadcast->platform,
                'platformKr' => Broadcast::$platforms[$teamBroadcast->platform],
                'broadcastId' => $teamBroadcast->id,
            ])->toArray();

            $operateGame = $team->operateGames->map(fn (GameType $game): string => $game->name)->toArray();

            $this->assertEquals($teamId, $compareTeam['id']);
            $this->assertEquals($team->name, $compareTeam['name']);
            $this->assertEquals($team->member_count, $compareTeam['memberCount']);
            $this->assertEquals($teamLogoImage, $compareTeam['logoImage']);
            $this->assertEquals(
                $teamBannerImages,
                $compareTeam['bannerImages']
            );
            $this->assertEquals($broadCasts, $compareTeam['broadCastAddress']);
            $this->assertEquals($activeUser->id, $compareTeam['owner']);
            $this->assertEquals($team->slug, $compareTeam['slug']);
            $this->assertEquals($operateGame, $compareTeam['operateGames']);
            $this->assertEquals($team->is_public, $compareTeam['isPublic']);

            if (config('app.test.useRealImage')) {
                collect($compareTeam['bannerImages'])->each(function (array $bannerImage): void {
                    $this->get($bannerImage['imageUrl'])->assertOk();
                });
                $this->get($teamLogoImage)->assertOk();
            }
        });
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookupTeamInfoWhenNotLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $owner = factory(User::class)->create();

        $teams = collect(range(0, $owner->create_team_limit -1))->map(fn (int $_): Team => factory(Team::class)->states([
            'addSlug', 'addSignedMembers', 'addBannerImage',
            'addBroadcasts', 'addOperateGame'
        ])->create([
            'owner' => $owner->id
        ]));


        $tryLookupTeamInfo = $this->getJson(route('team.showInfoByOwnerId', [
            'owner' => $owner->id
        ]))->assertUnauthorized();


        $this->assertFalse($tryLookupTeamInfo['ok']);
        $this->assertFalse($tryLookupTeamInfo['isValid']);
        $this->assertUnauthorizedMessages($tryLookupTeamInfo['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookupTeamInfoWhenOwnerHasNoTeam(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $tryLookupTeamInfo = $this->getJson(route('team.showInfoByOwnerId', [
            'owner' => $activeUser->id
        ]))->assertNotFound();
        $this->assertFalse($tryLookupTeamInfo['ok']);
        $this->assertFalse($tryLookupTeamInfo['isValid']);
        $this->assertNotFoundMessages($tryLookupTeamInfo['messages']);
    }
}
