<?php

declare(strict_types=1);

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

use App\Models\Team\Team;

class Slug extends Model
{
    use HasFactory;
    public const MIN_SLUG_LENGTH = 4;
    public const MAX_SLUG_LENGTH = 16;

    protected $fillable = [
        'slug', 'team_id'
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    public function unique(): string
    {
        do {
            $randomSlug = strtolower(Str::random(self::MAX_SLUG_LENGTH));
        } while (self::where('slug', $randomSlug)->exists());
        return $randomSlug;
    }
}
