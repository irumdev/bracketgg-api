<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Team\Team;
use App\Models\Team\Member as TeamMember;
use App\Models\Team\InvitationCard;
use App\Models\User;
use App\Models\Team\Slug;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Factories\TeamInfoUpdateFactory;

use App\Models\NotificationMessage;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

use function App\Events\teamInviteResolver;

class TeamRepository extends TeamInfoUpdateFactory
{
    private Team $team;

    public static string $inviteStatusForDB = 'invite_status';

    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    /**
     * 팀장이 일반 유저에게 팀원 가입 초대 하는 메소드 입니다.
     *
     * @param Team $team 팀원 가입 초대하는 팀
     * @param User $user 초대받는 유저
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return bool 카드 생성 여부
     */
    public function sendInviteCard(Team $team, User $user): bool
    {
        return DB::transaction(function () use ($team, $user): bool {
            $card = InvitationCard::where([
                ['team_id', '=', $team->id],
                ['user_id', '=', $user->id],
            ])->firstOrCreate([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'invitation_card_creator' => Auth::id(),
                'from_type' => InvitationCard::FROM_TEAM_OWNER,
            ]);
            return $card !== null;
        });
    }

    public function kickUser(Team $team, User $user)
    {
        return DB::transaction(function () use ($team, $user): bool {
            return $team->members()->where('user_id', $user->id)->delete() === 1;
        });
    }
    public function acceptInviteCard(Team $team): bool
    {
        return DB::transaction(function () use ($team): bool {
            $willAcceptUser = Auth::id();

            $card = InvitationCard::find($team->invitationCards()->where([
                ['user_id', '=', $willAcceptUser],
                ['status', '=', InvitationCard::PENDING],
            ])->first()->id);

            $member = TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $willAcceptUser
            ]);

            $team->member_count += 1;
            $card->status = InvitationCard::ACCEPT;

            event(teamInviteResolver(
                $team,
                $willAcceptUser,
                NotificationMessage::ACCEPT_INVITE_TEAM
            ));

            $cardDataHandelReusult = $card->save() && $card->delete();
            $teamDataHandelResult = $team->save();
            $requestUserInviteResult = $member !== null;

            $isSuccess = $cardDataHandelReusult && $teamDataHandelResult;

            return $isSuccess && $requestUserInviteResult;
        });
    }

    public function rejectInviteCard(Team $team): bool
    {
        return DB::transaction(function () use ($team): bool {
            $willRejectUser = Auth::id();

            $card = InvitationCard::find($team->invitationCards()->where([
                ['user_id', '=', $willRejectUser],
                ['status', '=', InvitationCard::PENDING],
            ])->first()->id);

            $card->status = InvitationCard::REJECT;

            event(teamInviteResolver(
                $team,
                $willRejectUser,
                NotificationMessage::REJECT_INVITE_TEAM
            ));

            return $card->save() && $card->delete();
        });
    }

    public function getByModel(Team $team): Builder
    {
        return $team->with(Team::TEAM_RELATIONS)->where('id', $team->id);
    }

    public function create(array $teamInfo): Team
    {
        return DB::transaction(function () use ($teamInfo): Team {
            $createdTeam = Team::create($teamInfo);
            $this->createUniqueSlug($createdTeam);
            $teamMember = TeamMember::create(['team_id' => $createdTeam->id, 'user_id' => $createdTeam->owner]);
            $teamMember->role = Team::OWNER;
            $teamMember->save();
            return $createdTeam;
        });
    }

    public function update(Team $team, array $updateInfo): Team
    {
        return DB::transaction(function () use ($team, $updateInfo): Team {
            $canCreateOrUpdateBroadCasts = isset($updateInfo['broadcasts']);
            if ($canCreateOrUpdateBroadCasts) {
                $this->updateBroadCast($team, $updateInfo['broadcasts']);
            }

            return $team->update($updateInfo);
        })->with(Team::TEAM_RELATIONS)->find($team->id);
    }

    public function createUniqueSlug(Team $team): Slug
    {
        return $team->slug()->create([
            'team_id' => $team->id,
            'slug' => $team->slug()->getRelated()->unique(),
        ]);
    }

    public function updateOrCreateImage(bool $isUpdate, string $type, array $attribute): bool
    {
        return $this->updateOrCreateTeamImage(
            $isUpdate,
            $type,
            $attribute
        );
    }

    public function findByUserId(string $userId)
    {
        return Team::whereHas('user', function (Builder $query) use ($userId): void {
            $query->where('owner', $userId);
        })->with(Team::TEAM_RELATIONS);
    }


    /**
     * @todo 팀원 신청을 할 때 초대장 보낸것과 혼동 될 우려가 있어
     *       요청을 했는지, 요청을 받았는지 분리 할 것
     */
    public function getRequestJoinUsers(Team $team): Builder
    {
        return User::whereHas('invitationCards', function (Builder $query) use ($team): void {
            $query->where([
                ['status', '=', InvitationCard::PENDING],
                ['team_id', '=', $team->id],
            ]);
        });
    }

    public function getTeamMembers(Team $team): HasManyThrough
    {
        $userTableName = (new User())->getTable();
        $invitationTableName = (new InvitationCard())->getTable();

        $users = sprintf("%s.*", $userTableName);

        $requestJoinUsers = $team->invitationUsers()->where('status', InvitationCard::PENDING)
                                                    ->select([
                                                        sprintf("%s.status as " . self::$inviteStatusForDB, $invitationTableName),
                                                        $users,
                                                        sprintf("%s.team_id as laravel_through_key", $invitationTableName)
                                                    ]);
        $members = $team->members()->select(DB::raw(sprintf('"%s"', self::$inviteStatusForDB)));

        $teamMemberListWithPendingUsers = $members->union($requestJoinUsers);

        return $teamMemberListWithPendingUsers;
    }
}
