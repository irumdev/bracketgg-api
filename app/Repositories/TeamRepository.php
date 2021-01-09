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
use Illuminate\Database\Eloquent\Collection;

class TeamRepository extends TeamInfoUpdateFactory
{
    private Team $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    public function sendInviteCard(Team $team, User $user): bool
    {
        return DB::transaction(function () use ($team, $user) {
            $card = InvitationCard::where([
                ['team_id', '=', $team->id],
                ['user_id', '=', $user->id],
            ])->firstOrCreate([
                'team_id' => $team->id,
                'user_id' => $user->id
            ]);
            return $card !== null;
        });
    }

    public function acceptInviteCard(Team $team): bool
    {
        return DB::transaction(function () use ($team) {
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

            $notificationMessage = NotificationMessage::create([
                'user_id' => $team->owner,
                'type' => NotificationMessage::ACCEPT_INVITE_TEAM,
                'message' => [
                    'team_id' => $team->id,
                    'user_id' => $willAcceptUser
                ]
            ]);

            $cardDataHandelReusult = $card->save() && $card->delete();
            $teamDataHandelResult = $team->save();
            $createMemberAndNotificationMessageResult = $notificationMessage !== null && $member !== null;

            $isSuccess = $cardDataHandelReusult && $teamDataHandelResult && $createMemberAndNotificationMessageResult;

            return $isSuccess;
        });
    }

    public function rejectInviteCard(Team $team): bool
    {
        return DB::transaction(function () use ($team) {
            $willRejectUser = Auth::id();

            $card = InvitationCard::find($team->invitationCards()->where([
                ['user_id', '=', $willRejectUser],
                ['status', '=', InvitationCard::PENDING],
            ])->first()->id);

            $card->status = InvitationCard::REJECT;

            $notificationMessage = NotificationMessage::create([
                'user_id' => $team->owner,
                'type' => NotificationMessage::REJECT_INVITE_TEAM,
                'message' => [
                    'team_id' => $team->id,
                    'user_id' => $willRejectUser
                ]
            ]);


            return $card->save() && $card->delete() && $notificationMessage !== null;
        });
    }

    public function getByModel(Team $team): Builder
    {
        return $team->with(Team::TEAM_RELATIONS)->where('id', $team->id);
    }

    public function create(array $teamInfo): Team
    {
        return DB::transaction(function () use ($teamInfo) {
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
        return DB::transaction(function () use ($team, $updateInfo) {
            $canCreateOrUpdateBroadCasts = isset($updateInfo['broadcasts']) && count($updateInfo['broadcasts']) >= 1;
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
        return Team::whereHas('user', function (Builder $query) use ($userId) {
            $query->where('owner', $userId);
        })->with(Team::TEAM_RELATIONS);
    }

    public function getRequestJoinUsers(Team $team): Builder
    {
        return User::whereHas('invitationCards', function (Builder $query) use ($team) {
            $query->where([
                ['status', '=', InvitationCard::PENDING],
                ['team_id', '=', $team->id],
            ]);
        });
    }
}
