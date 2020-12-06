<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Team\Team;

use App\Models\User;
use App\Factories\Update\ImageUpdateFactory;
use App\Wrappers\UpdateTypeWrapper;
use Illuminate\Support\Facades\DB;

class TeamRepository
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
            return $createdTeam;
        });
    }

    private function resolveUpdateFactory(string $type, array $attribute): ImageUpdateFactory
    {
        return new ImageUpdateFactory(
            new UpdateTypeWrapper('team', $type),
            $attribute
        );
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

    public function updateImage(string $type, array $attribute): bool
    {
        return DB::transaction(function () use ($type, $attribute) {
            return $this->resolveUpdateFactory($type, $attribute)->update();
        });
    }

    public function createImage(string $type, array $attribute): bool
    {
        return DB::transaction(function () use ($type, $attribute) {
            return $this->resolveUpdateFactory($type, $attribute)->create();
        });
    }
}
