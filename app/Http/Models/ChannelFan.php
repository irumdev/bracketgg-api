<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelFan extends Model
{
    use SoftDeletes;
    public const AUTORIZE_FAIL = 1;
    public const LIKE_OK = 2;
    public const ALREADY_LIKE = 3;
    public const UNLIKE_OK = 4;
    public const ALREADY_UNLIKE = 5;

    protected $fillable = [
        'channel_id',
        'user_id'
    ];
}
