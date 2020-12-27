<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Team\Team;

interface TeamInfoUpdateContract
{
    public function updateTeamImage(string $type, array $attribute): bool;
    public function createTeamImage(string $type, array $attribute): bool;
    public function updateOrCreateTeamImage(bool $isUpdate, string $type, array $attribute): bool;
    public function updateBroadCast(Team $team, array $attribute): void;
}
