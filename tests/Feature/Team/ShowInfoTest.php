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
use Styde\Enlighten\Tests\EnlightenSetup;

/**
 * @todo 팀 배너 이미지 리턴시 배너아이디도 같이 리턴하는거 테스트코드 추가
 */

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
        $this->assertEquals(
            ['code' => 401],
            $tryLookupTeam['messages']
        );
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
        $this->assertEquals(
            ['code' => 401],
            $tryLookupTeam['messages']
        );
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
        $this->assertEquals(
            ['code' => 404],
            $tryLookupTeam['messages']
        );
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


        $this->assertEquals($team->broadcastAddress->map(fn (TeamBroadCast $teamBroadcast) => [
            'broadcastAddress' => $teamBroadcast->broadcast_address,
            'platform' => $teamBroadcast->platform,
            'platformKr' => TeamBroadCast::$platforms[$teamBroadcast->platform],
        ])->toArray(), $message['broadCastAddress']);
        $this->assertEquals(
            $team->bannerImages->map(fn (TeamBannerImages $image) => [
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
            $team->members->map(fn (User $member) => $member->id)->contains(
                 $activeUser->id
             )
        );

        $this->assertTrue(
            TeamMember::where([
                ['user_id', '=', $team->owner],
                ['team_id', '=', $team->id],

            ])->exists()
        );
        $this->assertIsString($team->slug, $message['slug']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookupPublicTeamInfo(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug', 'addBannerImage', 'addBroadcasts', ])->create();

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


        $this->assertEquals($team->broadcastAddress->map(fn (TeamBroadCast $teamBroadcast) => [
            'broadcastAddress' => $teamBroadcast->broadcast_address,
            'platform' => $teamBroadcast->platform,
            'platformKr' => TeamBroadCast::$platforms[$teamBroadcast->platform],
        ])->toArray(), $message['broadCastAddress']);

        $this->assertEquals(
            $team->bannerImages->map(fn (TeamBannerImages $image) => [
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
            $team->members->map(fn (User $member) => $member->id)->contains(
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
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookupPublicTeamInfoWithoutLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $team = factory(Team::class)->states(['addSlug', 'addBannerImage', 'addBroadcasts',])->create();

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


        $this->assertEquals($team->broadcastAddress->map(fn (TeamBroadCast $teamBroadcast) => [
            'broadcastAddress' => $teamBroadcast->broadcast_address,
            'platform' => $teamBroadcast->platform,
            'platformKr' => TeamBroadCast::$platforms[$teamBroadcast->platform],
        ])->toArray(), $message['broadCastAddress']);

        $this->assertEquals(
            $team->bannerImages->map(fn (TeamBannerImages $image) => [
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
            $team->members->map(fn (User $member) => $member->id)->contains(
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


        if (config('app.test.useRealImage')) {
            $this->get($message['logoImage'])->assertOk();
            collect($message['bannerImages'])->map(fn ($bannerImage) => $this->get($bannerImage['imageUrl']));
        }
    }
}
