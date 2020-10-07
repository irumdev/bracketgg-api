<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ChannelBannerImage;
use App\Models\ChannelFollower;
use App\Models\ChannelBroadcast;
use App\Models\User;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Channel extends Model
{
    protected $fillable = [
        'logo_image', 'follwer_count',
        'like_count', 'description', 'name',
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
        $slugRelation = $this->hasOne(ChannelSlug::class, 'channel_id', 'id');
        return $slugRelation->first()->slug;
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

    public function resolveRouteBinding($slug, $field = null)
    {
        return ChannelSlug::where('slug', $slug)->firstOrFail()->channel;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
