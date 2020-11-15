<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
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
        return $this->isVerifyEmail($user);
    }

    private function getLimitCreateChannelCountFrom(User $user): int
    {
        return 5;
    }

    public function updateChannel(User $user, Channel $channel): bool
    {
        return $user->id === $channel->owner;
    }
}
