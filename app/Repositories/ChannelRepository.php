<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Channel\Channel;
use App\Models\User;
use App\Factories\ChannelInfoUpdateFactory;
use App\Factories\Update\ImageUpdateFactory;
use App\Wrappers\UpdateTypeWrapper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChannelRepository extends ChannelInfoUpdateFactory
{
    private Channel $channel;
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    public function followers(Channel $channel): HasManyThrough
    {
        return $channel->followers();
    }

    public function findByUserId(string $userId): Builder
    {
        return Channel::whereHas('user', function ($query) use ($userId) {
            $query->where('owner', $userId);
        })->with(User::$channelsInfo);
        // return User::findOrFail($userId)->channels()->with(User::$channelsInfo)->get();
    }

    /**
     * @todo 곧 쓸 메서드
     * 아직 안쓰는 메소드
     */
    public function findByName(string $channelName): Channel
    {
        return Channel::with(User::$channelsInfo)->where('name', $channelName)->first();
    }

    public function findById(int $id): Channel
    {
        return Channel::with(User::$channelsInfo)->findOrFail($id);
    }

    public function create(array $channelInfo): Channel
    {
        return DB::transaction(function () use ($channelInfo) {
            $createdChannel = Channel::create($channelInfo);

            $createdChannel->slug()->create([
                'channel_id' => $createdChannel->id,
                'slug' => $createdChannel->slug()->getRelated()->unique(),
            ]);

            return $createdChannel;
        });
    }

    public function updateChannelInfoWithOutImage(Channel $channel, array $updateInfo): bool
    {
        return DB::transaction(function () use ($channel, $updateInfo) {
            $this->slug($channel, data_get($updateInfo, 'slug'));
            return $channel->fill(array_filter($updateInfo, fn ($item) => empty($item) === false))->save();
        });
    }

    public function createImage(string $type, array $attribute): bool
    {
        return DB::transaction(function () use ($type, $attribute) {
            return $this->resolveUpdateFactory($type, $attribute)->create();
        });
    }

    public function updateImage(string $type = null, array $attribute): bool
    {
        return DB::transaction(function () use ($type, $attribute) {
            return $this->resolveUpdateFactory($type, $attribute)->update();
        });
    }

    private function resolveUpdateFactory(string $type, array $attribute): ImageUpdateFactory
    {
        return new ImageUpdateFactory(
            new UpdateTypeWrapper('channel', $type),
            $attribute
        );
    }
}
