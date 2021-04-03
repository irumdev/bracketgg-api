<?php

declare(strict_types=1);

namespace App\Events\Dispatchrs\Team;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Wrappers\Event\TeamInviteCard;

class InviteCard
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public TeamInviteCard $inviteCard;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TeamInviteCard $inviteCard)
    {
        $this->inviteCard = $inviteCard;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
