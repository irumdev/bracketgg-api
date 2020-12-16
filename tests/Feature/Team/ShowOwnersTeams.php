<?php

namespace Tests\Feature\Team;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Laravel\Sanctum\Sanctum;
use App\Models\User;

class ShowOwnersTeams extends TestCase
{
    /** @test */
    public function successLookupTeamInfoWhenLogin(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());



        // $tryLookupTeamInfo = $this->getJson(route('showTeamByOwnerId', [
        //     'user' =>
        // ]))->assertOk();
    }

    /** @test */
    public function failLookupTeamInfoWhenNotLogin(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /** @test */
    public function failLookupTeamInfoWhenOwnerHasNoTeam(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
