<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
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
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        Schema::defaultStringLength(191);

        if($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Register dynamic permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->isAdmin() ? true : null;
        });

        if (Schema::hasTable('permissions')) {
            \App\Models\Permission::all()->each(function ($permission) {
                \Illuminate\Support\Facades\Gate::define($permission->name, function ($user) use ($permission) {
                    return $user->hasPermission($permission->name);
                });
            });
        }

        \Illuminate\Support\Facades\View::composer(
            'layouts.admin', \App\Http\View\Composers\SidebarViewComposer::class
        );

        \Illuminate\Support\Facades\View::composer(
            '*', \App\Http\View\Composers\SettingsViewComposer::class
        );
    }
}
