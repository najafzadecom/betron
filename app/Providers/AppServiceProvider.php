<?php

namespace App\Providers;

// Interfaces
use App\Interfaces\BlacklistInterface;
use App\Interfaces\DashboardInterface;
use App\Interfaces\PermissionInterface;
use App\Interfaces\RoleInterface;
use App\Interfaces\SiteStatisticInterface;
use App\Interfaces\TransactionInterface;
use App\Interfaces\UserInterface;
use App\Interfaces\VendorInterface;
use App\Interfaces\VendorUserInterface;
use App\Interfaces\WalletFileInterface;
// Repositories
use App\Models\ActivityLog;
use App\Repositories\BlacklistRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\SiteStatisticRepository;
use App\Repositories\TransactionRepository;
// Others
use App\Repositories\UserRepository;
use App\Repositories\VendorRepository;
use App\Repositories\VendorUserRepository;
use App\Repositories\WalletFileRepository;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(RoleInterface::class, RoleRepository::class);
        $this->app->bind(PermissionInterface::class, PermissionRepository::class);
        $this->app->bind(TransactionInterface::class, TransactionRepository::class);
        $this->app->bind(BlacklistInterface::class, BlacklistRepository::class);
        $this->app->bind(VendorInterface::class, VendorRepository::class);
        $this->app->bind(VendorUserInterface::class, VendorUserRepository::class);
        $this->app->bind(WalletFileInterface::class, WalletFileRepository::class);
        $this->app->bind(DashboardInterface::class, DashboardRepository::class);
        $this->app->bind(SiteStatisticInterface::class, SiteStatisticRepository::class);
    }

    /**
     * Register custom authentication providers.
     */
    protected function registerAuthProviders(): void
    {
        \Illuminate\Support\Facades\Auth::provider('vendor-multi', function ($app, array $config) {
            return new \App\Auth\VendorAuthProvider($app['hash']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom auth providers
        $this->registerAuthProviders();

        // Set Carbon locale based on app locale
        Carbon::setLocale(config('app.locale'));

        // Set PHP locale for date formatting
        $locale = config('app.locale');
        if ($locale === 'az') {
            setlocale(LC_TIME, 'az_AZ.UTF-8', 'az_AZ', 'az');
        } elseif ($locale === 'tr') {
            setlocale(LC_TIME, 'tr_TR.UTF-8', 'tr_TR', 'tr');
        }

        // Rate limiter for api routes
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });

        Gate::before(function ($user, $ability) {
            // Vendor (main account) has access to everything
            if ($user instanceof \App\Models\Vendor) {
                return true;
            }

            // Admin Super Admin has access to everything
            if ($user instanceof \App\Models\User && method_exists($user, 'hasRole')) {
                return $user->hasRole('Super Admin') ? true : null;
            }

            // For VendorUser, check permissions with vendor guard
            if ($user instanceof \App\Models\VendorUser) {
                // Check if this is a vendor permission (starts with 'vendor-')
                if (str_starts_with($ability, 'vendor-')) {
                    return $user->hasPermissionTo($ability, 'vendor') ? true : null;
                }
            }

            // Let other gates handle it
            return null;
        });

        // Route Model Binding for ActivityLog
        Route::bind('activityLog', function ($value) {
            return ActivityLog::query()->findOrFail($value);
        });

        Paginator::useBootstrapFive();
    }
}
