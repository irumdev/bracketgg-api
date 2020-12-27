<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Team\Team;
use App\Models\Team\Member as TeamMember;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

use App\Factories\TeamInfoUpdateFactory;

class TeamRepository extends TeamInfoUpdateFactory
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

    public function create(array $teamInfo): Team
    {
        return DB::transaction(function () use ($teamInfo) {
            $createdTeam = Team::create($teamInfo);
            $this->createUniqueSlug($createdTeam);
            $teamMember = TeamMember::create(['team_id' => $createdTeam->id, 'user_id' => $createdTeam->owner]);
            $teamMember->role = Team::OWNER;
            $teamMember->save();
            return $createdTeam;
        });
    }

    public function update(Team $team, array $updateInfo): Team
    {
        return DB::transaction(function () use ($team, $updateInfo) {
            $canCreateOrUpdateBroadCasts = isset($updateInfo['broadcasts']) && count($updateInfo['broadcasts']) >= 1;
            if ($canCreateOrUpdateBroadCasts) {
                $this->updateBroadCast($team, $updateInfo['broadcasts']);
            }

            return $team->update($updateInfo);
        })->with(Team::TEAM_RELATIONS)->find($team->id);
    }

    public function createUniqueSlug(Team $team)
    {
        return $team->slug()->create([
            'team_id' => $team->id,
            'slug' => $team->slug()->getRelated()->unique(),
        ]);
    }

    public function updateOrCreateImage(bool $isUpdate, string $type, array $attribute): bool
    {
        return $this->updateOrCreateTeamImage(
            $isUpdate,
            $type,
            $attribute
        );
    }

    public function findByUserId(string $userId)
    {
        return Team::whereHas('user', function (Builder $query) use ($userId) {
            $query->where('owner', $userId);
        })->with(Team::TEAM_RELATIONS);
    }
}
