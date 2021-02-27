<?php

declare(strict_types=1);

namespace Tests\Feature\Team;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Styde\Enlighten\Tests\EnlightenSetup;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Team\Team;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Team\InvitationCard;

class ShowWantJoinToTeamUsersTest extends TestCase
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
    public function failLookUpRequestJoinUserWhenLookUpUserIsNotTeamOwner(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states(['addSlug'])->create();
        $tryLookupJoinRequestUsers = $this->getJson(route('getRequestJoinUserList', [
            'teamSlug' => $team->slug,
        ]))->assertUnauthorized();

        $this->assertFalse($tryLookupJoinRequestUsers['ok']);
        $this->assertFalse($tryLookupJoinRequestUsers['isValid']);
        $this->assertUnauthorizedMessages($tryLookupJoinRequestUsers['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookUpJoinTeamRequestUsers(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = Sanctum::actingAs(factory(User::class)->create());
        $team = factory(Team::class)->states(['addSlug', 'addPendingInvitationCards'])->create([
            'owner' => $user->id,
        ]);

        $current = 1;

        do {
            $requestUrl = route('getRequestJoinUserList', [
                'teamSlug' => $team->slug,
            ]) . '?' . http_build_query([
                'page' => $current
            ]);

            $tryLookupJoinRequestUsers = $this->getJson($requestUrl)->assertOk();

            $this->assertTrue($tryLookupJoinRequestUsers['ok']);
            $this->assertTrue($tryLookupJoinRequestUsers['isValid']);

            $responseJoinRequestUsers = $tryLookupJoinRequestUsers['messages']['requestUsers'];

            collect($responseJoinRequestUsers)->each(function ($wantJoinUser) use ($team) {
                $base = User::whereHas('invitationCards', function (Builder $query) use ($team) {
                    $query->where([
                        ['status', '=', InvitationCard::PENDING],
                        ['team_id', '=', $team->id],
                    ]);
                })->where('id', $wantJoinUser['id'])->first();

                $this->assertTrue($base !== null);
                $this->assertEquals($base->nick_name, $wantJoinUser['nickName']);
                $this->assertEquals($base->email, $wantJoinUser['email']);
                $this->assertEquals($base->profile_image, $wantJoinUser['profileImage']);
                $this->assertEquals($base->created_at, $wantJoinUser['createdAt']);
            });

            $current += 1;
        } while ($tryLookupJoinRequestUsers['messages']['meta']['hasMorePage']);
    }
}
