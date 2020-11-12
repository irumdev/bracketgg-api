<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

use App\Models\Channel;

class ChannelSlug extends Model
{
    use HasFactory;
    public const MIN_SLUG_LENGTH = 4;
    public const MAX_SLUG_LENGTH = 16;

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
            $randomSlug = strtolower(Str::random(self::MAX_SLUG_LENGTH));
        } while (self::where('slug', $randomSlug)->exists());
        return $randomSlug;
    }
}
