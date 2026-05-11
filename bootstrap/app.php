<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetMode::class,
            \App\Http\Middleware\ContentSecurityPolicy::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Bounce stale CSRF (419) on auth endpoints straight to the login
        // page instead of showing the unhelpful 419 page-expired view.
        //  - /logout : user intent is clear and logout is non-destructive,
        //              so we force the logout server-side and redirect.
        //  - /login  : stale form (e.g. left open in another tab) — show a
        //              fresh login form with a new CSRF token.
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->is('logout')) {
                \Illuminate\Support\Facades\Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login');
            }
            if ($request->is('login')) {
                return redirect()->route('login');
            }
            return null; // default 419 page for other routes
        });
    })->create();
