<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class UserService
{
    private UserRepository $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function info(User $user): array
    {
        return [
            'id' => $user->id,
            'nickName' => $user->nick_name,
            'email' => $user->email,
            'token' => $user->createToken(config('app.name'))->plainTextToken
        ];
    }

    public function createUser(array $attribute): User
    {
        return $this->userRepository->create($attribute);
    }
}
