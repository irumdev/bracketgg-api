<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use App\Events\Dispatchrs\Team\InviteCard as TeamAcceptOrRejectInviteCardEventDispatcher;
use App\Events\Listeners\Team\InviteCard as TeamInviteCardEventListener;

use App\Events\Listeners\Board\ViewArticle as ViewArticleEventListeners;
use App\Events\Dispatchrs\Board\ViewArticle as ViewArticleEventDispatcher;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        TeamAcceptOrRejectInviteCardEventDispatcher::class => [
            TeamInviteCardEventListener::class,
        ],

        ViewArticleEventDispatcher::class => [
            ViewArticleEventListeners::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        //
    }
}
