<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Channel\Channel;
use Illuminate\Http\UploadedFile;

interface ChannelUpdateInfoContract
{
    public function slug(Channel $channel, string $slug = null): void;
}
