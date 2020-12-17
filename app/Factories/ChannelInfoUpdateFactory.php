<?php

declare(strict_types=1);

namespace App\Factories;

use App\Models\Channel\Channel;
use App\Contracts\ChannelUpdateInfoContract;

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
}
