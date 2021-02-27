<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Channel\Channel;
use App\Models\Channel\Follower as ChannelFollower;
use App\Models\Channel\Fan as ChannelFan;

use App\Repositories\TeamRepository;
use App\Repositories\UserRepository;

use App\Exceptions\FileSaveFailException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Image;
use App\Models\Team\InvitationCard;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        $userDetailIinfo = [
            'id' => $user->id,
            'nickName' => $user->nick_name,
            'email' => $user->email,
            'profileImage' => empty($user->profile_image) ? null : Image::toStaticUrl('profileImage', [
                'profileImage' => $user->profile_image
            ]),
            'createdAt' => Carbon::parse($user->created_at)->format('Y-m-d H:i:s'),
        ];

        if (isset($user->invite_status)) {
            $userDetailIinfo = array_merge($userDetailIinfo, ['inviteStatus' => $this->convertInviteUserStatus($user->invite_status)]);
        }

        return $userDetailIinfo;
    }

    private function convertInviteUserStatus(string $inviteStatus): int
    {
        if ($inviteStatus === TeamRepository::$inviteStatusForDB) {
            return InvitationCard::ALREADY_TEAM_MEMBER;
        }
        return (int)$inviteStatus;
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

    public function getFollowedChannels(User $user): Paginator
    {
        $pagiNatedFollowedChannel = $this->userRepository->getFollowedChannels($user);
        throw_if($pagiNatedFollowedChannel->isEmpty(), (new ModelNotFoundException())->setModel(Channel::class));
        return $pagiNatedFollowedChannel;
    }

    public function markEmailAsVerified(string $userIdx): bool
    {
        return $this->userRepository->markEmailAsVerified(
            $this->userRepository->findByIdx($userIdx)
        );
    }
}
