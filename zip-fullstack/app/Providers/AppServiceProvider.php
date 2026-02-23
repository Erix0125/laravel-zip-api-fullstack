<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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
        View::composer('*', function ($view): void {
            $currentUser = Auth::guard()->user();
            $userData = $currentUser instanceof Model ? $currentUser->getAttributes() : null;

            $view->with('auth', (object) [
                'check' => Auth::check(),
                'user' => $userData,
            ]);
        });
    }
}
