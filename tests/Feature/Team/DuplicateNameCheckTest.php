<?php

declare(strict_types=1);

namespace Tests\Feature\Team;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Team\Team;

class DuplicateNameCheckTest extends TestCase
{
    /** @test */
    public function getTrueWhenTeamNameIsExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->create();
        $tryCheckTeamNameIsDuplicate = $this->getJson(route('checkTeamNameDuplicate', [
            'teamName' => $team->name
        ]))->assertStatus(422);

        $this->assertFalse($tryCheckTeamNameIsDuplicate['ok']);
        $this->assertFalse($tryCheckTeamNameIsDuplicate['isValid']);
        $this->assertTrue($tryCheckTeamNameIsDuplicate['messages']['isDuplicate']);
    }

    /** @test */
    public function getFalseWhenTeamNameIsNotExists(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $tryCheckTeamNameIsDuplicate = $this->getJson(route('checkTeamNameDuplicate', [
            'teamName' => \Illuminate\Support\Str::random(10),
        ]))->assertOk();

        $this->assertTrue($tryCheckTeamNameIsDuplicate['ok']);
        $this->assertTrue($tryCheckTeamNameIsDuplicate['isValid']);
        $this->assertFalse($tryCheckTeamNameIsDuplicate['messages']['isDuplicate']);
    }
}
