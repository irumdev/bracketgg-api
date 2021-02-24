<?php

declare(strict_types=1);

namespace App\Events\Listeners\Channel;

use App\Events\Dispatchrs\Channel\Create as ChannelCreateEvent;
use App\Exceptions\DBtransActionFail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Channel\Board\Category;

class Create
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
    public function handle(ChannelCreateEvent $event): void
    {
        $channel = $event->channel;
        $category = Category::create([
            'name' => __('board.default.name'),
            'show_order' => 1,
            'article_count' => 0,
            'is_public' => true,
            'channel_id' => $channel->id,
        ]);

        throw_if($category === null, new DBtransActionFail());
    }
}
