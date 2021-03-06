<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Channel\Channel;
use App\Models\Channel\BannerImage as ChannelBannerImage;
use App\Helpers\ResponseBuilder;
use App\Models\Channel\Broadcast as ChannelBroadcast;
use App\Models\Channel\Slug as ChannelSlug;
use App\Repositories\ChannelRepository;
use App\Repositories\Channel\BoardRespository as ChannelBoardRepository;
use App\Exceptions\DBtransActionFail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Helpers\Image;
use App\Models\Channel\Board\Article;
use Illuminate\Support\Carbon;

class ChannelService
{
    private ChannelRepository $channelRepostiroy;
    private ChannelBoardRepository $channelBoardRepository;
    private ResponseBuilder $responseBuilder;

    public function __construct(ChannelBoardRepository $channelBoardRepository, ChannelRepository $channelRepostiroy, ResponseBuilder $responseBuilder)
    {
        $this->channelRepostiroy = $channelRepostiroy;
        $this->channelBoardRepository = $channelBoardRepository;
        $this->responseBuilder = $responseBuilder;
    }

    public function findBySlug(string $slug): Channel
    {
        return ChannelSlug::where('slug', $slug)->firstOr(function (): void {
            throw (new ModelNotFoundException())->setModel(Channel::class);
        })->channel;
    }

    public function findChannelsByUserId(string $userId): Collection
    {
        $getUserChannelsByUserId = $this->channelRepostiroy->findByUserId($userId)->simplePaginate();
        throw_unless($getUserChannelsByUserId->isNotEmpty(), (new ModelNotFoundException())->setModel(Channel::class));
        $result = $this->responseBuilder->paginateMeta($getUserChannelsByUserId)->merge([
            'channels' => collect($getUserChannelsByUserId->items())->map(fn (Channel $channel): array => $this->info($channel))
        ]);
        return $result;
    }

    /**
     * @deprecated
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

    public function createChannel(array $createChannnelInfo): Channel
    {
        return $this->channelRepostiroy->create($createChannnelInfo);
    }

    public function updateChannelInfoWithOutImage(Channel $channel, array $updateInfo): bool
    {
        $updateResult = $this->channelRepostiroy->updateChannelInfoWithOutImage($channel, $updateInfo);
        throw_unless($updateResult, new DBtransActionFail());
        return $updateResult;
    }

    public function createBannerImage(array $bannerInfo, Channel $channel): bool
    {
        return $this->channelRepostiroy->createImage('banner', [
            'channel' => $channel,
            'updateInfo' => $bannerInfo
        ]);
    }

    public function updateBannerImage(array $bannerInfo, Channel $channel): bool
    {
        return $this->channelRepostiroy->updateImage('banner', [
            'channel' => $channel,
            'updateInfo' => $bannerInfo
        ]);
    }

    public function updateLogoImage(array $logoInfo, Channel $channel): bool
    {
        return $this->channelRepostiroy->updateImage('logo', [
            'channel' => $channel,
            'updateInfo' => $logoInfo
        ]);
    }

    public function followers(Channel $channel): HasManyThrough
    {
        return $this->channelRepostiroy->followers($channel);
    }

    public function info(Channel $channel): array
    {
        return [
            'id' => $channel->id,
            'name' => $channel->name,
            'logoImage' => $channel->logo_image ? Image::toStaticUrl('channelLogoImage', [
                'logoImage' => $channel->logo_image,
            ]) : null,
            'followerCount' => $channel->follwer_count,
            'likeCount' => $channel->like_count,
            'description' => $channel->description,
            'bannerImages' => $channel->bannerImages->map(function (ChannelBannerImage $channelBannerImage): array {
                /**
                 * @todo else문 걸릴때 타입힌팅 처리
                 */
                if ($channelBannerImage->banner_image) {
                    return [
                        'id' => $channelBannerImage->id,
                        'imageUrl' => Image::toStaticUrl('channelBannerImage', [
                            'bannerImage' => $channelBannerImage->banner_image,
                        ])
                    ];
                }
            }),
            'broadCastAddress' => $channel->broadcastAddress->map(fn (ChannelBroadcast $channelBroadcast): array => [
                'platform' => $channelBroadcast->platform,
                'platformKr' => ChannelBroadcast::$platforms[$channelBroadcast->platform],
                'broadcastAddress' => $channelBroadcast->broadcastAddress,
                'broadcastId' => $channelBroadcast->broadcastId,
            ]),
            'latestArticles' => $this->channelBoardRepository->latestTenArticles($channel)->map(fn (Article $article): array => [
                'id' => $article->id,
                'title' => $article->title,
                'categoryName' => $article->category->name,
                'createdAt' => Carbon::parse($article->created_at)->format('Y-m-d H:i:s'),
            ]),
            'latestArticlesCount' => $this->channelBoardRepository->latestArticlesCount($channel),
            'owner' => $channel->owner,
            'slug' => $channel->slug,
        ];
    }
}
