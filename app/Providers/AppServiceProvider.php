<?php

namespace App\Providers;

use App\Auth\ActiveUserProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Auth::provider('active-eloquent', function (Application $app, array $config): ActiveUserProvider {
            return new ActiveUserProvider($app->make(Hasher::class), $config['model']);
        });

        Password::defaults(fn () => Password::min(10)
            ->mixedCase()
            ->numbers()
            ->symbols());
    }
}
