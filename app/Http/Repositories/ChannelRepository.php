<?php

namespace App\Repositories;

use App\Models\Channel;
use App\Models\ChannelSlug;
use App\Models\User;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ChannelRepository
{
    private Channel $channel;
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
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
        return Channel::where('name', $channelName)->first();
    }

    public function findById(string $id): Channel
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
}
