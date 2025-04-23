<?php
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
         then: function () {
            Route::middleware('api')
                ->prefix('user')
                ->name('user.')
                ->group(base_path('routes/user.php'));

            Route::middleware('api')
                ->prefix('driver')
                ->name('driver.')
                ->group(base_path('routes/driver.php'));
        }
    )->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'set-language'      => LanguageMiddleware::class,
            'set-localization'  => SetLocalization::class,
        ]);
        // $middleware->append(LanguageMiddleware::class);

        // $middleware->use([
        //     \App\Http\Middleware\AuthSessionMiddleware::class,
        // ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {

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
