<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelFollower extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id', 'channel_id'
    ];
}
