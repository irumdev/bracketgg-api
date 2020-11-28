<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Team\Team;

use App\Models\User;
use App\Factories\ChannelInfoFactory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

class TeamRepository extends ChannelInfoFactory
{
    private Team $team;
    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    public function getByModel(Team $team)
    {
        return $team->with(Team::TEAM_RELATIONS)->where('id', $team->id);
    }

    // public function findByUserId(string $userId): Builder
    // {
    //     return Channel::whereHas('user', function ($query) use ($userId) {
    //         $query->where('owner', $userId);
    //     })->with(User::$channelsInfo);
    //     // return User::findOrFail($userId)->channels()->with(User::$channelsInfo)->get();
    // }

    // /**
    //  * @todo 곧 쓸 메서드
    //  * 아직 안쓰는 메소드
    //  */
    // public function findByName(string $channelName): Channel
    // {
    //     return Channel::where('name', $channelName)->first();
    // }

    // public function findById(int $id): Channel
    // {
    //     return Channel::with(User::$channelsInfo)->findOrFail($id);
    // }

    public function create(array $teamInfo): Team
    {
        return DB::transaction(function () use ($teamInfo) {
            $createdTeam = Team::create($teamInfo);
            $this->createUniqueSlug($createdTeam);
            return $createdTeam;
        });
    }

    public function update(Team $team, array $updateInfo): Team
    {
        return DB::transaction(fn () => $team->update($updateInfo))->with(Team::TEAM_RELATIONS)
                                                                  ->where('id', $team->id)
                                                                  ->first();
    }

    public function createUniqueSlug(Team $team)
    {
        return $team->slug()->create([
            'team_id' => $team->id,
            'slug' => $team->slug()->getRelated()->unique(),
        ]);
    }
    // public function updateChannelInfo(Channel $channel, array $updateInfo): bool
    // {
    //     return DB::transaction(function () use ($channel, $updateInfo) {
    //         $this->slug($channel, data_get($updateInfo, 'slug'));

    //         $filteredBannerInfo = array_filter([
    //             'bannerImage' => $updateInfo['banner_image'] ?? '',
    //             'id' => $updateInfo['banner_image_id'] ?? ''
    //         ], fn ($item) => empty($item) === false);

    //         $this->bannerImage($channel, $filteredBannerInfo);
    //         $updateInfo['logo_image'] = $this->logoImage($channel, data_get($updateInfo, 'logo_image'));

    //         return $channel->fill(array_filter($updateInfo, fn ($item) => empty($item) === false))->save();
    //     });
    // }
}
