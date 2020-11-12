<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelBannerImage extends Model
{
    //
    protected $fillable = [
        'banner_image',
        'channel_id',
    ];
}
