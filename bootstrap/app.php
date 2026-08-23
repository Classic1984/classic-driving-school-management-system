<?php

use App\Http\Middleware\EnsureUserCanManageCourses;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsDirector;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'director' => EnsureUserIsDirector::class,
            'course-manager' => EnsureUserCanManageCourses::class,
        ]);

        // The marketing site posts here cross-origin with no session, so it
        // can never carry a CSRF token - CORS + rate limiting guard it instead.
        $middleware->validateCsrfTokens(except: [
            'public/leads',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
