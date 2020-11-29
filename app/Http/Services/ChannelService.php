<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Channel\Channel;
use App\Models\Channel\BannerImage as ChannelBannerImage;
use App\Helpers\ResponseBuilder;
use App\Models\Channel\Broadcast as ChannelBroadcast;
use App\Models\Channel\Slug as ChannelSlug;
use App\Repositories\ChannelRepository;
use App\Exceptions\DBtransActionFail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class ChannelService
{
    private ChannelRepository $channelRepostiroy;
    private ResponseBuilder $responseBuilder;

    public function __construct(ChannelRepository $channelRepostiroy, ResponseBuilder $responseBuilder)
    {
        $this->channelRepostiroy = $channelRepostiroy;
        $this->responseBuilder = $responseBuilder;
    }

    public function findBySlug(string $slug): Channel
    {
        $findBySlugResult = ChannelSlug::where('slug', $slug)->first();
        throw_unless($findBySlugResult, (new ModelNotFoundException())->setModel(Channel::class));
        return $findBySlugResult->channel;
    }

    public function findChannelsByUserId(string $userId): Collection
    {
        $getUserChannelsByUserId = $this->channelRepostiroy->findByUserId($userId)->simplePaginate();
        throw_unless($getUserChannelsByUserId->isNotEmpty(), (new ModelNotFoundException())->setModel(Channel::class));
        $result = $this->responseBuilder->paginateMeta($getUserChannelsByUserId)->merge([
            'channels' => collect($getUserChannelsByUserId->items())->map(fn (Channel $channel) => $this->info($channel))
        ]);
        return $result;
    }

    /**
     * @todo 곧 쓸 메서드
     * 아직 안쓰는 메소드
     */
    public function findByName(string $channelName): Channel
    {
        $findByName = $this->channelRepostiroy->findByName($channelName);
        return $findByName;
    }

    public function findChannelById(int $channelId): Collection
    {
        return collect($this->info($this->channelRepostiroy->findById($channelId)));
    }

    public function createChannel(array $createChannnelInfo): array
    {
        $createdChannel = $this->channelRepostiroy->create($createChannnelInfo);
        return $this->info($createdChannel);
    }

    public function updateChannelInfoWithOutImage(Channel $channel, array $updateInfo)
    {
        $updateResult = $this->channelRepostiroy->updateChannelInfoWithOutImage($channel, $updateInfo);
        throw_unless($updateResult, new DBtransActionFail());
        return $this->info($channel);
    }

    public function followers(Channel $channel)
    {
        return $this->channelRepostiroy->followers($channel);
    }

    public function info(Channel $channel): array
    {
        return [
            'id' => $channel->id,
            'channelName' => $channel->name,
            'logoImage' => $channel->logo_image,
            'followerCount' => $channel->follwer_count,
            'likeCount' => $channel->like_count,
            'description' => $channel->description,
            'bannerImages' => $channel->bannerImages->map(fn (ChannelBannerImage $image) => $image->banner_image),
            'broadCastAddress' => $channel->broadcastAddress->map(fn (ChannelBroadcast $channelBroadcast) => collect($channelBroadcast)->merge([
                'platformKr' => ChannelBroadcast::$platforms[$channelBroadcast->platform]
            ])),
            'owner' => $channel->owner,
            'slug' => $channel->slug,
        ];
    }
}
