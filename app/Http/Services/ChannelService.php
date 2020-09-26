<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ChannelBannerImage;
use App\Helpers\ResponseBuilder;
use App\Models\User;
use App\Models\ChannelBroadcast;

use App\Repositories\ChannelRepository;
use Illuminate\Contracts\Pagination\Paginator;
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


    public function findChannelsByUserId(string $userId): Collection
    {
        $getUserChannelsByUserId = $this->channelRepostiroy->findByUserId($userId)->simplePaginate();
        $this->assertChannel($getUserChannelsByUserId);
        $result = $this->responseBuilder->paginateMeta($getUserChannelsByUserId)->merge([
            'channels' => collect($getUserChannelsByUserId->items())->map(fn(Channel $channel) => $this->info($channel))
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
            'bannerImages' => $channel->bannerImages->map(fn(ChannelBannerImage $image) => $image->banner_image),
            'followers' => $channel->followers->map(fn(User $user) => [
                'userId' => $user->user_id,
                'nickName' => $user->nickName,
                'profileImage' => $user->profileImage,
                'email' => $user->email,
            ]),
            'followerCount' => $channel->followers->count(),
            'broadCastAddress' => $channel->broadcastAddress->map(fn (ChannelBroadcast $channelBroadcast) => collect($channelBroadcast)->merge([
                'platformKr' => ChannelBroadcast::$platforms[$channelBroadcast->platform]
            ])),
        ];
    }

    // fn(ChannelBroadcast $channelBroadcast) => $channelBroadcast->merge([
    //     'platformKr' => ChannelBroadcast::$platforms[$channelBroadcast->platform]
    // ])

    private function assertChannel(Paginator $paginator): void
    {
        if ($paginator->isNotEmpty() === false) {
            throw (new ModelNotFoundException())->setModel(Channel::class);
        }
    }
}
