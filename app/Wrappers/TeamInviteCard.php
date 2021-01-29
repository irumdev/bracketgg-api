<?php

declare(strict_types=1);

namespace App\Wrappers;

use App\Models\Team\Team;

class TeamInviteCard
{
    public Team $team;
    public int $targetUser;
    public int $type;

    public function __construct(Team $team, int $targetUser, int $type)
    {
        $this->team = $team;
        $this->targetUser = $targetUser;
        $this->type = $type;
    }
}
