<?php

namespace Tests\Feature\Team;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Team\Team;
use App\Models\Team\BannerImage;
use App\Models\Team\Broadcast;

class ShowOwnersTeams extends TestCase
{
    /** @test */
    public function successLookupTeamInfoWhenLogin(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $teams = collect(range(0, $activeUser->create_team_limit -1))->map(fn ($item) => factory(Team::class)->states([
            'addSlug', 'addMembers', 'addBannerImage',
            'addBroadcasts', 'addOperateGame'
        ])->create([
            'owner' => $activeUser->id
        ]));


        $tryLookupTeamInfo = $this->getJson(route('showTeamByOwnerId', [
            'owner' => $activeUser->id
        ]))->assertOk();


        $this->assertTrue($tryLookupTeamInfo['ok']);
        $this->assertTrue($tryLookupTeamInfo['isValid']);


        $teams->each(function ($team) use ($tryLookupTeamInfo, $activeUser) {
            $messages = collect($tryLookupTeamInfo['messages']);
            $teamId = $team->id;

            $searchReslt = $messages->search(fn ($message) => $message['id'] === $teamId);
            $this->assertTrue($searchReslt !== false);

            $compareTeam = $messages->get($searchReslt);

            $teamLogoImage = $team->logo_image ? route('teamLogoImage', [
                'logoImage' => $team->logo_image
            ]) : null;


            $teamBannerImages = $team->bannerImages->map(fn (BannerImage $image) => $image->banner_image ? route('teamBannerImage', [
                'bannerImage' => $image->banner_image,
            ]) : null)->toArray();

            $broadCasts = $team->broadcastAddress->map(fn (Broadcast $teamBroadcast) => [
                'broadcastAddress' => $teamBroadcast->broadcast_address,
                'platform' => $teamBroadcast->platform,
                'platformKr' => Broadcast::$platforms[$teamBroadcast->platform],
            ])->toArray();

            $operateGame = $team->operateGames->map(fn ($game) => $game->name)->toArray();

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
                collect($teamBannerImages)->each(function ($bannerImage) {
                    $this->get($bannerImage)->assertOk();
                });
                $this->get($teamLogoImage)->assertOk();
            }
        });
    }

    /** @test */
    public function failLookupTeamInfoWhenNotLogin(): void
    {
        $owner = factory(User::class)->create();

        $teams = collect(range(0, $owner->create_team_limit -1))->map(fn ($item) => factory(Team::class)->states([
            'addSlug', 'addMembers', 'addBannerImage',
            'addBroadcasts', 'addOperateGame'
        ])->create([
            'owner' => $owner->id
        ]));


        $tryLookupTeamInfo = $this->getJson(route('showTeamByOwnerId', [
            'owner' => $owner->id
        ]))->assertUnauthorized();


        $this->assertFalse($tryLookupTeamInfo['ok']);
        $this->assertFalse($tryLookupTeamInfo['isValid']);
        $this->assertEquals(['code' => 401], $tryLookupTeamInfo['messages']);
    }

    /** @test */
    public function failLookupTeamInfoWhenOwnerHasNoTeam(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $tryLookupTeamInfo = $this->getJson(route('showTeamByOwnerId', [
            'owner' => $activeUser->id
        ]))->assertNotFound();
        $this->assertFalse($tryLookupTeamInfo['ok']);
        $this->assertFalse($tryLookupTeamInfo['isValid']);
        $this->assertEquals(['code' => 404], $tryLookupTeamInfo['messages']);
    }
}
