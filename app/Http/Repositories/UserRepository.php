<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Channel;
use App\Models\ChannelFollower;
use App\Models\ChannelFan;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class UserRepository
{
    use SoftDeletes;
    private User $user;
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function create(array $attribute): User
    {
        return $this->user->create($attribute);
    }

    private function isAlreadyLikeOrFollowCondition(User $user, Channel $channel): array
    {
        return [
            ['user_id', '=', $user->id],
            ['channel_id', '=', $channel->id],
        ];
    }

    private function findFollowerCondition(User $user, Channel $channel)
    {
        return ChannelFollower::where($this->isAlreadyLikeOrFollowCondition($user, $channel));
    }

    public function isAlreadyFollow(User $user, Channel $channel): bool
    {
        return $this->findFollowerCondition($user, $channel)->exists();
    }

    public function followChannel(User $user, Channel $channel): ChannelFollower
    {
        $createItem = [
            'channel_id' => $channel->id,
            'user_id' => $user->id,
        ];
        return ChannelFollower::firstOrCreate(
            $createItem,
            $createItem,
        );
    }

    public function likeChannel(User $user, Channel $channel): array
    {
        $createItem = [
            'channel_id' => $channel->id,
            'user_id' => $user->id,
        ];
        $isSuccess = false;
        $isAlreadyLike = $this->isAlreadyLike($user, $channel);

        if ($isAlreadyLike === false) {
            $channel->like_count += 1;
            $channel->save();
            ChannelFan::firstOrCreate($createItem, $createItem);
            $isSuccess = true;
        }
        return [
            'isAlreadyLike' => $isAlreadyLike,
            'isSuccess' => $isSuccess
        ];
    }

    public function unLikeChannel(User $user, Channel $channel): bool
    {
        $createItem = [
            'channel_id' => $channel->id,
            'user_id' => $user->id,
        ];
        $isSuccess = false;
        $isAlreadyLike = $this->isAlreadyLike($user, $channel);

        if ($isAlreadyLike) {
            ChannelFan::where($this->isAlreadyLikeOrFollowCondition($user, $channel))->delete();
            $channel->like_count = $channel->like_count === 0 ? 0 : $channel->like_count - 1;
            $channel->save();
            $isSuccess = true;
        }
        return $isSuccess;
    }

    public function isAlreadyLike(User $user, Channel $channel): bool
    {
        return ChannelFan::where($this->isAlreadyLikeOrFollowCondition($user, $channel))->exists();
    }

    public function unFollowChannel(User $user, Channel $channel): bool
    {
        $isSuccess = false;
        $isAlreadyFollow = $this->isAlreadyFollow($user, $channel);
        if ($isAlreadyFollow) {
            $isSuccess = $this->findFollowerCondition($user, $channel)->delete();
            $channel->follwer_count = $channel->follwer_count === 0 ? 0 : $channel->follwer_count - 1;

            $channel->save();

        }
        return $isAlreadyFollow;
    }
}
