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
    public function failUpdateTeamNameWhenNameIsNotUnique(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create();
        $team->owner = $activeUser->id;
        $team->save();

        $team = Team::find($team->id);
        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug
        ]);
        $tryUpdateTeam = $this->postJson($requestUrl, [
            'name' => $team->name
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeam['ok']);
        $this->assertFalse($tryUpdateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::TEAM_NAME_IS_NOT_UNIQUE, $tryUpdateTeam['messages']['code']);
    }


    /**
     * @test
     * @enlighten
     */
    public function failUpdateTeamNameWhenNameIsNotString(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create();
        $team->owner = $activeUser->id;
        $team->save();

        $team = Team::find($team->id);
        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug
        ]);
        $tryUpdateTeam = $this->postJson($requestUrl, [
            'name' => UploadedFile::fake()->create('test.png', 2048),
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeam['ok']);
        $this->assertFalse($tryUpdateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::TEAM_NAME_IS_NOT_STRING, $tryUpdateTeam['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successUpdateTeamName(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create();
        $team->owner = $activeUser->id;
        $team->save();

        $team = Team::find($team->id);
        $beforeTeamName = $team->name;

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug
        ]);
        $tryUpdateTeam = $this->postJson($requestUrl, [
            'name' => $changedTeamName = Str::random(10),
        ])->assertOk();

        $this->assertTrue($tryUpdateTeam['ok']);
        $this->assertTrue($tryUpdateTeam['isValid']);
        $this->assertEquals($changedTeamName, Team::find($team->id)->name);
    }


    /**
     * @test
     * @enlighten
     */
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
        $tryUpdateTeam = $this->postJson($requestUrl, [
            'slug' => $team->slug
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeam['ok']);
        $this->assertFalse($tryUpdateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::SLUG_IS_NOT_UNIQUE, $tryUpdateTeam['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateTeamSlugWhenSlugIsLong(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create(['owner' => $activeUser->id]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug
        ]);
        $tryUpdateTeam = $this->postJson($requestUrl, [
            'slug' => \Illuminate\Support\Str::random(17)
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeam['ok']);
        $this->assertFalse($tryUpdateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::SLUG_IS_LONG, $tryUpdateTeam['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateTeamSlugWhenSlugIsShort(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create(['owner' => $activeUser->id]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug
        ]);
        $tryUpdateTeam = $this->postJson($requestUrl, [
            'slug' => \Illuminate\Support\Str::random(3)
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeam['ok']);
        $this->assertFalse($tryUpdateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::SLUG_IS_SHORT, $tryUpdateTeam['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
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
            $tryUpdateTeam = $this->postJson($requestUrl, [
                'slug' => $illeagleSlug
            ])->assertStatus(422);
            $this->assertFalse($tryUpdateTeam['ok']);
            $this->assertFalse($tryUpdateTeam['isValid']);
            $this->assertEquals(UpdateInfoWithOutBannerRequest::SLUG_PATTERN_IS_NOT_MATCH, $tryUpdateTeam['messages']['code']);
        });
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateTeamSlugWhenPublicStatusIsNotBoolean(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create(['owner' => $activeUser->id]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeam = $this->postJson($requestUrl, [
            'is_public' => 'asdf',
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeam['ok']);
        $this->assertFalse($tryUpdateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::PUBLIC_STATUS_IS_NOT_BOOLEAN, $tryUpdateTeam['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateTeamSlugWhenGameCategoryIsNotArray(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create(['owner' => $activeUser->id]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeam = $this->postJson($requestUrl, [
            'games' => 'asdf',
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeam['ok']);
        $this->assertFalse($tryUpdateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::GAME_CATEGORY_IS_NOT_ARRAY, $tryUpdateTeam['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateTeamSlugWhenGameCategoryItemIsLong(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create(['owner' => $activeUser->id]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeam = $this->postJson($requestUrl, [
            'games' => [\Illuminate\Support\Str::random(256), \Illuminate\Support\Str::random(256)],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeam['ok']);
        $this->assertFalse($tryUpdateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::GAME_CATEGORY_ITEM_IS_LONG, $tryUpdateTeam['messages']['code']);
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
            \Illuminate\Support\Str::random(40),
            \Illuminate\Support\Arr::random($team->operateGames->map(fn ($game) => $game->name)->toArray()),
            \Illuminate\Support\Str::random(40)
        ];

        $tryUpdateTeam = $this->postJson($requestUrl, [
            'slug' =>  $randSlug = 'a' . \Illuminate\Support\Str::random(10),
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



    /**
     * @test
     * @enlighten
     */
    public function failUpdateBannerImageWhenTryAnotherTeam(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $anotehrUser = factory(User::class)->create();
        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $anotherTeam = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $anotherTeam->owner = $anotehrUser->id;
        $anotherTeam->save();

        $anotherTeam = Team::find($anotherTeam->id);

        $requestUrl = route('updateTeamBanner', [
            'teamSlug' => $anotherTeam->slug,
        ]);

        $tryUpdateTeamBanner = $this->postJson($requestUrl, [
            'banner_image' => UploadedFile::fake()->create('test.png', 2048),
            // 'banner_image_id' => 'abcd'
        ])->assertStatus(401);

        $this->assertFalse($tryUpdateTeamBanner['ok']);
        $this->assertFalse($tryUpdateTeamBanner['isValid']);

        $this->assertEquals(['code' => 401], $tryUpdateTeamBanner['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBannerWhenBannerIsNotFile(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug', 'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamBanner', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBanner = $this->postJson($requestUrl, [
            'banner_image' => 'asdf',
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamBanner['ok']);
        $this->assertFalse($tryUpdateTeamBanner['isValid']);

        $this->assertEquals(['code' => UpdateBannerImageRequest::BANNER_UPLOAD_IS_NOT_FULL_UPLOADED_FILE], $tryUpdateTeamBanner['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBannerWhenBannerIsNotImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug', 'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamBanner', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBanner = $this->postJson($requestUrl, [
            'banner_image' => UploadedFile::fake()->create('test.asdf', 250),
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamBanner['ok']);
        $this->assertFalse($tryUpdateTeamBanner['isValid']);

        $this->assertEquals(['code' => UpdateBannerImageRequest::BANNER_UPLOAD_FILE_IS_NOT_IMAGE], $tryUpdateTeamBanner['messages']);
    }


    /**
     * @test
     * @enlighten
     */
    public function failCreateBannerWhenBannerIsLarge(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug', 'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamBanner', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBanner = $this->postJson($requestUrl, [
            'banner_image' => UploadedFile::fake()->create('test.png', 2049),
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamBanner['ok']);
        $this->assertFalse($tryUpdateTeamBanner['isValid']);

        $this->assertEquals(['code' => UpdateBannerImageRequest::BANNER_UPLOAD_FILE_IS_LARGE], $tryUpdateTeamBanner['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBannerWhenAlreadyHasBanner(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug', 'addOperateGame', 'addBannerImage' ,'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamBanner', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBanner = $this->postJson($requestUrl, [
            'banner_image' => UploadedFile::fake()->create('test.png', 2048),
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamBanner['ok']);
        $this->assertFalse($tryUpdateTeamBanner['isValid']);

        $this->assertEquals(['code' => UpdateBannerImageRequest::BANNER_UPLOAD_FILE_HAS_MANY_BANNER], $tryUpdateTeamBanner['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successCreateBannerImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug', 'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamBanner', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBanner = $this->postJson($requestUrl, [
            'banner_image' => UploadedFile::fake()->create('test.png', 2048),
        ])->assertOk();

        $this->assertTrue($tryUpdateTeamBanner['ok']);
        $this->assertTrue($tryUpdateTeamBanner['isValid']);

        $this->assertEquals(['isSuccess' => true], $tryUpdateTeamBanner['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateBannerImageWhenBannerIdIsInvalid(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamBanner', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBanner = $this->postJson($requestUrl, [
            'banner_image' => UploadedFile::fake()->create('test.png', 2048),
            'banner_image_id' => -24
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamBanner['ok']);
        $this->assertFalse($tryUpdateTeamBanner['isValid']);

        $this->assertEquals(['code' => UpdateBannerImageRequest::BANNER_IMAGE_ID_IS_NOT_EXISTS], $tryUpdateTeamBanner['messages']);
    }


    /**
     * @test
     * @enlighten
     */
    public function failUpdateBannerImageWhenBannerIdIsNotNumeric(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamBanner', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBanner = $this->postJson($requestUrl, [
            'banner_image' => UploadedFile::fake()->create('test.png', 2048),
            'banner_image_id' => 'abcd'
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamBanner['ok']);
        $this->assertFalse($tryUpdateTeamBanner['isValid']);

        $this->assertEquals(['code' => UpdateBannerImageRequest::BANNER_IMAGE_ID_IS_NOT_NUMERIC], $tryUpdateTeamBanner['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateBannerImageIsNotAttached(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamBanner', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBanner = $this->postJson($requestUrl, [
            // 'banner_image' => UploadedFile::fake()->create('test.png', 2048),
            // 'banner_image_id' => 'abcd'
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamBanner['ok']);
        $this->assertFalse($tryUpdateTeamBanner['isValid']);

        $this->assertEquals(['code' => UpdateBannerImageRequest::BANNER_UPLOAD_FILE_IS_NOT_ATTACHED], $tryUpdateTeamBanner['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successUpdateBannerImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamBanner', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBanner = $this->postJson($requestUrl, [
            'banner_image' => UploadedFile::fake()->create('test.png', 2048),
            'banner_image_id' => $team->bannerImages->first()->id
        ])->assertOk();

        $this->assertTrue($tryUpdateTeamBanner['ok']);
        $this->assertTrue($tryUpdateTeamBanner['isValid']);

        $this->assertEquals(['isSuccess' => true], $tryUpdateTeamBanner['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateLogoImageIsNotAttached(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamLogo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamLogo = $this->postJson($requestUrl, [

        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamLogo['ok']);
        $this->assertFalse($tryUpdateTeamLogo['isValid']);

        $this->assertEquals(['code' => UpdateLogoImageRequest::LOGO_IS_NOT_ATTACHED], $tryUpdateTeamLogo['messages']);
    }


    /**
     * @test
     * @enlighten
     */
    public function failUpdateLogoImageIsNotFile(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamLogo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamLogo = $this->postJson($requestUrl, [
            'logo_image' => 'sfd',
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamLogo['ok']);
        $this->assertFalse($tryUpdateTeamLogo['isValid']);

        $this->assertEquals(['code' => UpdateLogoImageRequest::LOGO_IS_NOT_FILE], $tryUpdateTeamLogo['messages']);
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

    /**
     * @test
     * @enlighten
     */
    public function failUpdateLogoImageIsLarge(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamLogo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamLogo = $this->postJson($requestUrl, [
            'logo_image' => UploadedFile::fake()->create('test.png', 2049),
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamLogo['ok']);
        $this->assertFalse($tryUpdateTeamLogo['isValid']);

        $this->assertEquals(['code' => UpdateLogoImageRequest::LOGO_IS_IMAGE_IS_LARGE], $tryUpdateTeamLogo['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateWhenLogoImageMimeIsWrong(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamLogo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamLogo = $this->postJson($requestUrl, [
            'logo_image' => UploadedFile::fake()->create('test.jpg', 2048, 'application/octet-stream'),
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamLogo['ok']);
        $this->assertFalse($tryUpdateTeamLogo['isValid']);

        $this->assertEquals(['code' => UpdateLogoImageRequest::LOGO_MIME_IS_NOT_MATCH], $tryUpdateTeamLogo['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successUpdateLogoImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('updateTeamLogo', [
            'teamSlug' => $team->slug,
        ]);

        $before = $team->logo_image;

        $tryUpdateTeamLogo = $this->postJson($requestUrl, [
            'logo_image' => UploadedFile::fake()->create('test.png', 2048),
        ])->assertOk();

        $this->assertTrue($tryUpdateTeamLogo['ok']);
        $this->assertTrue($tryUpdateTeamLogo['isValid']);
        $this->assertEquals(['isSuccess' => true], $tryUpdateTeamLogo['messages']);
        $this->assertFalse($before === Team::find($team->id)->logo_image);
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenUrlIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);


        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'platform' => Arr::random(array_keys(TeamBroadCast::$platforms)),

                ]
            ],
        ])->assertStatus(422);


        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(['code' => BroadcastRules::BROADCAST_ADDRESS_HAS_NOT_PLATFORM], $tryUpdateTeamBroadCast['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenBroadCastIsNotArray(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);


        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => 'asdf',
        ])->assertStatus(422);


        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(['code' => BroadcastRules::BROADCAST_IS_NOT_ARRAY], $tryUpdateTeamBroadCast['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenPlatformIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);


        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => 'https://' . Str::random(20) . '.com',

                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_ADDRESS_HAS_NOT_URL],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenUrlIsNotString(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);


        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => false,
                    'platform' => Arr::random(array_keys(TeamBroadCast::$platforms)),
                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_URL_IS_NOT_STRING],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenUrlIsNotUnique(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => $team->broadcastAddress->first()->broadcast_address,
                    'platform' => Arr::random(array_keys(TeamBroadCast::$platforms)),
                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_URL_IS_NOT_UNIQUE],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenPlatformIsNotNumeric(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => 'https://' . Str::random(20) . '.com',
                    'platform' => 'asdf',
                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_PLATFORM_IS_INVALID],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failCreateBroadcastWhenPlatformIsInvalid(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => 'https://' . Str::random(20) . '.com',
                    'platform' => -3,
                ]
            ],
        ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_PLATFORM_IS_INVALID],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successCreateBroadcast(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => [
                [
                    'url' => $randUrl = 'https://' . Str::random(20) . '.com',
                    'platform' => $randPlatform = Arr::random(array_keys(TeamBroadCast::$platforms)),
                ]
            ],
        ])->assertOk();
        $this->assertTrue($tryUpdateTeamBroadCast['ok']);
        $this->assertTrue($tryUpdateTeamBroadCast['isValid']);

        $teamBroadCast = Team::find($team->id)->broadcastAddress;

        $teamBroadcastUrls = $teamBroadCast->map(fn (TeamBroadCast $broadcast) => $broadcast->broadcast_address);
        $teamBroadcastPlatforms = $teamBroadCast->map(fn (TeamBroadCast $broadcast) => $broadcast->platform);

        $this->assertTrue($teamBroadcastUrls->contains($randUrl));
        $this->assertTrue($teamBroadcastPlatforms->contains($randPlatform));
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateBroadcastWhenPlatformIdIsNotNumeric(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
           'teamSlug' => $team->slug,
       ]);

        $randKey = Arr::random(array_keys($team->broadcastAddress->keys()->toArray()));

        $randTeamBroadCast = $team->broadcastAddress->get($randKey);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
           'broadcasts' => [
               [
                   'url' => 'https://' . Str::random(20) . '.com',
                   'platform' => $randPlatform = Arr::random(array_keys(TeamBroadCast::$platforms)),
                   'id' => 'asdf',
               ]
           ],
       ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_ID_IS_NOT_NUMERIC],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateBroadcastWhenTryUpdateAnotherTeamPlatform(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $anotherTeam = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => factory(User::class)->create()->id, 'is_public' => false]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
           'teamSlug' => $team->slug,
        ]);

        $targetTeamRandKey = Arr::random(array_keys($team->broadcastAddress->keys()->toArray()));
        $anotherTeamRandKey = Arr::random(array_keys($anotherTeam->broadcastAddress->keys()->toArray()));

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
           'broadcasts' => [
               [
                   'url' => 'https://' . Str::random(20) . '.com',
                   'platform' => $randPlatform = Arr::random(array_keys(TeamBroadCast::$platforms)),
                   'id' => $anotherTeam->broadcastAddress->get($anotherTeamRandKey)->id,
               ]
           ],
       ])->assertStatus(422);
        $this->assertFalse($tryUpdateTeamBroadCast['ok']);
        $this->assertFalse($tryUpdateTeamBroadCast['isValid']);
        $this->assertEquals(
            ['code' => BroadcastRules::BROADCAST_ID_IS_NOT_BELONGS_TO_MY_TEAM],
            $tryUpdateTeamBroadCast['messages']
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successUpdateBroadcast(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
           'teamSlug' => $team->slug,
        ]);

        $targetTeamRandKey = Arr::random(array_keys($team->broadcastAddress->keys()->toArray()));

        $targetRandTeamBroadCast = $team->broadcastAddress->get($targetTeamRandKey);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
           'broadcasts' => [
               [
                   'url' => $randUrl = 'https://' . Str::random(20) . '.com',
                   'platform' => $randPlatform = Arr::random(array_keys(TeamBroadCast::$platforms)),
                   'id' => $targetRandTeamBroadCast->id,
               ]
           ],
       ])->assertOk();

        $this->assertTrue($tryUpdateTeamBroadCast['ok']);
        $this->assertTrue($tryUpdateTeamBroadCast['isValid']);

        $changedTeamBroadcastAddress = Team::find($team->id)->broadcastAddress;

        $teamBroadcastUrls = $changedTeamBroadcastAddress->map(fn (TeamBroadCast $broadcast) => $broadcast->broadcast_address);
        $teamBroadcastPlatforms = $changedTeamBroadcastAddress->map(fn (TeamBroadCast $broadcast) => $broadcast->platform);

        $this->assertTrue($teamBroadcastUrls->contains($randUrl));
        $this->assertTrue($teamBroadcastPlatforms->contains($randPlatform));
    }

    /**
     * @test
     * @enlighten
     */
    public function successCreateBroadcastKeepAlreadyExistsBroadcastUrl(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage', 'addTenBroadcasts'])->create(['owner' => $activeUser->id, ]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
            'teamSlug' => $team->slug,
        ]);

        $broadcastInfos = $team->broadcastAddress->toArray();

        $tryUpdateChannelBroadCast = $this->postJson($requestUrl, [
            'broadcasts' => $postData = [
                [
                    'id' => $broadcastInfos[0]['id'],
                    'url' => $broadcastInfos[0]['broadcast_address'],
                    'platform' => $broadcastInfos[0]['platform'],
                ],
                [
                    'id' => $broadcastInfos[1]['id'],
                    'url' => $broadcastInfos[1]['broadcast_address'],
                    'platform' => $broadcastInfos[1]['platform'],
                ],
                // [
                //     'url' => $channel->broadcastAddress[1]['broadcast_address'],
                //     'platform' => Arr::random(array_keys(ChannelBoradcast::$platforms)),
                // ],
                [
                    'url' => 'http://' . Str::random(10) . 'create.first.com',
                    'platform' => Arr::random(array_keys(TeamBroadCast::$platforms)),
                ],
                [
                    'url' => 'https://' . Str::random(10) . 'create.second.com',
                    'platform' => Arr::random(array_keys(TeamBroadCast::$platforms)),
                ]
            ],
        ])->assertOk();
        $dbBoradCast = Team::find($team->id)->broadcastAddress;

        $this->assertTrue($tryUpdateChannelBroadCast['ok']);
        $this->assertTrue($tryUpdateChannelBroadCast['isValid']);
        $this->assertEquals(count($postData), $dbBoradCast->count());

        collect($postData)->each(function ($broadCastInfo) {
            if (isset($broadCast['id'])) {
                $broadCastInstance = TeamBroadCast::find($broadCastInfo['id']);
            } else {
                $broadCastInstance = TeamBroadCast::where([
                    ['broadcast_address', '=', $broadCastInfo['url']],
                    ['platform', '=', $broadCastInfo['platform']],
                ]);

                $this->assertEquals(1, $broadCastInstance->get()->count());

                $broadCastInstance = $broadCastInstance->first();
            }

            $this->assertNotNull($broadCastInstance);
            $this->assertEquals($broadCastInfo['url'], $broadCastInstance->broadcast_address);
            $this->assertEquals($broadCastInfo['platform'], $broadCastInstance->platform);
        });
    }


    /**
     * @test
     * @enlighten
     */
    public function successUpdateAndCreateBroadcast(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $requestUrl = route('updateTeamInfoWithoutImage', [
           'teamSlug' => $team->slug,
        ]);

        $targetTeamRandKey = Arr::random(array_keys($team->broadcastAddress->keys()->toArray()));

        $targetRandTeamBroadCast = $team->broadcastAddress->get($targetTeamRandKey);

        $tryUpdateTeamBroadCast = $this->postJson($requestUrl, [
           'broadcasts' => [
                [
                   'url' => $updateRandUrl = 'https://' . Str::random(20) . '.com',
                   'platform' => $updateRandPlatform = Arr::random(array_keys(TeamBroadCast::$platforms)),
                   'id' => $targetRandTeamBroadCast->id,
                ],
                [
                    'url' => $createRandUrl = 'https://' . Str::random(20) . '.com',
                    'platform' => $createRandPlatform = Arr::random(array_keys(TeamBroadCast::$platforms)),
                ]
           ],
       ])->assertOk();

        $this->assertTrue($tryUpdateTeamBroadCast['ok']);
        $this->assertTrue($tryUpdateTeamBroadCast['isValid']);

        $changedTeamBroadcastAddress = Team::find($team->id)->broadcastAddress;

        $teamBroadcastUrls = $changedTeamBroadcastAddress->map(fn (TeamBroadCast $broadcast) => $broadcast->broadcast_address);
        $teamBroadcastPlatforms = $changedTeamBroadcastAddress->map(fn (TeamBroadCast $broadcast) => $broadcast->platform);

        $this->assertTrue($teamBroadcastUrls->contains($createRandUrl));
        $this->assertTrue($teamBroadcastPlatforms->contains($createRandPlatform));

        $this->assertEquals(
            Team::find($team->id)->broadcastAddress()
                                 ->where('id', '=', $targetRandTeamBroadCast->id)
                                 ->first()
                                 ->broadcast_address,
            $updateRandUrl
        );

        $this->assertEquals(
            Team::find($team->id)->broadcastAddress()
                                 ->where('id', '=', $targetRandTeamBroadCast->id)
                                 ->first()
                                 ->platform,
            $updateRandPlatform
        );
    }
}
