<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface TeamAndChannelContract
{
    public function getSlugAttribute(): string;

    public function bannerImages(): HasMany;
    public function broadcastAddress(): HasMany;
    public function boardCategories(): HasMany;
    public function articles(): HasMany;
    public function user(): BelongsTo;
}
