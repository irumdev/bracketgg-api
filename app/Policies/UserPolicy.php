<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    private function isVerifyEmail(User $user): bool
    {
        return is_null($user->email_verified_at) === false;
    }

    public function followChannel(User $user): bool
    {
        return $this->isVerifyEmail($user);
    }

    public function likeChannel(User $user): bool
    {
        return $this->isVerifyEmail($user);
    }

    public function createChannel(User $user): bool
    {
        return $this->isVerifyEmail($user);
    }
}
