<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Team\Team;
use App\Models\GameType;
use App\Repositories\TeamRepository;
use App\Models\Team\BannerImage as TeamBannerImages;
use App\Models\Team\Broadcast as TeamBroadCast;
use App\Repositories\Team\BoardRespository as TeamBoardRepository;

use App\Helpers\Image;
use App\Models\Team\Board\Article;

use App\Properties\Paginate;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Carbon;

class TeamService
{
    private TeamRepository $teamRepository;
    private TeamBoardRepository $teamBoardRepository;

    private const USE_UPDATE = true;
    private const USE_CREATE = false;

    public function __construct(TeamRepository $teamRepository, TeamBoardRepository $teamBoardRepository)
    {
        $this->teamRepository = $teamRepository;
        $this->teamBoardRepository = $teamBoardRepository;
    }

    public function createTeam(array $createTeamInfo): array
    {
        $createdTeam = $this->teamRepository->create($createTeamInfo);
        return $this->info($createdTeam);
    }

    public function updateLogoImage(Team $team, array $updateInfo): bool
    {
        return $this->teamRepository->updateOrCreateImage(self::USE_UPDATE, 'logo', [
            'team' => $team,
            'updateInfo' => $updateInfo,
        ]);
    }

    public function updateBannerImage(Team $team, array $updateInfo): bool
    {
        return $this->teamRepository->updateOrCreateImage(self::USE_UPDATE, 'banner', [
            'team' => $team,
            'updateInfo' => $updateInfo,
        ]);
    }

    public function findTeamsByUserId(string $userId): Collection
    {
        $getTeamsByOwnerId = $this->teamRepository->findByUserId($userId)->get();
        throw_if($getTeamsByOwnerId->count() <= 0, (new ModelNotFoundException())->setModel(Team::class));
        return $getTeamsByOwnerId->map(fn (Team $team): array => $this->info($team));
    }

    public function createBannerImage(Team $team, array $updateInfo): bool
    {
        return $this->teamRepository->updateOrCreateImage(self::USE_CREATE, 'banner', [
            'team' => $team,
            'updateInfo' => $updateInfo,
        ]);
    }

    public function sendInviteCard(Team $team, User $user): bool
    {
        return $this->teamRepository->sendInviteCard($team, $user);
    }

    public function acceptInviteCard(Team $team): bool
    {
        return $this->teamRepository->acceptInviteCard($team);
    }

    public function rejectInviteCard(Team $team): bool
    {
        return $this->teamRepository->rejectInviteCard($team);
    }

    public function updateInfo(Team $team, array $updateInfo): array
    {
        $updateTeamResult = $this->teamRepository->update($team, $updateInfo);
        return $this->info($updateTeamResult);
    }

    public function getRequestJoinUsers(Team $team): Paginator
    {
        return $this->teamRepository->getRequestJoinUsers($team)->simplePaginate(Paginate::PER);
    }

    public function getTeamMembers(Team $team): Paginator
    {
        return $this->teamRepository->getTeamMembers($team)->simplePaginate(Paginate::PER);
    }

    public function kickByUserModel(Team $team, User $willKickUser): bool
    {
        return $this->teamRepository->kickUser($team, $willKickUser);
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
            'bannerImages' => $team->bannerImages->map(function (TeamBannerImages $bannerImage): array {
                if ($bannerImage->bannerImage) {
                    return [
                        'id' => $bannerImage->id,
                        'imageUrl' => Image::toStaticUrl('teamBannerImage', [
                            'bannerImage' => $bannerImage->bannerImage,
                        ])
                    ];
                }
            }),
            'broadCastAddress' => $team->broadcastAddress->map(fn (TeamBroadCast $teamBroadcast): array => [
                'broadcastAddress' => $teamBroadcast->broadcastAddress,
                'platform' => $teamBroadcast->platform,
                'platformKr' => TeamBroadCast::$platforms[$teamBroadcast->platform],
                'broadcastId' => $teamBroadcast->broadcastId,
            ]),
            'latestArticles' => $this->teamBoardRepository->latestTenArticles($team)->map(fn (Article $article): array => [
                'id' => $article->id,
                'title' => $article->title,
                'categoryName' => $article->category->name,
                'createdAt' => Carbon::parse($article->created_at)->format('Y-m-d H:i:s'),
            ]),
            'latestArticlesCount' => $this->teamBoardRepository->latestArticlesCount($team),
            'isPublic' => $team->is_public,
            'owner' => $team->owner,
            'slug' => $team->slug,
            'operateGames' => $team->operateGames->map(fn (GameType $gameType): string => $gameType->name),
            'createdAt' => Carbon::parse($team->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
