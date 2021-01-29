<?php

declare(strict_types=1);

namespace App\Events\Listeners\Team;

use App\Events\Dispatchrs\Team\InviteCard as TeamAcceptInviteCardEvent;
use App\Exceptions\DBtransActionFail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\NotificationMessage;

class InviteCard
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  InviteCard  $event
     * @return void
     */
    public function handle(TeamAcceptInviteCardEvent $event): void
    {
        $team = $event->inviteCard->team;
        $notificationTargetUser = $event->inviteCard->targetUser;

        $inviteCard = NotificationMessage::create([
            'user_id' => $team->owner,
            'type' => $event->inviteCard->type,
            'message' => [
                'team_id' => $team->id,
                'user_id' => $notificationTargetUser
            ]
        ]);

        throw_if($inviteCard === null, new DBtransActionFail());
    }
}
