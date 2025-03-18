<?php

use App\Exceptions\InternalException;
use App\Exceptions\NotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Exception $exception) {
            if ($exception instanceof InternalException) {
                return redirect()->back()->withErrors($exception->getErrors())->withInput();
            }
            if ($exception instanceof NotFoundException) {
                return response()->view('errors.404', ['message' => $exception->getMessage()], 404);
            }
            //            if ($exception instanceof TokenMismatchException) {
            //                return $exception->getMessage();
            //            }
            //            if($exception instanceof \Illuminate\Auth\AuthenticationException){
            //                return redirect()->route('login');
            //            }
        });

    })->create();
