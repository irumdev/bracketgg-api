<?php

declare(strict_types=1);

namespace App\Factories;

use App\Factories\Update\ImageUpdateFactory;
use App\Wrappers\UpdateImageTypeWrapper;
use Illuminate\Support\Facades\DB;
use App\Contracts\TeamInfoUpdateContract;
use App\Exceptions\DBtransActionFail;
use App\Models\Team\Team;

class TeamInfoUpdateFactory implements TeamInfoUpdateContract
{
    private function resolveUpdateFactory(string $type, array $attribute): ImageUpdateFactory
    {
        return new ImageUpdateFactory(
            new UpdateImageTypeWrapper('team', $type),
            $attribute
        );
    }

    public function updateTeamImage(string $type, array $attribute): bool
    {
        return DB::transaction(function () use ($type, $attribute) {
            return $this->resolveUpdateFactory($type, $attribute)->update();
        });
    }

    public function createTeamImage(string $type, array $attribute): bool
    {
        return DB::transaction(function () use ($type, $attribute) {
            return $this->resolveUpdateFactory($type, $attribute)->create();
        });
    }

    public function updateOrCreateTeamImage(bool $isUpdate, string $type, array $attribute): bool
    {
        if ($isUpdate) {
            return $this->updateTeamImage($type, $attribute);
        }

        return $this->createTeamImage($type, $attribute);
    }

    public function updateBroadCast(Team $team, array $broadCasts): void
    {
        $broadCastInstances = $team->broadcastAddress();
        if (count($broadCasts) === 0) {
            $willDeleteBroadCastsCount = $broadCastInstances->get(['id'])->count();
            $deleteResult = $broadCastInstances->delete();

            throw_unless($willDeleteBroadCastsCount === $deleteResult, new DBtransActionFail());
        } else {
            $teamBroadCasts = $team->broadcastAddress();
            collect($broadCasts)->each(function ($broadCast) use ($teamBroadCasts, $team) {
                $createItem = [
                    'team_id' => $team->id,
                    'broadcast_address' => $broadCast['url'],
                    'platform' => $broadCast['platform']
                ];
                if (isset($broadCast['id'])) {
                    $teamBroadCasts->updateOrCreate(
                        ['id' => $broadCast['id']],
                        $createItem
                    );
                } else {
                    $teamBroadCasts->create($createItem);
                }
            });
        }
    }
}
