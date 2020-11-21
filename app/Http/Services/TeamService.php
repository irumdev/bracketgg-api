<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Team\Team;
use App\Models\Team\BannerImage as TeamBannerImages;
use App\Models\Team\Broadcast as TeamBroadCasts;
use App\Repositories\TeamRepository;

class TeamService
{
    private TeamRepository $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function createTeam(array $createTeamInfo): array
    {
        $createdTeam = $this->teamRepository->create($createTeamInfo);
        return $this->info($createdTeam);
    }

    public function info(Team $team): array
    {
        return [
            'id' => $team->id,
            'channelName' => $team->name,
            'logoImage' => $team->logo_image,
            'bannerImages' => $team->bannerImages->map(fn (TeamBannerImages $image) => $image->banner_image),
            'broadCastAddress' => $team->broadcastAddress->map(fn (TeamBroadCasts $teamBroadcast) => collect($teamBroadcast)->merge([
                'platformKr' => TeamBroadCasts::$platforms[$teamBroadcast->platform]
            ])),
            'owner' => $team->owner,
            'slug' => $team->slug,
        ];
    }

}
