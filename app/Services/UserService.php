<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Channel\Channel;
use App\Models\Channel\Follower as ChannelFollower;
use App\Models\Channel\Fan as ChannelFan;

use App\Repositories\UserRepository;

use App\Exceptions\FileSaveFailException;

use Illuminate\Support\Facades\Storage;

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

    public function followChannel(User $user, Channel $channel): int
    {
        $isAlreadyFollow = $this->userRepository->isAlreadyFollowChannel($user, $channel);

        if ($isAlreadyFollow) {
            return ChannelFollower::ALREADY_FOLLOW;
        }
        $this->userRepository->followChannel($user, $channel);
        return ChannelFollower::FOLLOW_OK;
    }

    public function isFollowChannel(User $user, Channel $channel): bool
    {
        return $this->userRepository->isAlreadyFollowChannel($user, $channel);
    }

    public function unFollowChannel(User $user, Channel $channel): int
    {
        $isAlreadyFollow = $this->userRepository->isAlreadyFollowChannel($user, $channel);
        if ($isAlreadyFollow === false) {
            return ChannelFollower::ALREADY_UNFOLLOW;
        }
        $this->userRepository->unFollowChannel($user, $channel);
        return ChannelFollower::UNFOLLOW_OK;
    }

    public function likeChannel(User $user, Channel $channel): int
    {
        $isAlreadyLike = $this->userRepository->isAlreadyLike($user, $channel);
        if ($isAlreadyLike) {
            return ChannelFan::ALREADY_LIKE;
        }
        return $this->userRepository->likeChannel($user, $channel);
    }

    public function unLikeChannel(User $user, Channel $channel): int
    {
        $isAlreadyLike = $this->userRepository->isAlreadyLike($user, $channel);
        if ($isAlreadyLike) {
            $this->userRepository->unLikeChannel($user, $channel);
            return ChannelFan::UNLIKE_OK;
        }
        return ChannelFan::ALREADY_UNLIKE;
    }

    public function isAlreadyLike(User $user, Channel $channel): bool
    {
        $isAlreadyLike = $this->userRepository->isAlreadyLike($user, $channel);
        return $isAlreadyLike;
    }

    public function info(User $user): array
    {
        return [
            'id' => $user->id,
            'nickName' => $user->nick_name,
            'email' => $user->email,
            'profileImage' => empty($user->profile_image) ? null : route('profileImage', [
                'profileImage' => $user->profile_image
            ]),
        ];
    }

    public function createUser(array $attribute): User
    {
        if (isset($attribute['profile_image'])) {
            $storeImageResult = $attribute['profile_image']->store('profileImages');
            throw_unless(is_string($storeImageResult), new FileSaveFailException());
            $attribute['profile_image'] = $attribute['profile_image']->hashName();
        }
        return $this->userRepository->create($attribute);
    }

    public function markEmailAsVerified(string $userIdx): bool
    {
        return $this->userRepository->markEmailAsVerified(
            $this->userRepository->findByIdx($userIdx)
        );
    }
}
