<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Team\Team;
use App\Models\Channel\Channel;
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

    public function updateTeam(User $user, Team $team)
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
}
