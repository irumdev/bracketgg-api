<?php

namespace Tests\Feature\Team;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Styde\Enlighten\Tests\EnlightenSetup;
use Laravel\Sanctum\Sanctum;
use App\Models\Team\Team;
use App\Models\Team\Member as TeamMember;
use App\Models\Team\InvitationCard;
use App\Models\User;
use Tests\TestCase;
use App\Http\Requests\Team\SendInveitationCardRequest;
use Symfony\Component\HttpFoundation\Response;

class InviteMemberTest extends TestCase
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
    public function failInviteWhenReceiverUserIsAlreadyMember(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $activeUser->id
        ]);
        $receiveUser = factory(User::class)->create();

        \App\Models\Team\Member::create([
            'team_id' => $team->id,
            'user_id' => $receiveUser->id
        ]);

        $this->assertTrue(
            $team->members()->where('user_id', $receiveUser->id)->exists()
        );

        $trySendInviteCard = $this->postJson(route('inviteTeamMember', [
            'userIdx' => $receiveUser->id,
            'teamSlug' => $team->slug
        ]))->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($trySendInviteCard['ok']);
        $this->assertFalse($trySendInviteCard['isValid']);
        $this->assertEquals(['code' => SendInveitationCardRequest::RECEIVER_ALREADY_TEAM_MEMBER], $trySendInviteCard['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failInviteWhenSendToTeamOwner(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $activeUser->id
        ]);

        $targetUser = factory(User::class)->create();

        $this->setName($this->getCurrentCaseKoreanName());

        $trySendInviteCard = $this->postJson(route('inviteTeamMember', [
            'userIdx' => $activeUser->id,
            'teamSlug' => $team->slug
        ]))->assertUnauthorized();

        $this->assertFalse($trySendInviteCard['ok']);
        $this->assertFalse($trySendInviteCard['isValid']);
        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $trySendInviteCard['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failInviteWhenSendTeamSlugIsNotExists(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $activeUser->id
        ]);

        $targetUser = factory(User::class)->create();

        $this->setName($this->getCurrentCaseKoreanName());

        $trySendInviteCard = $this->postJson(route('inviteTeamMember', [
            'userIdx' => $activeUser->id,
            'teamSlug' => '-123'
        ]))->assertNotFound();

        $this->assertFalse($trySendInviteCard['ok']);
        $this->assertFalse($trySendInviteCard['isValid']);
        $this->assertEquals(['code' => Response::HTTP_NOT_FOUND], $trySendInviteCard['messages']);
    }


    /**
     * @test
     * @enlighten
     */
    public function failInviteWhenSendTargetUserIsNotExists(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $activeUser->id
        ]);

        $targetUser = factory(User::class)->create();

        $this->setName($this->getCurrentCaseKoreanName());

        $trySendInviteCard = $this->postJson(route('inviteTeamMember', [
            'userIdx' => '-1',
            'teamSlug' => $team->slug
        ]))->assertNotFound();

        $this->assertFalse($trySendInviteCard['ok']);
        $this->assertFalse($trySendInviteCard['isValid']);
        $this->assertEquals(['code' => Response::HTTP_NOT_FOUND], $trySendInviteCard['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failInviteWhenSendUserIsNotLogin(): void
    {
        $activeUser = factory(User::class)->create();

        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $activeUser->id
        ]);

        $targetUser = factory(User::class)->create();

        $this->setName($this->getCurrentCaseKoreanName());

        $trySendInviteCard = $this->postJson(route('inviteTeamMember', [
            'userIdx' => $activeUser->id,
            'teamSlug' => $team->slug
        ]))->assertUnauthorized();

        $this->assertFalse($trySendInviteCard['ok']);
        $this->assertFalse($trySendInviteCard['isValid']);
        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $trySendInviteCard['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successSendInviteCard(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $activeUser->id
        ]);

        $targetUser = factory(User::class)->create();

        $trySendInviteCard = $this->postJson(route('inviteTeamMember', [
            'userIdx' => $targetUser->id,
            'teamSlug' => $team->slug,
        ]))->assertOk();

        $this->assertTrue($trySendInviteCard['ok']);
        $this->assertTrue($trySendInviteCard['isValid']);
        $this->assertEquals(['sendInviteCard' => true], $trySendInviteCard['messages']);
        $this->assertTrue(
            InvitationCard::where([
                ['team_id', '=', $team->id],
                ['user_id', '=', $targetUser->id],
            ])->exists()
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function failInviteWhenAlreadySendInviteCardToTargetUser(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $activeUser->id
        ]);

        $targetUser = factory(User::class)->create();
        $this->setName($this->getCurrentCaseKoreanName());

        $trySendInviteCard = $this->postJson(route('inviteTeamMember', [
            'userIdx' => $targetUser->id,
            'teamSlug' => $team->slug,
        ]))->assertOk();

        $this->assertTrue($trySendInviteCard['ok']);
        $this->assertTrue($trySendInviteCard['isValid']);
        $this->assertEquals(['sendInviteCard' => true], $trySendInviteCard['messages']);

        $trySendInviteCard = $this->postJson(route('inviteTeamMember', [
            'userIdx' => $targetUser->id,
            'teamSlug' => $team->slug,
        ]))->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertEquals(['code' => SendInveitationCardRequest::ALREADY_SEND_INVITE_CARD], $trySendInviteCard['messages']);
    }
}
