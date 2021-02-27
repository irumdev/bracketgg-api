<?php

declare(strict_types=1);

namespace Tests\Feature\Team;

use App\Models\Team\Team;
use App\Models\Team\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Team\InvitationCard;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ShowMemberInfoTest extends TestCase
{
    /**
     * 1. 팀원이 아닌 상태로 조회 실패
     * 2. 팀원이 나밖에 없는 상태
     * 3. 나밖에 없는 상태 + 신청 유저 있는 상태
     * 4. 신청유저 + 팀원들 있는 상태
     * 5. 팀장 조회가 아닌, 팀원도 조회에 성공하라
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookupTeamMemberListWhenUserIsNotTeamMember(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $requestUser = Sanctum::actingAs(factory(User::class)->create());

        $team = factory(Team::class)->states([
            'addSlug', 'addPendingInvitationCards', 'addSignedMembers'
        ])->create();

        $requestUrl = route('getTeamMemberList', [
            'teamSlug' => $team->slug
        ]);

        $tryLookupTeamMembers = $this->getJson($requestUrl)->assertUnauthorized();

        $this->assertFalse($tryLookupTeamMembers['ok']);
        $this->assertFalse($tryLookupTeamMembers['isValid']);

        $this->assertUnauthorizedMessages($tryLookupTeamMembers['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookupWhenMemberIsOnlyTeamOwner(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $team = factory(Team::class)->states([
            'addSlug',
        ])->create();
        $requestUser = Sanctum::actingAs(User::find($team->owner));
        $pageNo = 0;
        $requestUrl = route('getTeamMemberList', [
            'teamSlug' => $team->slug
        ]) . '?' . http_build_query(['page' => $pageNo]);


        $tryLookupTeamMembers = $this->getJson($requestUrl)->assertOk();


        $this->assertTrue($tryLookupTeamMembers['ok']);
        $this->assertTrue($tryLookupTeamMembers['isValid']);

        $this->assertEquals(1, $tryLookupTeamMembers['messages']['meta']['length']);
        $this->assertEquals(1, count($tryLookupTeamMembers['messages']['teamMembers']));
        $this->assertEquals($team->owner, $tryLookupTeamMembers['messages']['teamMembers'][0]['id']);
    }


    /**
     * @test
     * @enlighten
     */
    public function successLookupPendingUsersAndMembers(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $team = factory(Team::class)->states([
            'addSlug', 'addRandInvitationCards',
            'addSignedMembers'
        ])->create();

        // $team->members->map(fn())

        $requestUser = Sanctum::actingAs(User::find($team->owner));
        $pageNo = 0;
        do {
            $requestUrl = route('getTeamMemberList', [
                'teamSlug' => $team->slug
            ]) . '?' . http_build_query(['page' => $pageNo]);

            $tryLookupTeamMembers = $this->getJson($requestUrl)->assertOk();

            $this->assertTrue($tryLookupTeamMembers['ok']);
            $this->assertTrue($tryLookupTeamMembers['isValid']);

            collect($tryLookupTeamMembers['messages']['teamMembers'])->each(function (array $teamMember) use ($team) {
                $assertUser = User::find($teamMember['id']);

                $this->assertTrue(
                    ($teamMember['inviteStatus'] === InvitationCard::PENDING) || ($teamMember['inviteStatus'] === InvitationCard::ALREADY_TEAM_MEMBER)
                );

                $this->assertNotNull($assertUser);

                $this->assertEquals($assertUser->nick_name, $teamMember['nickName']);
                $this->assertEquals($assertUser->email, $teamMember['email']);
                $this->assertEquals($assertUser->profile_image, $teamMember['profileImage']);
                $this->assertEquals(Carbon::parse($assertUser->created_at)->format('Y-m-d H:i:s'), $teamMember['createdAt']);
            });
            $pageNo += 1;
        } while ($tryLookupTeamMembers['messages']['meta']['hasMorePage']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookupOnlyPendingUsers(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());

        $team = factory(Team::class)->states([
            'addSlug', 'addManyPendingInvitationCard',
            // 'addSignedMembers'
        ])->create();

        $requestUser = Sanctum::actingAs(User::find($team->owner));
        $pageNo = 0;
        do {
            $requestUrl = route('getTeamMemberList', [
                'teamSlug' => $team->slug
            ]) . '?' . http_build_query(['page' => $pageNo]);


            $tryLookupTeamMembers = $this->getJson($requestUrl)->assertOk();

            $this->assertTrue($tryLookupTeamMembers['ok']);
            $this->assertTrue($tryLookupTeamMembers['isValid']);


            collect($tryLookupTeamMembers['messages']['teamMembers'])->each(function (array $teamMember) use ($team) {
                if ($teamMember['id'] !== $team->owner) {
                    $assertStatusValue = InvitationCard::PENDING;
                    $assertUser = InvitationCard::where([
                        ['team_id', '=', $team->id],
                        ['user_id', '=', $teamMember['id']],
                    ])->first();

                    $this->assertNotNull($assertUser);
                } else {
                    $assertStatusValue = InvitationCard::ALREADY_TEAM_MEMBER;

                    $assertUser = Member::where([

                        ['team_id', '=', $team->id],
                        ['user_id', '=', $teamMember['id']],
                    ])->first();

                    $this->assertNotNull($assertUser);
                }
                $this->assertEquals($assertStatusValue, $teamMember['inviteStatus']);


                $assertUser = User::find($teamMember['id']);

                $this->assertNotNull($assertUser);

                $this->assertEquals($assertUser->nick_name, $teamMember['nickName']);
                $this->assertEquals($assertUser->email, $teamMember['email']);
                $this->assertEquals($assertUser->profile_image, $teamMember['profileImage']);
                $this->assertEquals(Carbon::parse($assertUser->created_at)->format('Y-m-d H:i:s'), $teamMember['createdAt']);
            });
            $pageNo += 1;
        } while ($tryLookupTeamMembers['messages']['meta']['hasMorePage']);
    }
}
