<?php

declare(strict_types=1);

namespace App\Wrappers\Type;

use App\Contracts\TeamAndChannelContract;
use App\Models\User;

class TeamInviteCard
{
    public function __construct(public TeamAndChannelContract $team, public User $user, public int $fromType)
    {
        $this->user = $user;
        $this->team = $team;
        $this->fromType = $fromType;
    }
}
