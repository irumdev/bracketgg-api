<?php

namespace App\Contracts;

use App\Models\Channel;
use App\Models\ChannelBannerImage;
use Illuminate\Http\UploadedFile;

interface ChannelUpdateInfoContract
{
    public function slug(Channel $channel, string $slug = null): void;
    public function logoImage(Channel $channel, UploadedFile $logoImage = null): ?string;
    public function bannerImage(Channel $channel, array $bannerImages = null): void;
}
