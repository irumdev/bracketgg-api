<?php

declare(strict_types=1);

namespace Tests\Feature\Team;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Team\Team;
use Styde\Enlighten\Tests\EnlightenSetup;

class DuplicateNameCheckTest extends TestCase
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
    public function getTrueWhenTeamNameIsExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->create();
        $tryCheckTeamNameIsDuplicate = $this->getJson(route('team.name.isDuplicate', [
            'teamName' => $team->name
        ]))->assertStatus(422);

        $this->assertFalse($tryCheckTeamNameIsDuplicate['ok']);
        $this->assertFalse($tryCheckTeamNameIsDuplicate['isValid']);
        $this->assertTrue($tryCheckTeamNameIsDuplicate['messages']['isDuplicate']);
    }

    /**
     * @test
     * @enlighten
     */
    public function getFalseWhenTeamNameIsNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $tryCheckTeamNameIsDuplicate = $this->getJson(route('team.name.isDuplicate', [
            'teamName' => \Illuminate\Support\Str::random(10),
        ]))->assertOk();

        $this->assertTrue($tryCheckTeamNameIsDuplicate['ok']);
        $this->assertTrue($tryCheckTeamNameIsDuplicate['isValid']);
        $this->assertFalse($tryCheckTeamNameIsDuplicate['messages']['isDuplicate']);
    }
}
