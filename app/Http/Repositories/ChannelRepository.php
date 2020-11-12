<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Channel;
use App\Models\ChannelSlug;
use App\Models\User;
use App\Models\ChannelBannerImage;
use App\Factories\ChannelInfoFactory;

use App\Exceptions\FileSaveFailException;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

class ChannelRepository extends ChannelInfoFactory
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

    public function updateChannelInfo(Channel $channel, array $updateInfo): bool
    {
        return DB::transaction(function () use ($channel, $updateInfo) {
            $this->slug($channel, data_get($updateInfo, 'slug'));

            $filteredBannerInfo = array_filter([
                'bannerImage' => $updateInfo['banner_image'] ?? '',
                'id' => $updateInfo['banner_image_id'] ?? ''
            ], fn ($item) => empty($item) === false);

            $this->bannerImage($channel, $filteredBannerInfo);
            $updateInfo['logo_image'] = $this->logoImage($channel, data_get($updateInfo, 'logo_image'));

            return $channel->fill(array_filter($updateInfo, fn ($item) => empty($item) === false))->save();
        });
    }

    public function followers(Channel $channel): HasManyThrough
    {
        return $channel->followers();
    }
}
