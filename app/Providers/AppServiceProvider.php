<?php

namespace App\Providers;

use App\Services\AspNetIdentityHasher;
use App\View\Composers\SiteChromeComposer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.app', SiteChromeComposer::class);

        // All accounts were created by the original .NET/ASP.NET Core Identity
        // app - hash/verify in that same PBKDF2 format app-wide so existing
        // logins keep working and stay readable by the .NET app during the
        // transition (see App\Services\AspNetIdentityHasher).
        Hash::extend('aspnet', fn () => new AspNetIdentityHasher);
        config(['hashing.driver' => 'aspnet']);
    }
}
