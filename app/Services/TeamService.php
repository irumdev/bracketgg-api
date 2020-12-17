<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Team\Team;
use App\Models\GameType;
use App\Repositories\TeamRepository;
use App\Models\Team\BannerImage as TeamBannerImages;
use App\Models\Team\Broadcast as TeamBroadCast;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

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

    public function updateLogoImage(Team $team, array $updateInfo)
    {
        return $this->teamRepository->updateImage('logo', [
            'team' => $team,
            'updateInfo' => $updateInfo,
        ]);
    }

    public function updateBannerImage(Team $team, array $updateInfo)
    {
        return $this->teamRepository->updateImage('banner', [
            'team' => $team,
            'updateInfo' => $updateInfo,
        ]);
    }

    public function findTeamsByUserId(string $userId): Collection
    {
        $getTeamsByOwnerId = $this->teamRepository->findByUserId($userId)->get();
        throw_if($getTeamsByOwnerId->count() <= 0, (new ModelNotFoundException())->setModel(Team::class));
        return $getTeamsByOwnerId->map(fn (Team $team) => $this->info($team));
    }

    public function createBannerImage(Team $team, array $updateInfo)
    {
        return $this->teamRepository->createImage('banner', [
            'team' => $team,
            'updateInfo' => $updateInfo,
        ]);
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
            'name' => $team->name,
            'memberCount' => $team->member_count,
            'logoImage' => $team->logo_image ? route('teamLogoImage', [
                'logoImage' => $team->logo_image
            ]) : null,
            'bannerImages' => $team->bannerImages->map(fn (TeamBannerImages $image) => $image->bannerImage ? route('teamBannerImage', [
                'bannerImage' => $image->bannerImage,
            ]) : null),
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
