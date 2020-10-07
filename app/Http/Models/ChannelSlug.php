<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Channel;

class ChannelSlug extends Model
{
    use HasFactory;
    protected $fillable = [
        'slug', 'channel_id'
    ];
    public function channel(): HasOne
    {
        return $this->hasOne(Channel::class, 'id', 'id');
    }
}
