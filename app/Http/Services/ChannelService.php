<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ChannelBannerImage;
use App\Helpers\ResponseBuilder;
use App\Models\ChannelBroadcast;
use App\Models\ChannelSlug;
use App\Repositories\ChannelRepository;
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
        throw_if($findBySlugResult === null, (new ModelNotFoundException())->setModel(Channel::class));
        return $findBySlugResult->channel;
    }

    public function findChannelsByUserId(string $userId): Collection
    {
        $getUserChannelsByUserId = $this->channelRepostiroy->findByUserId($userId)->simplePaginate();
        throw_if($getUserChannelsByUserId->isNotEmpty() === false, (new ModelNotFoundException())->setModel(Channel::class));
        $result = $this->responseBuilder->paginateMeta($getUserChannelsByUserId)->merge([
            'channels' => collect($getUserChannelsByUserId->items())->map(fn (Channel $channel) => $this->info($channel))
        ]);
        return $result;
    }

    public function findChannelById(string $channelId): Collection
    {
        return collect($this->info($this->channelRepostiroy->findById($channelId)));
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
            'slug' => $channel->slug,
        ];
    }
}
