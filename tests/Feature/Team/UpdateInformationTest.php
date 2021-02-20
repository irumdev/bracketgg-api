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
use App\Http\Requests\Team\UpdateBannerImageRequest;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Team\UpdateLogoImageRequest;
use Styde\Enlighten\Tests\EnlightenSetup;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Requests\Rules\Broadcast as BroadcastRules;

class UpdateInformationTest extends TestCase
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
    public function successUpdateAllItem(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug', 'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $gameTypes = [
            Str::random(40),
            Arr::random($team->operateGames->map(fn ($game) => $game->name)->toArray()),
            Str::random(40)
        ];

        $tryUpdateTeam = $this->postJson($requestUrl, [
            'slug' =>  $randSlug = 'a' . Str::random(10),
            'is_public' => true,
            'games' => $gameTypes,
        ])->assertOk();

        $this->assertTrue($tryUpdateTeam['ok']);
        $this->assertTrue($tryUpdateTeam['isValid']);
        $this->assertEquals([], $tryUpdateTeam['messages']['bannerImages']);
        $this->assertEquals($randSlug, $tryUpdateTeam['messages']['slug']);
        $this->assertTrue($tryUpdateTeam['messages']['isPublic']);

        $teamBroadcastAddresses = $team->broadcastAddress->map(fn (TeamBroadCast $teamBroadcast) => [
            'broadcastAddress' => $teamBroadcast->broadcast_address,
            'platform' => $teamBroadcast->platform,
            'platformKr' => TeamBroadCast::$platforms[$teamBroadcast->platform],
            'broadcastId' => $teamBroadcast->id,
        ])->toArray();

        $this->assertEquals($teamBroadcastAddresses, $tryUpdateTeam['messages']['broadCastAddress']);
        collect($gameTypes)->each(fn (string $gameType) => $this->assertTrue(
            in_array($gameType, $tryUpdateTeam['messages']['operateGames'])
        ));
    }

    /** @test @deprecate */
    // public function failUpdateLogoImageIsNotImage(): void
    // {
    //     $this->setName($this->getCurrentCaseKoreanName());
    //     $activeUser = Sanctum::actingAs(factory(User::class)->create());

    //     $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
    //     $requestUrl = route('updateTeamLogo', [
    //         'teamSlug' => $team->slug,
    //     ]);

    //     $tryUpdateTeamLogo = $this->postJson($requestUrl, [
    //         'logo_image' => UploadedFile::fake()->create('test.asdf', 2048),
    //     ])->assertStatus(422);

    //     $this->assertFalse($tryUpdateTeamLogo['ok']);
    //     $this->assertFalse($tryUpdateTeamLogo['isValid']);

    //     $this->assertEquals(['code' => UpdateLogoImageRequest::LOGO_IS_NOT_IMAGE], $tryUpdateTeamLogo['messages']);
    // }
}
