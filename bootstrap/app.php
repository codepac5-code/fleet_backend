<?php
use App\Http\Middleware\ConfigureDatabase;
use App\Http\Middleware\MultipleDatabases;
use App\Http\Middleware\ResolveShard;
use App\Http\Middleware\SetCountryDatabase;
use App\Http\Middleware\SetDatabaseByDialCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Database\QueryException;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Const\Messages\ErrorMessages;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Middleware\LanguageMiddleware;
use App\Http\Middleware\SetLocalization;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/Dashboard/dashboard.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function() {
            // Serve public-disk storage files (avatars, service images, uploads)
            // directly. `public/storage` is NOT a working symlink on this Windows
            // + `php artisan serve` setup, so `/storage/*` returned 403 and every
            // uploaded image (e.g. profile photos) failed to load. Reads straight
            // from the public disk root; safe path-traversal guard included.
            Route::get('storage/{path}', function (string $path) {
                $root = realpath(storage_path('app/public'));
                $full = realpath(storage_path('app/public/'.$path));
                abort_if($full === false || $root === false || ! str_starts_with($full, $root) || ! is_file($full), 404);
                return response()->file($full);
            })->where('path', '.*')->name('storage.serve');

            Route::middleware(['api', 'tenant-shard'])
                ->prefix('user')
                ->name('user.')
                ->group(base_path('routes/user.php'));

            Route::middleware(['api', 'tenant-shard'])
                ->prefix('driver')
                ->name('driver.')
                ->group(base_path('routes/driver.php'));

            Route::middleware(['api', 'tenant-shard'])
                ->prefix('realtime')
                ->name('realtime.')
                ->group(base_path('routes/realtime.php'));

            // Payment gateway webhooks (Stripe). No auth/CSRF (verified by the
            // gateway signature); no shard — ledger_payments is on the global
            // connection. Configure the URL as `POST {host}/webhooks/payments/stripe`.
            Route::middleware('api')
                ->prefix('webhooks')
                ->name('webhooks.')
                ->post('payments/{provider}', [\App\Http\Api\V1\Controllers\PaymentWebhookController::class, 'handle'])
                ->name('payments');

            // Subscription (recurring billing) webhooks. Like the payment webhook
            // above: no auth/CSRF (verified by the Stripe signature). But office
            // subscriptions live on a tenant shard, so the controller activates
            // the shard from the event's `country` metadata before applying.
            // Configure as `POST {host}/webhooks/subscriptions/stripe`.
            Route::middleware('api')
                ->prefix('webhooks')
                ->name('webhooks.')
                ->post('subscriptions/{provider}', [\App\Http\Api\V1\Controllers\SubscriptionWebhookController::class, 'handle'])
                ->name('subscriptions');

            // Shared trip chat (rider + driver) — authenticated by EITHER guard.
            Route::middleware(['api', 'tenant-shard', 'user-api', 'auth:user,driver'])
                ->prefix('bookings')
                ->name('bookings.chat.')
                ->group(function () {
                    Route::get('{id}/chat', [\App\Http\Services\Shared\Controllers\BookingChatController::class, 'history'])->whereNumber('id');
                    Route::post('{id}/chat', [\App\Http\Services\Shared\Controllers\BookingChatController::class, 'send'])->whereNumber('id');
                    Route::post('{id}/chat/read', [\App\Http\Services\Shared\Controllers\BookingChatController::class, 'read'])->whereNumber('id');
                });

            // Push-token registration — shared by the rider + driver apps
            // (`POST /devices`), authenticated by EITHER guard.
            Route::middleware(['api', 'tenant-shard', 'user-api', 'auth:user,driver'])
                ->post('devices', [\App\Http\Services\Shared\Controllers\DeviceRegistrationController::class, 'store'])
                ->name('devices.store');

            // Driver → office link request. Public: reachable during onboarding
            // before the driver has an account (the driver is resolved from the
            // bearer token when present, for in-app office switching).
            Route::middleware(['api', 'tenant-shard', 'user-api'])
                ->post('offices/link-requests', [\App\Http\Services\Driver\Controllers\DriverApplicationsController::class, 'linkOffice'])
                ->name('offices.link-requests');

            Route::middleware([
                \App\Http\Middleware\SetupBootConfig::class,
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            ])->group(base_path('routes/setup.php'));

            Route::middleware('web')
                ->group(base_path('routes/public.php'));

            Route::middleware('web')
                ->prefix('panel')
                ->name('panel.')
                ->group(base_path('routes/panel/auth.php'));

            Route::middleware('web')
                ->prefix('panel/admin')
                ->name('panel.admin.')
                ->group(base_path('routes/panel/admin.php'));

            Route::middleware('web')
                ->prefix('panel/office')
                ->name('panel.office.')
                ->group(base_path('routes/panel/office.php'));

            Route::middleware('web')
                ->prefix('panel/employee')
                ->name('panel.employee.')
                ->group(base_path('routes/panel/employee.php'));
        }
    )->withMiddleware(function (Middleware $middleware) {

        $middleware->prepend(\App\Http\Middleware\EnsureInstalled::class);

        $middleware->alias([
            'set-language'          => LanguageMiddleware::class,
            'set-localization'      => SetLocalization::class,
            'multiple-database'     => MultipleDatabases::class,
            'SetDatabaseByDialCode' => SetDatabaseByDialCode::class,
            'SetCountryDatabase'    => SetCountryDatabase::class,
            'Resolved-Shard'         => ResolveShard::class,
            'tenant-shard'           => \App\Http\Middleware\ResolveTenantShard::class,
            'user-api'               => \App\Http\Middleware\UserApiEnvelope::class,
            'token-audience'         => \App\Http\Middleware\EnsureTokenAudience::class,
            'Configure-Database'=>ConfigureDatabase::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'panel.country-db'   => \App\Http\Middleware\Panel\ConfigureCountryDatabase::class,
            'panel.single-shard' => \App\Http\Middleware\Panel\RequireSingleShard::class,
            'panel.2fa'          => \App\Http\Middleware\Panel\RequireTwoFactorEnrollment::class,
        ]);

        // An unauthenticated panel request must land on the PANEL login form, not
        // the public marketing site (`route('login')` is the landing page `/`).
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('panel', 'panel/*')) {
                return route('panel.login');
            }

            return route('login');
        });

        // $middleware->append(LanguageMiddleware::class);

        // $middleware->use([
        //     \App\Http\Middleware\AuthSessionMiddleware::class,
        // ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (\Throwable $e, $request) {
            $route = $request->route();
            $isV2 = $request->attributes->get('fleet_v2')
                || ($route && in_array('user-api', $route->gatherMiddleware(), true));

            if ($isV2) {
                return \App\Http\Services\User\Support\Reply::fromException($e);
            }

            return null;
        });

        $exceptions->render(function (\App\Http\Core\Exceptions\DomainException $e) {
            return \App\Http\Api\V1\Support\ApiResponse::error($e->errorCode, $e->getMessage(), [], $e->status);
        });

        // $exceptions->render(function (QueryException $e) {
        //     return SendResponse::sendFiledResponse(
        //         new ResponseModel(data: $e->getTrace(), message: ErrorMessages::$dbError, status: $e->getCode())
        //     );
        // });

        // $exceptions->render(function (Throwable $e) {
        //     return SendResponse::sendExceptionResponse(
        //         new ResponseModel(data: $e->getTrace(), message: $e->getMessage(), status: $e->getCode())
        //     );
        // });

    })
    ->withProviders()->create();
