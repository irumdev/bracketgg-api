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
use Illuminate\Support\Str;
use App\Models\User;

class NameTest extends TestCase
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
    public function successUpdateTeamName(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create();
        $team->owner = $activeUser->id;
        $team->save();

        $team = Team::find($team->id);
        $beforeTeamName = $team->name;

        $requestUrl = route('team.updateInfo', [
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
    public function failUpdateTeamNameWhenNameIsNotUnique(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create();
        $team->owner = $activeUser->id;
        $team->save();

        $team = Team::find($team->id);
        $requestUrl = route('team.updateInfo', [
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
        $requestUrl = route('team.updateInfo', [
            'teamSlug' => $team->slug
        ]);
        $tryUpdateTeam = $this->postJson($requestUrl, [
            'name' => UploadedFile::fake()->create('test.png', 2048),
        ])->assertStatus(422);

        $this->assertFalse($tryUpdateTeam['ok']);
        $this->assertFalse($tryUpdateTeam['isValid']);
        $this->assertEquals(UpdateInfoWithOutBannerRequest::TEAM_NAME_IS_NOT_STRING, $tryUpdateTeam['messages']['code']);
    }
}
