<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

use App\Models\Channel;

class ChannelSlug extends Model
{
    use HasFactory;
    protected $fillable = [
        'slug', 'channel_id'
    ];
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id', 'id');
    }

    public function unique(): string
    {
        do {
            $randomSlug = Str::random(10);
        } while (self::where('slug', $randomSlug)->exists());
        return $randomSlug;
    }
}
