<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repositories
use App\Repositories\EventRepository;
use App\Repositories\TeamMemberRepository;
use App\Repositories\EventPackageRepository;
use App\Repositories\TripRepository;
use App\Repositories\BookingRepository;
use App\Repositories\LoginTokenRepository;

// Repository Interfaces
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use App\Repositories\Contracts\EventPackageRepositoryInterface;
use App\Repositories\Contracts\TripRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\LoginTokenRepositoryInterface;

// Services
use App\Services\EventService;
use App\Services\TeamMemberService;
use App\Services\EventPackageService;
use App\Services\TripService;
use App\Services\BookingService;
use App\Services\AuthService;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Event Repository & Service
        $this->app->when(EventService::class)
            ->needs(EventRepositoryInterface::class)
            ->give(EventRepository::class);

        // TeamMember Repository & Service
        $this->app->when(TeamMemberService::class)
            ->needs(TeamMemberRepositoryInterface::class)
            ->give(TeamMemberRepository::class);

        // EventPackage Repository & Service
        $this->app->when(EventPackageService::class)
            ->needs(EventPackageRepositoryInterface::class)
            ->give(EventPackageRepository::class);

        // Trip Repository & Service
        $this->app->when(TripService::class)
            ->needs(TripRepositoryInterface::class)
            ->give(TripRepository::class);

        // Booking Repository & Service
        $this->app->when(BookingService::class)
            ->needs(BookingRepositoryInterface::class)
            ->give(BookingRepository::class);

        // LoginToken Repository
        $this->app->when(AuthService::class)
            ->needs(LoginTokenRepositoryInterface::class)
            ->give(LoginTokenRepository::class);

        // Также регистрируем для использования в других местах
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(TeamMemberRepositoryInterface::class, TeamMemberRepository::class);
        $this->app->bind(EventPackageRepositoryInterface::class, EventPackageRepository::class);
        $this->app->bind(TripRepositoryInterface::class, TripRepository::class);
        $this->app->bind(BookingRepositoryInterface::class, BookingRepository::class);
        $this->app->bind(LoginTokenRepositoryInterface::class, LoginTokenRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

