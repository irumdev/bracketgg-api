<?php

declare(strict_types=1);

namespace Tests\Feature\Team;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Team\Team;
use App\Models\Team\Broadcast as TeamBroadCast;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Http\Requests\Team\UpdateInfoWithOutBannerRequest;

class UpdateInformationTest extends TestCase
{
    /** @test */
    public function failUpdateTeamSlugWhenSlugIsNotUnique(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create();
        $team->owner = $activeUser->id;
        $team->save();

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug
        ]);
        $tryCreateTeam = $this->postJson($requestUrl, [
            'slug' => $team->slug
        ])->assertStatus(422);
        $this->assertFalse($tryCreateTeam['ok']);
        $this->assertFalse($tryCreateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::SLUG_IS_NOT_UNIQUE, $tryCreateTeam['messages']['code']);
    }

    /** @test */
    public function failUpdateTeamSlugWhenSlugIsLong(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create(['owner' => $activeUser->id]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug
        ]);
        $tryCreateTeam = $this->postJson($requestUrl, [
            'slug' => \Illuminate\Support\Str::random(17)
        ])->assertStatus(422);
        $this->assertFalse($tryCreateTeam['ok']);
        $this->assertFalse($tryCreateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::SLUG_IS_LONG, $tryCreateTeam['messages']['code']);
    }

    /** @test */
    public function failUpdateTeamSlugWhenSlugIsShort(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create(['owner' => $activeUser->id]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug
        ]);
        $tryCreateTeam = $this->postJson($requestUrl, [
            'slug' => \Illuminate\Support\Str::random(3)
        ])->assertStatus(422);
        $this->assertFalse($tryCreateTeam['ok']);
        $this->assertFalse($tryCreateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::SLUG_IS_SHORT, $tryCreateTeam['messages']['code']);
    }

    /** @test */
    public function failUpdateTeamSlugWhenSlugPatternIsNotMatch(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create(['owner' => $activeUser->id]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug
        ]);
        collect([
            \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(13)),
            \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(13)) . '*'
        ])->each(function ($illeagleSlug) use ($requestUrl) {
            $tryCreateTeam = $this->postJson($requestUrl, [
                'slug' => $illeagleSlug
            ])->assertStatus(422);
            $this->assertFalse($tryCreateTeam['ok']);
            $this->assertFalse($tryCreateTeam['isValid']);
            $this->assertEquals(UpdateInfoWithOutBannerRequest::SLUG_PATTERN_IS_NOT_MATCH, $tryCreateTeam['messages']['code']);
        });
    }

    /** @test */
    public function failUpdateTeamSlugWhenPublicStatusIsNotBoolean(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create(['owner' => $activeUser->id]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryCreateTeam = $this->postJson($requestUrl, [
            'is_public' => 'asdf',
        ])->assertStatus(422);
        $this->assertFalse($tryCreateTeam['ok']);
        $this->assertFalse($tryCreateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::PUBLIC_STATUS_IS_NOT_BOOLEAN, $tryCreateTeam['messages']['code']);
    }

    /** @test */
    public function failUpdateTeamSlugWhenGameCategoryIsNotArray(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create(['owner' => $activeUser->id]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryCreateTeam = $this->postJson($requestUrl, [
            'games' => 'asdf',
        ])->assertStatus(422);
        $this->assertFalse($tryCreateTeam['ok']);
        $this->assertFalse($tryCreateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::GAME_CATEGORY_IS_NOT_ARRAY, $tryCreateTeam['messages']['code']);
    }

    /** @test */
    public function failUpdateTeamSlugWhenGameCategoryItemIsLong(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create(['owner' => $activeUser->id]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryCreateTeam = $this->postJson($requestUrl, [
            'games' => [\Illuminate\Support\Str::random(256), \Illuminate\Support\Str::random(256)],
        ])->assertStatus(422);
        $this->assertFalse($tryCreateTeam['ok']);
        $this->assertFalse($tryCreateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::GAME_CATEGORY_ITEM_IS_LONG, $tryCreateTeam['messages']['code']);
    }

    /** @test */
    public function successUpdateAllItem(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug', 'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $gameTypes = [
            \Illuminate\Support\Str::random(40),
            \Illuminate\Support\Arr::random($team->operateGames->map(fn ($game) => $game->name)->toArray()),
            \Illuminate\Support\Str::random(40)
        ];

        $tryCreateTeam = $this->postJson($requestUrl, [
            'slug' =>  $randSlug = 'a' . \Illuminate\Support\Str::random(10),
            'is_public' => true,
            'games' => $gameTypes,
        ])->assertOk();

        $this->assertTrue($tryCreateTeam['ok']);
        $this->assertTrue($tryCreateTeam['isValid']);
        $this->assertEquals([], $tryCreateTeam['messages']['bannerImages']);
        $this->assertEquals($randSlug, $tryCreateTeam['messages']['slug']);
        $this->assertTrue($tryCreateTeam['messages']['isPublic']);

        $teamBroadcastAddresses = $team->broadcastAddress->map(fn (TeamBroadCast $teamBroadcast) => [
            'broadcastAddress' => $teamBroadcast->broadcast_address,
            'platform' => $teamBroadcast->platform,
            'platformKr' => TeamBroadCast::$platforms[$teamBroadcast->platform],
        ])->toArray();

        $this->assertEquals($teamBroadcastAddresses, $tryCreateTeam['messages']['broadCastAddress']);
        collect($gameTypes)->each(fn (string $gameType) => $this->assertTrue(
            in_array($gameType, $tryCreateTeam['messages']['operateGames'])
        ));
    }
}
