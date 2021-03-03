<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Models\Channel\Channel;
use App\Models\Channel\Follower as ChannelFollower;
use App\Models\Channel\Fan as ChannelFan;

use App\Exceptions\DBtransActionFail;
use App\Properties\Paginate;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
        return DB::transaction(fn (): User => $this->user->create($attribute));
    }

    public function findByIdx(string $idx): User
    {
        return User::findOrFail($idx);
    }

    private function isAlreadyLikeOrFollowCondition(User $user, Channel $channel): array
    {
        return [
            ['user_id', '=', $user->id],
            ['channel_id', '=', $channel->id],
        ];
    }

    private function findFollowerCondition(User $user, Channel $channel): Builder
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

        $isSuccess = DB::transaction(function () use ($createItem, $channel): bool {
            $createFanResult = ChannelFan::firstOrCreate($createItem, $createItem);
            $channel->like_count += 1;
            return $createFanResult !== null && $channel->save();
        });
        throw_unless($isSuccess, new DBtransActionFail());
        return ChannelFan::LIKE_OK;
    }

    public function unLikeChannel(User $user, Channel $channel): bool
    {
        return DB::transaction(function () use ($user, $channel): bool {
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
        return DB::transaction(function () use ($user, $channel): bool {
            $channel->follwer_count = $channel->follwer_count === 0 ? 0 : $channel->follwer_count - 1;
            return $this->findFollowerCondition($user, $channel)->delete() &&
                   $channel->save();
        });
    }

    public function markEmailAsVerified(User $user): bool
    {
        return DB::transaction(fn (): bool => $user->markEmailAsVerified());
    }

    public function followChannel(User $user, Channel $channel): ChannelFollower
    {
        $createItem = [
            'channel_id' => $channel->id,
            'user_id' => $user->id,
        ];

        return DB::transaction(function () use ($createItem, $channel): ChannelFollower {
            $channel->follwer_count += 1;
            $channel->save();
            return ChannelFollower::firstOrCreate(
                $createItem,
                $createItem,
            );
        });
    }

    public function getFollowedChannels(User $user): Paginator
    {
        return $user->followedChannel()->simplePaginate(Paginate::FOLLOWED_CHANNEL_COUNT);
    }

    public function updatePassword(User $user, string $password): bool
    {
        return DB::transaction(function () use ($user, $password): bool {
            $user->setPasswordAttribute($password);
            return $user->save();
        });
    }
}
