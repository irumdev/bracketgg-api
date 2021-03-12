<?php

declare(strict_types=1);

namespace Tests\Feature\Team\Update;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\Team\Team;
use App\Http\Requests\Team\UpdateInfoWithOutBannerRequest;
use Illuminate\Http\UploadedFile;
use Styde\Enlighten\Tests\EnlightenSetup;
use App\Models\User;

class SlugTest extends TestCase
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
    public function failUpdateTeamSlugWhenSlugIsNotUnique(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create();
        $team->owner = $activeUser->id;
        $team->save();

        $requestUrl = route('team.updateInfo', [
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

        $requestUrl = route('team.updateInfo', [
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

        $requestUrl = route('team.updateInfo', [
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

        $requestUrl = route('team.updateInfo', [
            'teamSlug' => $team->slug
        ]);
        collect([
            \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(13)),
            \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(13)) . '*'
        ])->each(function (string $illeagleSlug) use ($requestUrl) {
            $tryUpdateTeam = $this->postJson($requestUrl, [
                'slug' => $illeagleSlug
            ])->assertStatus(422);
            $this->assertFalse($tryUpdateTeam['ok']);
            $this->assertFalse($tryUpdateTeam['isValid']);
            $this->assertEquals(UpdateInfoWithOutBannerRequest::SLUG_PATTERN_IS_NOT_MATCH, $tryUpdateTeam['messages']['code']);
        });
    }
}
