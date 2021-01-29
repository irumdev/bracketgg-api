<?php

declare(strict_types=1);

namespace App\Events;

use App\Events\Dispatchrs\Team\InviteCard;
use App\Wrappers\TeamInviteCard as TeamInviteCard;

use App\Models\Team\Team;

if (! function_exists('teamInviteResolver')) {
    function teamInviteResolver(Team $team, int $tagetUser, int $type): InviteCard
    {
        return new InviteCard(
            new TeamInviteCard($team, $tagetUser, $type)
        );
    }
}
