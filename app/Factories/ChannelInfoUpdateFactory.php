<?php

declare(strict_types=1);

namespace App\Factories;

use App\Models\Channel\Channel;
use App\Contracts\ChannelUpdateInfoContract;

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
