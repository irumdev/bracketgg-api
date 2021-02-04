<?php

declare(strict_types=1);

namespace App\Models\Team;

use App\Models\Team\BannerImage;
use App\Models\Team\Broadcast;

use App\Models\User;
use App\Models\GameType;
use App\Models\Team\OperateGame;
use App\Models\Team\Member as TeamMember;
use App\Models\Team\InvitationCard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;
    protected $table = 'teams';
    protected $fillable = [
        'logo_image', 'name', 'owner', 'is_public', 'member_count'
    ];

    protected $casts = [
        'is_public' => 'bool'
    ];

    public const TEAM_RELATIONS = [
        'bannerImages:id,team_id,banner_image AS bannerImage',
        'broadcastAddress:team_id,broadcast_address AS broadcastAddress,platform',
        'operateGames:name',
        'slug:id,slug'
    ];

    public const OWNER = 1;
    public const NORMAL_USER = 2;

    public const DEFAULT_BOARD_CATEGORY_COUNT_LIMIT = 3;

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

    public function invitationCards(): HasMany
    {
        return $this->hasMany(InvitationCard::class, 'team_id', 'id');
    }

    public function invitationUsers(): HasManyThrough
    {
        return $this->hasManyThroughUsers(InvitationCard::class);
    }

    public function operateGames(): HasManyThrough
    {
        return $this->hasManyThrough(
            GameType::class,
            OperateGame::class,
            'team_id',
            'id',
            'id',
            'game_type_id'
        );
    }

    public function members(): HasManyThrough
    {
        return $this->hasManyThroughUsers(TeamMember::class);
    }

    private function hasManyThroughUsers(string $throyghClass): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            $throyghClass,
            'team_id',
            'id',
            'id',
            'user_id'
        );
    }

    /**
     * @override
     * @see vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php
     * @see Illuminate\Database\Eloquent\Model
     */
    public function update(array $attributes = [], array $options = []): Team
    {
        $hasSlugAttribute = isset($attributes['slug']);
        $hasGameAttributes = isset($attributes['games']);

        if ($hasSlugAttribute) {
            $this->updateSlug($attributes['slug']);
        }

        if ($hasGameAttributes) {
            // 게임이 있을때
            $removeGameList = collect($this->operateGames)->map(fn (GameType $game) => $game->name)->diff(
                $games = collect($attributes['games'])
            );

            $this->removeUnOperateGames($this->operateGames->filter(fn (GameType $game) => $removeGameList->contains($game->name)));
            $this->createOperateGames($games);
        }
        $this->fill($attributes)->save();
        return $this;
    }

    public function removeUnOperateGames(Collection $removeGames): bool
    {
        return $removeGames->map(fn (GameType $removeGame) => OperateGame::where([
            ['team_id', '=', $this->id],
            ['game_type_id', '=', $removeGame->id]
        ])->delete())->filter(fn (bool $isSuccessDelete) => $isSuccessDelete === true)->count() === $removeGames->count();
    }

    private function updateSlug(string $slug): void
    {
        $this->slug()->update(['slug' => $slug]);
    }

    private function createOperateGames(Collection $gameNames): void
    {
        $gameTypeRelation = $this->operateGames()->getRelated();
        $gameNames->each(function (string $gameName) use ($gameTypeRelation) {
            $gameType = $gameTypeRelation->where('name', $gameName)->firstOrCreate(['name' => $gameName]);
            $findCondition = [
                ['team_id', '=', $this->id],
                ['game_type_id', '=', $gameType->id],
            ];
            OperateGame::where($findCondition)->firstOrCreate([
                'team_id' => $this->id,
                'game_type_id' => $gameType->id
            ]);
        });
    }
}
