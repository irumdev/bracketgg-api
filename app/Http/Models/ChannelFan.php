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
    protected $fillable = [
        'channel_id',
        'user_id'
    ];
}
