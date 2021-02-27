<?php

declare(strict_types=1);

namespace Tests\Feature\Team\Update;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\Team\Team;
use App\Models\User;
use App\Http\Requests\Team\UpdateInfoWithOutBannerRequest;
use Illuminate\Http\UploadedFile;
use Styde\Enlighten\Tests\EnlightenSetup;
use App\Http\Requests\Team\UpdateBannerImageRequest;

class BannerImageTest extends TestCase
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
        ])->assertUnauthorized();

        $this->assertFalse($tryUpdateTeamBanner['ok']);
        $this->assertFalse($tryUpdateTeamBanner['isValid']);
        $this->assertUnauthorizedMessages($tryUpdateTeamBanner['messages']);
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
}
