<?php

declare(strict_types=1);

namespace App\Models\Team;

use App\Models\Team\BannerImage;
use App\Models\Team\Broadcast;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Team extends Model
{
    protected $fillable = [
        'logo_image', 'name', 'owner', 'is_public'
    ];

    public function bannerImages(): HasMany
    {
        return $this->hasMany(BannerImage::class, 'team_id', 'id');
    }

    public function broadcastAddress(): HasMany
    {
        return $this->hasMany(Broadcast::class, 'team_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner', 'id');
    }

    public function getSlugAttribute(): string
    {
        $slugRelation = $this->slug();
        return $slugRelation->first()->slug;
    }

    public function slug(): HasOne
    {
        return $this->hasOne(Slug::class, 'team_id', 'id');
    }
}
