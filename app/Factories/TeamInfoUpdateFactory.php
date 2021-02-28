<?php

declare(strict_types=1);

namespace App\Factories;

use App\Factories\Update\ImageUpdateFactory;
use App\Wrappers\UpdateImageTypeWrapper;
use Illuminate\Support\Facades\DB;
use App\Contracts\TeamInfoUpdateContract;
use App\Exceptions\DBtransActionFail;
use App\Models\Team\Broadcast;
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
        return DB::transaction(function () use ($type, $attribute): bool {
            return $this->resolveUpdateFactory($type, $attribute)->update();
        });
    }

    public function createTeamImage(string $type, array $attribute): bool
    {
        return DB::transaction(function () use ($type, $attribute): bool {
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
        if (count($broadCasts)) {
            $teamBroadCasts = $team->broadcastAddress();

            $broadCastIds = $teamBroadCasts->get()->map(fn (Broadcast $broadCast): int => $broadCast->id);
            $willUpdateBroadCastIds = collect($broadCasts)->filter(fn (array $broadCast): bool => isset($broadCast['id']))->map(fn (array $broadCast): int => $broadCast['id']);
            $deleteItems = $broadCastIds->diff($willUpdateBroadCastIds);
            $deleteResult = $teamBroadCasts->whereIn('id', $deleteItems)->delete();

            throw_if(
                $deleteItems->count() !== $deleteResult,
                new DBtransActionFail()
            );

            $teamBroadCasts = $team->broadcastAddress();
            collect($broadCasts)->each(function (array $broadCast) use ($teamBroadCasts, $team): void {
                if (isset($broadCast['id'])) {
                    $teamBroadCasts->where('id', $broadCast['id'])->update([
                        'broadcast_address' => $broadCast['url'],
                        'platform' => $broadCast['platform']
                    ]);
                } else {
                    $teamBroadCasts->create([
                        'broadcast_address' => $broadCast['url'],
                        'team_id' => $team->id,
                        'platform' => $broadCast['platform']
                    ]);
                }
            });
        } else {
            $broadCastInstances = $team->broadcastAddress();
            $willDeleteBroadCastsCount = $broadCastInstances->get(['id'])->count();
            $deleteResult = $broadCastInstances->delete();
            throw_unless($willDeleteBroadCastsCount === $deleteResult, new DBtransActionFail());
        }
    }
}
