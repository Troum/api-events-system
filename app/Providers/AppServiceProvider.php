<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        Livewire::setScriptRoute(function ($handle) {
            return Route::get('/vendor/livewire/livewire.js', $handle);
        });

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware('web');
        });

        // Настраиваем URL для файлового хранилища с автоматическим определением протокола
        // Если запрос идет по HTTPS, принудительно используем HTTPS для всех URL
        if (request() && (request()->secure() || request()->header('X-Forwarded-Proto') === 'https')) {
            URL::forceScheme('https');
        }
    }
}
