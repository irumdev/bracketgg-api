<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Models\Channel\Slug as ChannelSlug;
use App\Models\Channel\Channel;
use App\Models\Team\Slug as TeamSlug;
use App\Models\Team\Board\Article as TeamBoardArticle;
use App\Models\Channel\Board\Article as ChannelBoardArticle;
use App\Models\User;
use App\Models\Common\Board\BaseArticle;
use App\Models\Team\Team;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        Route::bind('slug', function (string $channelSlug): Channel {
            return ChannelSlug::where('slug', $channelSlug)->firstOrFail()->channel;
        });

        Route::bind('name', function (string $channelName): Channel {
            return Channel::where('name', $channelName)->firstOrFail();
        });

        Route::bind('teamSlug', function (string $teamSlug): Team {
            return TeamSlug::where('slug', $teamSlug)->firstOrFail()->team;
        });

        Route::bind('userIdx', function (string $userIdx): User {
            return User::findOrFail($userIdx);
        });

        Route::bind('teamArticle', function (string $articleId): BaseArticle {
            return TeamBoardArticle::findOrFail($articleId);
        });

        Route::bind('channelArticle', function (string $articleId): BaseArticle {
            return ChannelBoardArticle::findOrFail($articleId);
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map(): void
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
}
