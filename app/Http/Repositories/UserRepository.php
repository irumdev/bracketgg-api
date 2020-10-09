<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Channel;
use App\Models\ChannelFollower;
use App\Models\ChannelFan;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        return DB::transaction(fn () => $this->user->create($attribute));
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

    public function isAlreadyFollowChannel(User $user, Channel $channel): bool
    {
        return $this->findFollowerCondition($user, $channel)->exists();
    }

    public function likeChannel(User $user, Channel $channel): int
    {
        $createItem = [
            'channel_id' => $channel->id,
            'user_id' => $user->id,
        ];

        $isSuccess = DB::transaction(function () use ($createItem, $channel) {
            $createFanResult = ChannelFan::firstOrCreate($createItem, $createItem);
            $channel->like_count += 1;
            return $createFanResult !== null && $channel->save();
        });
        if ($isSuccess === false) {
            // throw new DbFailException();
            /**
             * @todo 익셉션 처리하기
             */
        }
        return ChannelFan::LIKE_OK;
    }

    public function unLikeChannel(User $user, Channel $channel): bool
    {
        return DB::transaction(function () use ($user, $channel) {
            $deleteResult = ChannelFan::where($this->isAlreadyLikeOrFollowCondition($user, $channel))->delete();
            $channel->like_count = $channel->like_count === 0 ? 0 : $channel->like_count - 1;
            return $deleteResult !== null && $channel->save();
        });
    }

    public function isAlreadyLike(User $user, Channel $channel): bool
    {
        return ChannelFan::where($this->isAlreadyLikeOrFollowCondition($user, $channel))->exists();
    }

    public function unFollowChannel(User $user, Channel $channel): bool
    {
        return DB::transaction(function () use ($user, $channel) {
            $channel->follwer_count = $channel->follwer_count === 0 ? 0 : $channel->follwer_count - 1;
            return $this->findFollowerCondition($user, $channel)->delete() &&
                   $channel->save();
        });
    }

    public function followChannel(User $user, Channel $channel): ChannelFollower
    {
        $createItem = [
            'channel_id' => $channel->id,
            'user_id' => $user->id,
        ];

        return DB::transaction(function () use ($createItem, $channel) {
            $channel->follwer_count += 1;
            $channel->save();
            return ChannelFollower::firstOrCreate(
                $createItem,
                $createItem,
            );
        });
    }
}
