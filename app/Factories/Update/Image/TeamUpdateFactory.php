<?php

declare(strict_types=1);

namespace App\Factories\Update\Image;

use App\Exceptions\FileSaveFailException;
use App\Contracts\Image\UpdateContract;

class TeamUpdateFactory implements UpdateContract
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
            $createInfo['banner_image']->store('teamBanners'),
            new FileSaveFailException()
        );
        $createResult = $this->attribute['team']->bannerImages()->create([
            'team_id' => $this->attribute['team']->id,
            'banner_image' => $createInfo['banner_image']->hashName(),
        ]);
        return isset($createResult->id);
    }

    private function updateBanner(): bool
    {
        $team = $this->attribute['team'];
        $updateBannerInfo = $this->attribute['updateInfo'];
        $bannerImage = $updateBannerInfo['banner_image'];

        throw_unless(
            $bannerImage->store('teamBanners'),
            new FileSaveFailException()
        );

        throw_unless($bannerImage->store('teamBanners'), new FileSaveFailException());
        return $team->bannerImages()->where([
            ['id', '=', $updateBannerInfo['banner_image_id']],
        ])->update([
            'banner_image' => $bannerImage->hashName(),
        ]) === 1;
    }

    private function updateLogo(): bool
    {
        throw_unless($this->attribute['updateInfo']['logo_image']->store('teamLogos'), new FileSaveFailException());
        $updateResult = $this->attribute['team']->update([
            'logo_image' => $this->attribute['updateInfo']['logo_image']->hashName()
        ]);
        return isset($updateResult->id);
    }
}
