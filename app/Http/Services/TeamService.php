<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Team\Team;
use App\Models\GameType;
use App\Repositories\TeamRepository;
use App\Models\Team\BannerImage as TeamBannerImages;
use App\Models\Team\Broadcast as TeamBroadCast;

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

    public function updateInfo(Team $team, array $updateInfo): array
    {
        $updateTeamResult = $this->teamRepository->update($team, $updateInfo);
        return $this->info($updateTeamResult);
    }

    public function get(Team $team): array
    {
        return $this->info(
            $this->teamRepository->getByModel($team)->firstOrFail()
        );
    }

    public function info(Team $team): array
    {
        return [
            'id' => $team->id,
            'teamName' => $team->name,
            'logoImage' => $team->logo_image,
            'bannerImages' => $team->bannerImages->map(fn (TeamBannerImages $image) => $image->banner_image),
            'broadCastAddress' => $team->broadcastAddress->map(fn (TeamBroadCast $teamBroadcast) => [
                'broadcastAddress' => $teamBroadcast->broadcastAddress,
                'platform' => $teamBroadcast->platform,
                'platformKr' => TeamBroadCast::$platforms[$teamBroadcast->platform],
            ]),
            'isPublic' => $team->is_public,
            'owner' => $team->owner,
            'slug' => $team->slug,
            'operateGames' => $team->operateGames->map(fn (GameType $gameType) => $gameType->name),
        ];
    }
}
