<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Gate::define('followChannel', 'App\Policies\UserPolicy@followChannel');
        Gate::define('unFollowChannel', 'App\Policies\UserPolicy@unFollowChannel');

        Gate::define('likeChannel', 'App\Policies\UserPolicy@likeChannel');
        Gate::define('unLikeChannel', 'App\Policies\UserPolicy@unLikeChannel');

        Gate::define('createChannel', 'App\Policies\UserPolicy@createChannel');

        Gate::define('updateChannel', 'App\Policies\UserPolicy@updateChannel');

        Gate::define('createTeam', 'App\Policies\UserPolicy@createTeam');
        Gate::define('updateTeam', 'App\Policies\UserPolicy@updateTeam');
        Gate::define('viewTeam', 'App\Policies\UserPolicy@viewTeam');
    }
}
