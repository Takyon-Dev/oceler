<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\ServiceProvider as BaseServiceProvider;

class AuthServiceProvider extends BaseServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates here if needed
        Gate::define('is-admin', fn (User $user) => $user->role_id === 2);
        Gate::define('is-player', fn (User $user) => $user->role_id === 1);

        // Define role-based gates
        Gate::define('manage-trials', fn (User $user) => $user->role_id === 2);
        Gate::define('participate-in-trials', fn (User $user) => $user->role_id === 1);

        // Define resource-based gates
        Gate::define('view-trial', fn (User $user, $trial) => 
            $user->role_id === 2 || $trial->users->contains($user)
        );
    }
}
