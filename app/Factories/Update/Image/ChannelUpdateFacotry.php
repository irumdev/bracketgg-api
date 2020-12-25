<?php

declare(strict_types=1);

namespace App\Factories\Update\Image;

use App\Exceptions\FileSaveFailException;
use App\Contracts\Image\UpdateContract;

class ChannelUpdateFacotry implements UpdateContract
{
    private string $type;
    private array $attribute;
    public function __construct(string $type, array $attribute)
    {
        $this->type = $type;
        $this->attribute = $attribute;
    }

    public function updateImage(): bool
    {
        switch ($this->type) {
            case 'banner':
                return $this->updateBanner();
            default:
                return $this->updateLogo();
        }
    }

    public function createImage(): bool
    {
        switch ($this->type) {
            case 'banner':
                return $this->createBanner();
            default:
                return $this->updateLogo();

        }
    }

    private function createBanner(): bool
    {
        $createInfo = $this->attribute['updateInfo'];
        throw_unless(
            $createInfo['banner_image']->store('channelBanners'),
            new FileSaveFailException()
        );
        $createResult = $this->attribute['channel']->bannerImages()->create([
            'channel_id' => $this->attribute['channel']->id,
            'banner_image' => $createInfo['banner_image']->hashName(),
        ]);
        return isset($createResult->id);
    }

    private function updateBanner(): bool
    {
        $channel = $this->attribute['channel'];
        $updateBannerInfo = $this->attribute['updateInfo'];
        $bannerImage = $updateBannerInfo['banner_image'];

        throw_unless($bannerImage->store('channelBanners'), new FileSaveFailException());
        return $channel->bannerImages()->where([
            ['id', '=', $updateBannerInfo['banner_image_id']],
        ])->update([
            'banner_image' => $bannerImage->hashName(),
        ]) === 1;
    }

    private function updateLogo(): bool
    {
        throw_unless($this->attribute['updateInfo']['logo_image']->store('channelLogos'), new FileSaveFailException());
        return $this->attribute['channel']->update([
            'logo_image' => $this->attribute['updateInfo']['logo_image']->hashName()
        ]);
    }
}
