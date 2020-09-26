<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelFollower extends Model
{
    protected $fillable = [
        'user_id', 'channel_id'
    ];
}
