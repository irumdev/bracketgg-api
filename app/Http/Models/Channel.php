<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\ChannelBannerImage;
use App\Models\ChannelFollower;
use App\Models\ChannelBroadcast;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Channel extends Model
{
    protected $fillable = [
        'logo_image', 'follwer_count',
        'like_count', 'description',
        'name', 'owner'
    ];

    public function bannerImages(): HasMany
    {
        return $this->hasMany(ChannelBannerImage::class, 'channel_id', 'id');
    }

    public function broadcastAddress(): HasMany
    {
        return $this->hasMany(ChannelBroadcast::class, 'channel_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner', 'id');
    }

    public function getSlugAttribute(): string
    {
        $slugRelation = $this->slug();
        return $slugRelation->first()->slug;
    }

    public function fans(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            ChannelFan::class,
            'channel_id',
            'id',
            'id',
            'user_id'
        );
    }

    public function slug(): HasOne
    {
        return $this->hasOne(ChannelSlug::class, 'channel_id', 'id');
    }

    public function followers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            ChannelFollower::class,
            'channel_id',
            'id',
            'id',
            'user_id'
        );
    }
}
