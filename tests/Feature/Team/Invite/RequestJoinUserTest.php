<?php

declare(strict_types=1);

namespace Tests\Feature\Team\Invite;

use App\Http\Requests\Team\Invite\NormalUserJoinRequest;
use App\Models\Team\InvitationCard;
use App\Models\Team\Member;
use App\Models\Team\Team;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Styde\Enlighten\Tests\EnlightenSetup;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RequestJoinUserTest extends TestCase
{
    use EnlightenSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
    }


    ####### fail reject request ######

    public function failRejectTeamMemberRequestUserHasNotTicket(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug'
        ])->create([
            'owner' => $owner->id,
        ]);

        $tryAcceptTeamJoinRequest = $this->postJson(route('team.rejectJoin', [
            'teamSlug' => $team->slug,
            'userIdx' => $activeUser->id
        ]))->assertUnauthorized();

        $this->assertFalse($tryAcceptTeamJoinRequest['ok']);
        $this->assertFalse($tryAcceptTeamJoinRequest['isValid']);
        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $tryAcceptTeamJoinRequest['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failRejectTeamMemberRequestUserAlreadyTeamMember(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug'
        ])->create([
            'owner' => $owner->id,
        ]);

        $card = InvitationCard::factory()->create([
            'user_id' => $activeUser->id,
            'team_id' => $team->id,
            'from_type' => InvitationCard::FROM_NORMAL_USER,
            'invitation_card_creator' => $activeUser->id
        ]);


        Member::factory()->create([
            'team_id' => $team->id,
            'user_id' => $activeUser->id,
        ]);

        $tryAcceptTeamJoinRequest = $this->postJson(route('team.rejectJoin', [
            'teamSlug' => $team->slug,
            'userIdx' => $activeUser->id
        ]))->assertUnauthorized();

        $this->assertFalse($tryAcceptTeamJoinRequest['ok']);
        $this->assertFalse($tryAcceptTeamJoinRequest['isValid']);
        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $tryAcceptTeamJoinRequest['messages']);
    }


    ####### fail reject request ######


    ####### fail accept request ######
    /**
     * @test
     * @enlighten
     */
    public function failAcceptTeamMemberRequestUserHasNotTicket(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug'
        ])->create([
            'owner' => $owner->id,
        ]);

        $tryAcceptTeamJoinRequest = $this->postJson(route('team.acceptJoin', [
            'teamSlug' => $team->slug,
            'userIdx' => $activeUser->id
        ]))->assertUnauthorized();

        $this->assertFalse($tryAcceptTeamJoinRequest['ok']);
        $this->assertFalse($tryAcceptTeamJoinRequest['isValid']);
        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $tryAcceptTeamJoinRequest['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failAcceptTeamMemberRequestUserAlreadyTeamMember(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug'
        ])->create([
            'owner' => $owner->id,
        ]);

        $card = InvitationCard::factory()->create([
            'user_id' => $activeUser->id,
            'team_id' => $team->id,
            'from_type' => InvitationCard::FROM_NORMAL_USER,
            'invitation_card_creator' => $activeUser->id
        ]);


        Member::factory()->create([
            'team_id' => $team->id,
            'user_id' => $activeUser->id,
        ]);

        $tryAcceptTeamJoinRequest = $this->postJson(route('team.acceptJoin', [
            'teamSlug' => $team->slug,
            'userIdx' => $activeUser->id
        ]))->assertUnauthorized();

        $this->assertFalse($tryAcceptTeamJoinRequest['ok']);
        $this->assertFalse($tryAcceptTeamJoinRequest['isValid']);
        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $tryAcceptTeamJoinRequest['messages']);
    }


    ####### fail accept request ######


    ####### fail join request ######

    /**
     * @test
     * @enlighten
     */
    public function failRequestJoinToTeamWhenRequestUserAlreadyTeamMember(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug'
        ])->create([
            'owner' => $owner->id,
        ]);

        Member::factory()->create([
            'team_id' => $team->id,
            'user_id' => $activeUser->id
        ]);

        $tryRequestJoinToTeam = $this->postJson(route('user.requestJoin.to.team', [
            'teamSlug' => $team->slug
        ]))->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryRequestJoinToTeam['ok']);
        $this->assertFalse($tryRequestJoinToTeam['isValid']);
        $this->assertEquals(['code' => NormalUserJoinRequest::SENDER_ALREADY_TEAM_MEMBER], $tryRequestJoinToTeam['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failRequestJoinToTeamWhenRequestUserAlreadyRequstJoinToTeam(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug'
        ])->create([
            'owner' => $owner->id,
        ]);

        $tryRequestJoinToTeam = $this->postJson(route('user.requestJoin.to.team', [
            'teamSlug' => $team->slug
        ]))->assertOk();
        $this->assertTrue($tryRequestJoinToTeam['ok']);
        $this->assertTrue($tryRequestJoinToTeam['isValid']);
        $this->assertEquals(['sendInviteCard' => true], $tryRequestJoinToTeam['messages']);
        $card = InvitationCard::where('invitation_card_creator', $activeUser->id)->first();

        $this->assertNotNull($card);
        $this->assertEquals($team->id, $card->team_id);
        $this->assertEquals(InvitationCard::FROM_NORMAL_USER, $card->from_type);
        $this->assertEquals(InvitationCard::PENDING, $card->staus);
        $this->assertEquals($activeUser->id, $card->user_id);

        $tryRequestJoinToTeam = $this->postJson(route('user.requestJoin.to.team', [
            'teamSlug' => $team->slug
        ]))->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($tryRequestJoinToTeam['ok']);
        $this->assertFalse($tryRequestJoinToTeam['isValid']);
        $this->assertEquals(['code' => NormalUserJoinRequest::ALREADY_SEND_JOIN_REQUEST], $tryRequestJoinToTeam['messages']);
    }

    ####### fail join request ######

    ############# success case #############
    /**
     * @test
     * @enlighten
     */
    public function successRejectJoinRequest(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug'
        ])->create([
            'owner' => $owner->id,
        ]);

        $tryRequestJoinToTeam = $this->postJson(route('user.requestJoin.to.team', [
            'teamSlug' => $team->slug
        ]))->assertOk();
        $this->assertTrue($tryRequestJoinToTeam['ok']);
        $this->assertTrue($tryRequestJoinToTeam['isValid']);
        $this->assertEquals(['sendInviteCard' => true], $tryRequestJoinToTeam['messages']);
        $card = InvitationCard::where('invitation_card_creator', $activeUser->id)->first();


        $this->assertNotNull($card);
        $this->assertEquals($team->id, $card->team_id);
        $this->assertEquals(InvitationCard::FROM_NORMAL_USER, $card->from_type);
        $this->assertEquals(InvitationCard::PENDING, $card->staus);
        $this->assertEquals($activeUser->id, $card->user_id);

        Sanctum::actingAs($owner);

        $tryAcceptTeamRejectRequest = $this->postJson(route('team.rejectJoin', [
            'teamSlug' => $team->slug,
            'userIdx' => $activeUser->id,
        ]))->assertOk();

        $this->assertTrue($tryAcceptTeamRejectRequest['ok']);
        $this->assertTrue($tryAcceptTeamRejectRequest['isValid']);
        $this->assertEquals(['markTeamJoinRequestRejected' => true], $tryAcceptTeamRejectRequest['messages']);

        $this->assertNull(InvitationCard::find($card->id));
        $activeUser = User::find($activeUser->id);

        $teamMember = Member::where([
            ['user_id', '=', $activeUser->id],
            ['team_id', '=', $team->id],
        ]);
        $this->assertFalse(
            $teamMember->exists()
        );
    }

    /**
     * @test
     * @enlighten
     */
    public function successAcceptJoinRequest(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $owner = factory(User::class)->create();
        $team = factory(Team::class)->states([
            'addSlug'
        ])->create([
            'owner' => $owner->id,
        ]);

        $tryRequestJoinToTeam = $this->postJson(route('user.requestJoin.to.team', [
            'teamSlug' => $team->slug
        ]))->assertOk();
        $this->assertTrue($tryRequestJoinToTeam['ok']);
        $this->assertTrue($tryRequestJoinToTeam['isValid']);
        $this->assertEquals(['sendInviteCard' => true], $tryRequestJoinToTeam['messages']);
        $card = InvitationCard::where('invitation_card_creator', $activeUser->id)->first();


        $this->assertNotNull($card);
        $this->assertEquals($team->id, $card->team_id);
        $this->assertEquals(InvitationCard::FROM_NORMAL_USER, $card->from_type);
        $this->assertEquals(InvitationCard::PENDING, $card->staus);
        $this->assertEquals($activeUser->id, $card->user_id);

        Sanctum::actingAs($owner);

        $tryAcceptTeamJoinRequest = $this->postJson(route('team.acceptJoin', [
            'teamSlug' => $team->slug,
            'userIdx' => $activeUser->id
        ]))->assertOk();

        $this->assertTrue($tryAcceptTeamJoinRequest['ok']);
        $this->assertTrue($tryAcceptTeamJoinRequest['isValid']);
        $this->assertEquals(['markTeamInvited' => true], $tryAcceptTeamJoinRequest['messages']);

        $this->assertNull(InvitationCard::find($card->id));
        $activeUser = User::find($activeUser->id);

        $teamMember = Member::where([
            ['user_id', '=', $activeUser->id],
            ['team_id', '=', $team->id],
        ]);
        $this->assertTrue(
            $teamMember->exists()
        );
        $this->assertEquals(1, $teamMember->count());

        $teamMember = $teamMember->first();
        $this->assertEquals(
            $teamMember->role,
            Team::NORMAL_USER
        );
    }
    ############# success case #############
}
