<?php

declare(strict_types=1);

namespace App\Events\Dispatchrs\Board;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Wrappers\ArticleEventWrapper;

class ViewArticle
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public ArticleEventWrapper $articleEvent;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ArticleEventWrapper $articleEvent)
    {
        $this->articleEvent = $articleEvent;
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
