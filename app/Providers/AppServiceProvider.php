<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Project;
use App\Policies\CompanyPolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(
            static fn ($user): ?bool => $user->isSystemAdministrator() ? true : null,
        );

        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);

        Vite::prefetch(concurrency: 3);
    }
}
