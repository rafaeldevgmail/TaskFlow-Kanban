<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\Client;
use App\Models\Task;
use App\Policies\ClientPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Gate;

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
        /*if (config('app.env') !== 'local' || env('CODESPACES')) {
            URL::forceScheme('https');
        }*/
        Gate::policy(Task::class,   TaskPolicy::class);
        Gate::policy(Client::class, ClientPolicy::class);
    }
}
