<?php

declare(strict_types=1);

namespace App\Models\Channel;

use App\Contracts\TeamAndChannelContract;

use App\Models\Channel\BannerImage as ChannelBannerImage;
use App\Models\Channel\Follower as ChannelFollower;
use App\Models\Channel\Fan as ChannelFan;
use App\Models\Channel\Broadcast as ChannelBroadcast;
use App\Models\Channel\Slug as ChannelSlug;
use App\Models\Channel\Board\Category as ChannelBoardCategory;
use App\Models\Channel\Board\Article as ChannelArticles;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * 채널 모델 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class Channel extends Model implements TeamAndChannelContract
{
    public const DEFAULT_BOARD_CATEGORY_COUNT = 3;
    public const DEFAULT_ARTICLE_LATEST_COUNT = 10;

    protected $table = 'channels';
    protected $fillable = [
        'logo_image', 'follwer_count',
        'like_count', 'description',
        'name', 'owner'
    ];

    /**
     * 채널의 배너이미지 릴레이션 메소드 입니다.
     *
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return HasMany 배너이미지 릴레이션
     */
    public function bannerImages(): HasMany
    {
        return $this->hasMany(ChannelBannerImage::class, 'channel_id', 'id');
    }

    /**
     * 채널이 가지고있는 방송국 주소 릴레이션 메소드 입니다.
     *
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return HasMany 방송국 릴레이션
     */
    public function broadcastAddress(): HasMany
    {
        return $this->hasMany(ChannelBroadcast::class, 'channel_id', 'id');
    }

    /**
     * 채널이 가지고있는 방송국 주소 릴레이션 메소드 입니다.
     *
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return HasMany 방송국 릴레이션
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner', 'id');
    }

    /**
     * 슬러그 속성만 리턴해주는 메소드 입니다.
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return string 채널 슬러그
     */
    public function getSlugAttribute(): string
    {
        $slugRelation = $this->slug();
        return $slugRelation->first()->slug;
    }

    /**
     * 채널을 좋아요 한 유저를 가져오는 메소드 입니다,
     *
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return HasManyThrough 좋아요 유저 릴레이션
     */
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

    /**
     * 채널의 슬러그 릴레이션을 리턴하는 메소드 입니다.
     *
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return HasOne 슬러그 릴레이션
     */
    public function slug(): HasOne
    {
        return $this->hasOne(ChannelSlug::class, 'channel_id', 'id');
    }

    /**
     * 채널의 팔로워 릴레이션을 리턴하는 메소드 입니다.
     *
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return HasOne 슬러그 릴레이션
     */
    public function followers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            ChannelFollower::class,
            'channel_id',
            'id',
            'id',
            'user_id'
        )->select([
            'channel_followers.created_at as followedAt',
            'users.*',
        ]);
    }

    /**
     * 채널이 게시판에 게시한 모든 게시글들을 가져오는 릴레이션 입니다.
     *
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return HasMany 게시글 릴레이션
     */
    public function articles(): HasMany
    {
        return $this->hasMany(ChannelArticles::class, 'channel_id');
    }

    public function boardCategories(): HasMany
    {
        return $this->hasMany(ChannelBoardCategory::class, 'channel_id');
    }
}
