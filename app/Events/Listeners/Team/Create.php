<?php

declare(strict_types=1);

namespace App\Events\Listeners\Channel;

use App\Events\Dispatchrs\Team\Create as TeamCreateEvent;
use App\Exceptions\DBtransActionFail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Team\Board\Category;

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
    public function handle(TeamCreateEvent $event): void
    {
        $team = $event->team;
        $category = Category::create([
            'name' => __('board.default.name'),
            'show_order' => 1,
            'article_count' => 0,
            'is_public' => true,
            'team_id' => $team->id,
        ]);

        throw_if($category === null, new DBtransActionFail());
    }
}
