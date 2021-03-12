<?php

declare(strict_types=1);

namespace Tests\Feature\Team\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Team\Team;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Styde\Enlighten\Tests\EnlightenSetup;

class KickTest extends TestCase
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
    public function failKickTeamMemberWhenKickTargetIsNotTeamMember(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSignedMembers'
        ])->create([
            'owner' => $owner->id,
        ]);

        $randTeamMember = factory(User::class)->create();

        $requestUrl = route('team.kickMember', [
            'teamSlug' => $team->slug,
            'userIdx' => $randTeamMember->id
        ]);
        $tryKickTeamMember = $this->postJson($requestUrl)->assertUnauthorized();

        $this->assertFalse($tryKickTeamMember['ok']);
        $this->assertFalse($tryKickTeamMember['isValid']);
        $this->assertUnauthorizedMessages($tryKickTeamMember['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failKickTeamMemberWhenRequestUserIsNotTeamMember(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = factory(User::class)->create();
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSignedMembers'
        ])->create([
            'owner' => $owner->id,
        ]);

        $randTeamMember = factory(User::class)->create();

        $requestUrl = route('team.kickMember', [
            'teamSlug' => $team->slug,
            'userIdx' => $randTeamMember->id
        ]);
        $tryKickTeamMember = $this->postJson($requestUrl)->assertUnauthorized();

        $this->assertFalse($tryKickTeamMember['ok']);
        $this->assertFalse($tryKickTeamMember['isValid']);
        $this->assertUnauthorizedMessages($tryKickTeamMember['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failKickTeamMemberWhenKickTargetIsTeamOwner(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSignedMembers'
        ])->create([
            'owner' => $owner->id,
        ]);

        $requestUrl = route('team.kickMember', [
            'teamSlug' => $team->slug,
            'userIdx' => $team->owner,
        ]);

        $tryKickTeamMember = $this->postJson($requestUrl)->assertUnauthorized();

        $this->assertFalse($tryKickTeamMember['ok']);
        $this->assertFalse($tryKickTeamMember['isValid']);
        $this->assertUnauthorizedMessages($tryKickTeamMember['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failKickTeamMemberWhenRequestUserIsNotTeamOwner(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug', 'addSignedMembers'
        ])->create([
            'owner' => $owner->id,
        ]);

        $requestUser = $team->members()->where([
            ['role' , '!=', Team::OWNER]
        ])->get()->random();


        $activeUser = Sanctum::actingAs($requestUser);

        $requestUrl = route('team.kickMember', [
            'teamSlug' => $team->slug,
            'userIdx' => $team->members()->where([
                ['role' , '!=', Team::OWNER],
                ['user_id' , '!=', $requestUser->id],
            ])->get()->random()->id,
        ]);

        $tryKickTeamMember = $this->postJson($requestUrl)->assertUnauthorized();

        $this->assertFalse($tryKickTeamMember['ok']);
        $this->assertFalse($tryKickTeamMember['isValid']);
        $this->assertUnauthorizedMessages($tryKickTeamMember['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successKickTeamMember(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $owner = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states([
            'addSlug', 'addSignedMembers'
        ])->create([
            'owner' => $owner->id,
        ]);

        $randTeamMember = $team->members()->where([
            ['role' , '!=', Team::OWNER]
        ])->get()->random();

        $requestUrl = route('team.kickMember', [
            'teamSlug' => $team->slug,
            'userIdx' => $randTeamMember->id
        ]);
        $tryKickTeamMember = $this->postJson($requestUrl)->assertOk();

        $this->assertTrue($tryKickTeamMember['ok']);
        $this->assertTrue($tryKickTeamMember['isValid']);
        $this->assertTrue($tryKickTeamMember['messages']['makeTeamMemberKicked']);

        $this->assertFalse($team->members()->where([
            ['user_id', '=', $randTeamMember->id]
        ])->exists());
    }
}
