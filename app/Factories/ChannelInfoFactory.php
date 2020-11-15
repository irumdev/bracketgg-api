<?php

declare(strict_types=1);

namespace App\Factories;

use App\Contracts\ChannelUpdateInfoContract;
use App\Models\Channel\Channel;
use App\Models\Channel\BannerImage as ChannelBannerImage;

use Illuminate\Http\UploadedFile;

use App\Exceptions\FileSaveFailException;

class ChannelInfoFactory implements ChannelUpdateInfoContract
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

    public function logoImage(Channel $channel, UploadedFile $logoImage = null): ?string
    {
        $fileName = null;
        if ($this->isNoEmpty($logoImage)) {
            throw_unless($logoImage->store('channelLogo'), new FileSaveFailException());
            $fileName = $logoImage->hashName();
        }
        return $fileName;
    }

    public function bannerImage(Channel $channel, array $bannerImage = null): void
    {
        if ($this->isNoEmpty($bannerImage)) {
            throw_unless($bannerImage['bannerImage']->store('channelBanners'), new FileSaveFailException());
            $channel->bannerImages()->where([
                ['id', '=', $bannerImage['id']],
            ])->update([
                'banner_image' => $bannerImage['bannerImage']->hashName(),
            ]);
        }
    }
}
