<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelFan extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'channel_id',
        'user_id'
    ];
}
