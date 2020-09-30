<?php

namespace App\Services;

use App\Models\User;
use App\Models\Channel;
use App\Models\ChannelFollower;
use App\Repositories\UserRepository;
use Illuminate\Support\Collection;

class UserService
{
    private UserRepository $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createToken(User $user): array
    {
        return array_merge($this->info($user), [
            'token' => $user->createToken(config('app.name'))->plainTextToken
        ]);
    }

    public function followChannel(User $user, Channel $channel): array
    {
        $isAlreadyFollow = $this->userRepository->isAlreadyFollow($user, $channel);
        $result = $this->userRepository->followChannel($user, $channel);

        return [
            'ok' => $isAlreadyFollow ? false : isset($result->id),
            'isAlreadyFollow' => $isAlreadyFollow,
        ];
    }

    public function unFollowChannel(User $user, Channel $channel): array
    {
        $unFollowChannel = $this->userRepository->unFollowChannel($user, $channel);
        return [
            'ok' => $unFollowChannel,
            'isAlreadyUnFollow' => ! $unFollowChannel
        ];
    }

    public function likeChannel(User $user, Channel $channel)
    {
        return $this->userRepository->likeChannel($user, $channel);
    }

    public function unLikeChannel(User $user, Channel $channel)
    {
        return $this->userRepository->unLikeChannel($user, $channel);

    }
    public function info(User $user): array
    {
        return [
            'id' => $user->id,
            'nickName' => $user->nick_name,
            'email' => $user->email,
        ];
    }

    public function createUser(array $attribute): User
    {
        return $this->userRepository->create($attribute);
    }
}
