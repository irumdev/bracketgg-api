<?php

declare(strict_types=1);

namespace App\Models\Channel;

use Illuminate\Database\Eloquent\Model;

/**
 * 채널의 배너이미지 모델 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class BannerImage extends Model
{
    protected $table = 'channel_banner_images';

    protected $fillable = [
        'banner_image',
        'channel_id',
    ];
}
