<?php

namespace Tests\Feature\Team\Invite;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Styde\Enlighten\Tests\EnlightenSetup;
use Laravel\Sanctum\Sanctum;
use App\Models\Team\Team;
use App\Models\Team\Member as TeamMember;
use App\Models\Team\InvitationCard;
use App\Models\User;
use Tests\TestCase;
use App\Http\Requests\Team\Invite\InviteRequest;
use Symfony\Component\HttpFoundation\Response;

class InviteMemberTest extends TestCase
{
    use EnlightenSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
    }


    #### success case ####

    /**
     * @test
     * @enlighten
     */
    public function successRejectTeamOper(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $teamOwner = factory(User::class)->create();
        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $teamOwner->id,
            'member_count' => 1
        ]);

        $targetUser = factory(User::class)->create();

        Sanctum::actingAs($teamOwner);
        $trySendInviteCard = $this->postJson(route('team.inviteMember.for.owner', [
            'userIdx' => $targetUser->id,
            'teamSlug' => $team->slug,
        ]))->assertOk();


        Sanctum::actingAs($targetUser);

        $tryRejectOper = $this->postJson(route('user.rejectTeamInvite', [
            'teamSlug' => $team->slug
        ]))->assertOk();

        $this->assertTrue($tryRejectOper['ok']);
        $this->assertTrue($tryRejectOper['isValid']);
        $this->assertTrue($tryRejectOper['messages']['markTeamOperRejected']);
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

        $trySendInviteCard = $this->postJson(route('team.inviteMember.for.owner', [
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
                ['invitation_card_creator', '=', $team->owner],
            ])->exists()
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successAcceptInvite(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());

        $teamOwner = factory(User::class)->create();

        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $teamOwner->id,
            'member_count' => 1
        ]);

        $inviteCard = InvitationCard::create([
            'user_id' => $requestUser->id,
            'team_id' => $team->id,
            'from_type' => InvitationCard::FROM_TEAM_OWNER,
            'invitation_card_creator' => $team->owner,
        ]);

        $team = Team::find($team->id);

        $this->assertEquals(1, $team->member_count);
        $tryAcceptInviteCard = $this->postJson(route('user.acceptTeamInvite', [
            'teamSlug' => $team->slug
        ]))->assertOk();

        $team = Team::find($team->id);
        $trashedCard = InvitationCard::onlyTrashed()->where('id', $inviteCard->id)->first();
        $this->assertTrue($tryAcceptInviteCard['ok']);
        $this->assertTrue($tryAcceptInviteCard['isValid']);
        $this->assertTrue($tryAcceptInviteCard['messages']['markTeamInvited']);
        $this->assertNull(InvitationCard::find($inviteCard->id));
        $this->assertTrue(TeamMember::where([
            ['team_id', '=', $team->id],
            ['user_id', '=', $requestUser->id],
        ])->exists());
        $this->assertEquals(2, $team->member_count);
        $this->assertEquals(InvitationCard::ACCEPT, $trashedCard->status);
    }

    ### success case ####

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

        $trySendInviteCard = $this->postJson(route('team.inviteMember.for.owner', [
            'userIdx' => $receiveUser->id,
            'teamSlug' => $team->slug
        ]))->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($trySendInviteCard['ok']);
        $this->assertFalse($trySendInviteCard['isValid']);
        $this->assertEquals(['code' => InviteRequest::RECEIVER_ALREADY_TEAM_MEMBER], $trySendInviteCard['messages']);
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

        $trySendInviteCard = $this->postJson(route('team.inviteMember.for.owner', [
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

        $trySendInviteCard = $this->postJson(route('team.inviteMember.for.owner', [
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

        $trySendInviteCard = $this->postJson(route('team.inviteMember.for.owner', [
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

        $trySendInviteCard = $this->postJson(route('team.inviteMember.for.owner', [
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
    public function failInviteWhenAlreadySendInviteCardToTargetUser(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $activeUser->id
        ]);

        $targetUser = factory(User::class)->create();
        $this->setName($this->getCurrentCaseKoreanName());

        $trySendInviteCard = $this->postJson(route('team.inviteMember.for.owner', [
            'userIdx' => $targetUser->id,
            'teamSlug' => $team->slug,
        ]))->assertOk();

        $this->assertTrue($trySendInviteCard['ok']);
        $this->assertTrue($trySendInviteCard['isValid']);
        $this->assertEquals(['sendInviteCard' => true], $trySendInviteCard['messages']);

        $trySendInviteCard = $this->postJson(route('team.inviteMember.for.owner', [
            'userIdx' => $targetUser->id,
            'teamSlug' => $team->slug,
        ]))->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertEquals(['code' => InviteRequest::ALREADY_SEND_INVITE_CARD], $trySendInviteCard['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failAcceptInviteWhenUserIsNotLogin(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $inActiveUser = factory(User::class)->create();
        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $inActiveUser->id,
            'member_count' => 1
        ]);

        $tryAcceptInviteCard = $this->postJson(route('user.acceptTeamInvite', [
            'teamSlug' => $team->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryAcceptInviteCard['ok']);
        $this->assertFalse($tryAcceptInviteCard['isValid']);
        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $tryAcceptInviteCard['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failAcceptInviteWhenUserHasNotInviteCard(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $activeUser->id,
            'member_count' => 1
        ]);

        $tryAcceptInviteCard = $this->postJson(route('user.acceptTeamInvite', [
            'teamSlug' => $team->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryAcceptInviteCard['ok']);
        $this->assertFalse($tryAcceptInviteCard['isValid']);
        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $tryAcceptInviteCard['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failRejectTeamOperWhenUserNotHaveInviteCard(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $teamOwner = factory(User::class)->create();
        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $teamOwner->id,
            'member_count' => 1
        ]);

        $targetUser = factory(User::class)->create();

        Sanctum::actingAs($targetUser);

        $tryRejectOper = $this->postJson(route('user.rejectTeamInvite', [
            'teamSlug' => $team->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryRejectOper['ok']);
        $this->assertFalse($tryRejectOper['isValid']);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $tryRejectOper['messages']['code']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failRejectTeamOperWhenUserIsAlreadyTeamMember(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $teamOwner = factory(User::class)->create();
        $team = factory(Team::class)->states(['addSlug'])->create([
            'owner' => $teamOwner->id,
            'member_count' => 1
        ]);

        $targetUser = factory(User::class)->create();

        Sanctum::actingAs($teamOwner);
        $trySendInviteCard = $this->postJson(route('team.inviteMember.for.owner', [
            'userIdx' => $targetUser->id,
            'teamSlug' => $team->slug,
        ]))->assertOk();

        TeamMember::create([
            'team_id' => $team->id, 'user_id' => $targetUser->id,
        ]);

        Sanctum::actingAs($targetUser);

        $tryRejectOper = $this->postJson(route('user.rejectTeamInvite', [
            'teamSlug' => $team->slug
        ]))->assertUnauthorized();

        $this->assertFalse($tryRejectOper['ok']);
        $this->assertFalse($tryRejectOper['isValid']);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $tryRejectOper['messages']['code']);
    }
}
