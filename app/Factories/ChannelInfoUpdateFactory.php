<?php

declare(strict_types=1);

namespace App\Factories;

use App\Models\Channel\Channel;
use App\Contracts\ChannelUpdateInfoContract;
use App\Exceptions\DBtransActionFail;
use App\Models\Channel\Broadcast;

/**
 * 채널정보 업데이트 팩토리 구현체 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ChannelInfoUpdateFactory implements ChannelUpdateInfoContract
{
    private function isNoEmpty($value): bool
    {
        return empty($value) === false;
    }

    public function slug(Channel $channel, string $slug = null): void
    {
        if ($this->isNoEmpty($slug)) {
            $channel->slug()->update([
                'slug' => $slug,
            ]);
        }
    }

    public function updateBroadcast(Channel $channel, array $broadCasts): void
    {
        if (count($broadCasts)) {
            $channelBroadCasts = $channel->broadcastAddress();

            $broadCastIds = $channelBroadCasts->get()->map(fn (Broadcast $broadCast): int => $broadCast->id);
            $willUpdateBroadCastIds = collect($broadCasts)->filter(fn (array $broadCast): bool => isset($broadCast['id']))->map(fn (array $broadCast): int => $broadCast['id']);
            $deleteItems = $broadCastIds->diff($willUpdateBroadCastIds);
            $deleteResult = $channelBroadCasts->whereIn('id', $deleteItems)->delete();

            throw_if(
                $deleteItems->count() !== $deleteResult,
                new DBtransActionFail()
            );

            $channelBroadCasts = $channel->broadcastAddress();
            collect($broadCasts)->each(function (array $broadCast) use ($channelBroadCasts, $channel): void {
                if (isset($broadCast['id'])) {
                    $channelBroadCasts->where('id', $broadCast['id'])->update([
                        'broadcast_address' => $broadCast['url'],
                        'platform' => $broadCast['platform']
                    ]);
                } else {
                    $channelBroadCasts->create([
                        'channel_id' => $channel->id,
                        'broadcast_address' => $broadCast['url'],
                        'platform' => $broadCast['platform']
                    ]);
                }
            });
        } else {
            $broadCastInstances = $channel->broadcastAddress();
            $willDeleteBroadCastsCount = $broadCastInstances->get(['id'])->count();
            $deleteResult = $broadCastInstances->delete();
            throw_unless($willDeleteBroadCastsCount === $deleteResult, new DBtransActionFail());
        }
    }
}
