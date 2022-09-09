<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\Location;
use App\Models\User;
use App\Policies\LocationPolicy;
use App\Policies\TagPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Tags\Tag;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class     => UserPolicy::class,
        Location::class => LocationPolicy::class,
        Tag::class      => TagPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('adminDashboard', static fn(User $user) => $user->role === Role::Admin->value);
    }
}
