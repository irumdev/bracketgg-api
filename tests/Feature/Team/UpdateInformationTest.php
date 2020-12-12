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



    /** @test */
    public function failUpdateBannerImageWhenTryAnotherTeam(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $anotehrUser = factory(User::class)->create();
        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $anotehrTeam = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);

        $anotehrTeam->owner = $anotehrUser->id;
        $anotehrTeam->save();

        $anotehrTeam = Team::find($anotehrTeam->id);

        $requestUrl = route('updateTeamBanner', [
            'teamSlug' => $anotehrTeam->slug,
        ]);

        $tryUpdateTeamBanner = $this->postJson($requestUrl, [
            'banner_image' => UploadedFile::fake()->create('test.png', 2048),
            // 'banner_image_id' => 'abcd'
        ])->assertStatus(401);

        $this->assertFalse($tryUpdateTeamBanner['ok']);
        $this->assertFalse($tryUpdateTeamBanner['isValid']);

        $this->assertEquals(['code' => 401], $tryUpdateTeamBanner['messages']);
    }

    /** @test */
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

    /** @test */
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


    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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


    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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


    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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
}
