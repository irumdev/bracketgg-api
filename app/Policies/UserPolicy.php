<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Team\Team;
use App\Models\Channel\Channel;
use App\Models\Common\Board\BaseCategory;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserPolicy
{
    use HandlesAuthorization;

    private function isVerifyEmail(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    public function unLikeChannel(User $user): bool
    {
        return $this->isVerifyEmail($user);
    }

    public function followChannel(User $user): bool
    {
        return $this->isVerifyEmail($user);
    }

    public function unFollowChannel(User $user): bool
    {
        return $this->isVerifyEmail($user);
    }

    public function likeChannel(User $user): bool
    {
        return $this->isVerifyEmail($user);
    }

    public function createChannel(User $user): bool
    {
        return $this->isVerifyEmail($user) && (
            $user->channels->count() < $this->getLimitCreateChannelCountFrom($user)
        );
    }

    public function createTeam(User $user): bool
    {
        return $this->isVerifyEmail($user) && (
            $user->teams->count() < $this->getLimitCreateTeamCountFrom($user)
        );
    }

    public function updateTeam(User $user, Team $team): bool
    {
        return $user->id === $team->owner;
    }

    public function viewTeam(User $user, Team $team): bool
    {
        return $team->members()->where('user_id', $user->id)->exists();
    }

    private function getLimitCreateTeamCountFrom(User $user): int
    {
        return $user->create_team_limit;
    }

    private function getLimitCreateChannelCountFrom(User $user): int
    {
        return $user->create_channel_limit;
    }

    public function updateChannel(User $user, Channel $channel): bool
    {
        return $user->id === $channel->owner;
    }

    public function inviteMember(User $user, Team $team, User $invitedUser): bool
    {
        return $user->id === $team->owner && (
            $invitedUser->id !== $user->id
        );
    }


    public function kickTeamMember(User $requestUser, Team $team, User $willKickUser): bool
    {
        /**
         * 팀원 추방 요청한 유저($requestUser) 가 팀장이며
         * $willKickUser 가 팀원인지
         */
        $willKickUserIsNotTeamOwner = $team->members()->where([
            ['user_id', '=', $willKickUser->id],
            ['role', '!=', Team::OWNER],
        ])->firstOr(fn (): ?User => null);

        if (! $willKickUserIsNotTeamOwner) {
            return false;
        }

        $willKickUserIsNotTeamOwner = $willKickUserIsNotTeamOwner->owner !== Team::OWNER;

        $canKickTeamMember = $team->members()->where([
            ['user_id', '=', $requestUser->id],
            ['role', '=', Team::OWNER],
        ])->exists();

        return $canKickTeamMember && $willKickUserIsNotTeamOwner;
    }

    public function acceptInvite(User $willAcceptUser, Team $team): bool
    {
        /**
         * @var bool $hasInviteCard 초대장이 존재하는지 여부, 초대장은 반드시 있어야 함
         */
        $hasInviteCard = $team->invitationCards()->where('user_id', $willAcceptUser->id)->exists();

        /**
         * @var bool $acceptedUserIsNotTeamMember 팀원이 아니여야 함
         */
        $acceptedUserIsNotTeamMember = $team->members()->where('user_id', $willAcceptUser->id)->exists() === false;

        return $hasInviteCard && $acceptedUserIsNotTeamMember;
    }

    public function rejectInvite(User $willRejectUser, Team $team): bool
    {
        /**
         * @var bool $hasInviteCard 초대장이 존재하는지 여부, 초대장은 반드시 있어야 함
         */
        $hasInviteCard = $team->invitationCards()->where('user_id', $willRejectUser->id)->exists();

        /**
         * @var bool $acceptedUserIsNotTeamMember 팀원이 아니여야 함
         */
        $rejectUserIsNotTeamMember = $team->members()->where('user_id', $willRejectUser->id)->exists() === false;

        return $hasInviteCard && $rejectUserIsNotTeamMember;
    }
}
