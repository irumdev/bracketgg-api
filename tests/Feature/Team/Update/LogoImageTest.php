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
use App\Http\Requests\Team\UpdateLogoImageRequest;

class LogoImageTest extends TestCase
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
    public function successUpdateLogoImage(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('team.updateLogo', [
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
    public function failUpdateLogoImageIsNotAttached(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('team.updateLogo', [
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
        $requestUrl = route('team.updateLogo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamLogo = $this->postJson($requestUrl, [
            'logo_image' => 'sfd',
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamLogo['ok']);
        $this->assertFalse($tryUpdateTeamLogo['isValid']);

        $this->assertEquals(['code' => UpdateLogoImageRequest::LOGO_IS_NOT_FILE], $tryUpdateTeamLogo['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failUpdateLogoImageIsLarge(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug','addBannerImage' ,'addOperateGame', 'addBroadcasts'])->create(['owner' => $activeUser->id, 'is_public' => false]);
        $requestUrl = route('team.updateLogo', [
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
        $requestUrl = route('team.updateLogo', [
            'teamSlug' => $team->slug,
        ]);

        $tryUpdateTeamLogo = $this->postJson($requestUrl, [
            'logo_image' => UploadedFile::fake()->create('test.jpg', 2048, 'application/octet-stream'),
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeamLogo['ok']);
        $this->assertFalse($tryUpdateTeamLogo['isValid']);

        $this->assertEquals(['code' => UpdateLogoImageRequest::LOGO_MIME_IS_NOT_MATCH], $tryUpdateTeamLogo['messages']);
    }
}
