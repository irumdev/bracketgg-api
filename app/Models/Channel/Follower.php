<?php

declare(strict_types=1);

namespace App\Models\Channel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @todo 여기부터 주석 다시 달기
 */
class Follower extends Model
{
    use SoftDeletes;

    public const AUTORIZE_FAIL = 1;
    public const OWNER_FOLLOW_OWNER = 2;
    public const ALREADY_FOLLOW = 3;
    public const FOLLOW_OK = 4;
    public const UNFOLLOW_OK = 5;
    public const ALREADY_UNFOLLOW = 6;

    protected $table = 'channel_followers';
    protected $fillable = [
        'user_id', 'channel_id'
    ];
}
