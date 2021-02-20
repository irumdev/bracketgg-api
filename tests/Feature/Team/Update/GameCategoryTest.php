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

class GameCategoryTest extends TestCase
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
    public function failUpdateTeamGameCategoryWhenGameCategoryIsNotArray(): void
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
    public function failUpdateTeamGameCategoryWhenGameCategoryItemIsLong(): void
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
}
