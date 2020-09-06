<?php
namespace App\Services;

use App\User;

class UserService
{
    public function info(User $user): array
    {
        return [
            'id' => $user->id,
            'nickName' => $user->nick_name,
            'email' => $user->email,
            'token' => $user->createToken(config('app.name'))->plainTextToken
        ];
    }
}
