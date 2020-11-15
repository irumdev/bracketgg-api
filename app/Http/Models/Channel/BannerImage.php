<?php

declare(strict_types=1);

namespace App\Models\Channel;

use Illuminate\Database\Eloquent\Model;

class BannerImage extends Model
{
    protected $table = 'channel_banner_images';

    protected $fillable = [
        'banner_image',
        'channel_id',
    ];
}
