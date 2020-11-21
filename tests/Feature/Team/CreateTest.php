<?php

declare(strict_types=1);

namespace Tests\Feature\Team;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\Team\Team;
use App\Models\User;

class CreateTest extends TestCase
{
    /** @test */
    public function failCreatTeamWhenTeamNameIsNotUnique(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->create([
            'owner' => $user->id,
        ]);

        $team->owner = $user->id;
        $team->save();
        $tryCreateTeam = $this->postJson(route('createTeam'), [
            'name' => $team->name
        ])->assertStatus(422);

        $this->assertFalse($tryCreateTeam['ok']);
        $this->assertFalse($tryCreateTeam['isValid']);

        $this->assertEquals(['code' => 5], $tryCreateTeam['messages']);
    }


    /** @test */
    public function failCreatTeamWhenUserIsNotLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $teamName = \Illuminate\Support\Str::random(8);
        $tryCreateTeam = $this->postJson(route('createTeam'), [
            'name' => $teamName
        ])->assertUnauthorized();

        $this->assertFalse($tryCreateTeam['ok']);
        $this->assertFalse($tryCreateTeam['isValid']);

        $this->assertEquals(['code' => 401], $tryCreateTeam['messages']);
    }

    /** @test */
    public function failCreatTeamWhenTeamNameIsLong(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $teamName = \Illuminate\Support\Str::random(21);

        $tryCreateTeam = $this->postJson(route('createTeam'), [
            'name' => $teamName
        ])->assertStatus(422);


        $this->assertFalse($tryCreateTeam['ok']);
        $this->assertFalse($tryCreateTeam['isValid']);
        $this->assertEquals(['code' => 4], $tryCreateTeam['messages']);
    }

    /** @test */
    public function failCreatTeamWhenTeamNameIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $tryCreateTeam = $this->postJson(route('createTeam'), [

        ])->assertStatus(422);
        $this->assertFalse($tryCreateTeam['ok']);
        $this->assertFalse($tryCreateTeam['isValid']);
        $this->assertEquals(['code' => 1], $tryCreateTeam['messages']);
    }

    /** @test */
    public function failCreatTeamWhenUserHasManyTeams(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        collect(range(0 ,2))->map(function() use ($user) {
            $team = factory(Team::class)->create();

            $team->owner = $user->id;
            $team->save();
        });
        $teamName = \Illuminate\Support\Str::random(15);

        $tryCreateTeam = $this->postJson(route('createTeam'), [
            'name' => $teamName
        ])->assertStatus(401);

        $this->assertFalse($tryCreateTeam['ok']);
        $this->assertFalse($tryCreateTeam['isValid']);
        $this->assertEquals(['code' => 1], $tryCreateTeam['messages']);
    }

    /** @test */
    public function successCreateTeam(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $teamName = \Illuminate\Support\Str::random(15);
        $user = Sanctum::actingAs(factory(User::class)->create());

        $tryCreateTeam = $this->postJson(route('createTeam'), [
            'name' => $teamName
        ])->assertOk();

        $team = Team::where('owner', $user->id)->first();

        $message = $tryCreateTeam['messages'];

        $this->assertTrue($tryCreateTeam['ok']);
        $this->assertTrue($tryCreateTeam['isValid']);
        $this->assertNotNull($team);
        $this->assertEquals([], $message['bannerImages']);
        $this->assertEquals([], $message['broadCastAddress']);
        $this->assertEquals($team->id, $message['id']);
        $this->assertEquals($teamName, $team->name);
        $this->assertEquals($teamName, $message['channelName']);
        $this->assertNull($team->logo_image);
        $this->assertNull($message['logoImage']);
        $this->assertEquals($user->id, $message['owner']);
        $this->assertIsString($message['slug']);
    }
}
