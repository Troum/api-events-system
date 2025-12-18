<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function register(): void
    {
        Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (): bool {
            if (app()->environment('production')) {
                return (bool) config('telescope.enabled');
            }

            return true;
        });
    }

    public function boot(): void
    {
        parent::boot();

        Telescope::auth(function (Request $request): bool {
            $user = $request->user();

            if (! $user instanceof User) {
                return false;
            }

            return Gate::forUser($user)->check('viewTelescope', [$user]);
        });
    }

    protected function gate(): void
    {
        Gate::define('viewTelescope', function (User $user): bool {
            $allowedEmails = collect(explode(',', (string) config('telescope.allowed_emails', '')))
                ->map(fn (string $email): string => Str::lower(trim($email)))
                ->filter()
                ->values();

            if ($allowedEmails->isEmpty()) {
                return false;
            }

            return $allowedEmails->contains(Str::lower((string) $user->email));
        });
    }

    protected function hideSensitiveRequestDetails(): void
    {
        if (! app()->environment('production')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
            'authorization',
        ]);
    }
}
