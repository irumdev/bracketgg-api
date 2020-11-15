<?php

declare(strict_types=1);

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Model;

class BannerImage extends Model
{
    protected $table = 'team_banner_images';

    protected $fillable = [
        'banner_image',
        'team_id',
    ];
}
