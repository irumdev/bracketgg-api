<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
