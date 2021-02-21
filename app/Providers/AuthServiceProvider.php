<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\UserPolicy;

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

    private array $registerPolicies = [
        'followChannel'   => [UserPolicy::class, 'followChannel'],
        'unFollowChannel' => [UserPolicy::class, 'unFollowChannel'],

        'likeChannel' =>   [UserPolicy::class, 'likeChannel'],
        'unLikeChannel' => [UserPolicy::class, 'unLikeChannel'],

        'createChannel' => [UserPolicy::class, 'createChannel'],
        'updateChannel' => [UserPolicy::class, 'updateChannel'],

        'createTeam' => [UserPolicy::class, 'createTeam'],
        'updateTeam' => [UserPolicy::class, 'updateTeam'],
        'viewTeam' =>   [UserPolicy::class, 'viewTeam'],

        'inviteMember' => [UserPolicy::class, 'inviteMember'],
        'acceptInvite' => [UserPolicy::class, 'acceptInvite'],
        'rejectInvite' => [UserPolicy::class, 'rejectInvite'],
        'kickTeamMember' => [UserPolicy::class, 'kickTeamMember'],
    ];

    private function registerCustomPolicies(): void
    {
        collect($this->registerPolicies)->each(fn ($policyMethod, $policyKey) => Gate::define($policyKey, $policyMethod[0] . '@' . $policyMethod[1]));
    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerCustomPolicies();
    }
}
