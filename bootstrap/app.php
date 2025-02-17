<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\InternalException;
use Illuminate\Validation\ValidationException;
use App\Exceptions\HttpException;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Exception $exception) {
            if ($exception instanceof ValidationException) {
                return redirect()->back()->withErrors($exception->errors());
            }

            if ($exception instanceof HttpException) {
                if ($exception->shouldRenderView()) {
                    return response()->view($exception->getViewName(), $exception->getViewData(), $exception->getCode() ?: 500);
                }
                return redirect()->back()->with('error', $exception->getMessage());
            }

            if ($exception instanceof InternalException) {
                if ($exception->shouldRenderView()) {
                    return response()->view($exception->getViewName(), $exception->getViewData(), $exception->getCode() ?: 500);
                }
                return redirect()->back()->with('error', $exception->getMessage());
            }
        });

    })->create();
