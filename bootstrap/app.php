<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckApiToken;
use App\Http\Middleware\CheckBlacklist;
use App\Http\Middleware\VendorPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix(env('ADMIN_PREFIX', 'admin'))
                ->as('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->prefix('vendor')
                ->as('vendor.')
                ->group(base_path('routes/vendor.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'check_api_token' => CheckApiToken::class,
            'auth' => Authenticate::class,
            'check_blacklist' => CheckBlacklist::class,
            'vendor_permission' => VendorPermission::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'paypap/*',
            'paraqr/callback',
            'paraqr/salam'
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        //$schedule->command('app:sync-pratik-wallets')->everyMinute();
        //$schedule->command('app:sync-transaction')->everyMinute();
        //$schedule->command('app:send-money')->everyMinute();
        //$schedule->command('app:apply-withdrawal')->everyMinute();
        $schedule->command('withdrawals:distribute')->everyMinute();
        $schedule->command('app:delete-old-pending-transactions')->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
