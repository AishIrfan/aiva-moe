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
        // On BOTH /login and /logout we invalidate + regenerate the session
        // so the redirect's Set-Cookie carries a fresh laravel_session +
        // matching XSRF-TOKEN; the next GET renders a form whose <meta
        // name="csrf-token"> matches what the cookie holds. Without this
        // the redirect would chain back into another stale-token error.
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            $isLogout = $request->is('logout');
            $isLogin  = $request->is('login');

            if (! $isLogout && ! $isLogin) {
                return null; // default 419 page for non-auth routes
            }

            if ($isLogout) {
                \Illuminate\Support\Facades\Auth::guard('web')->logout();
            }

            // Hard refresh the session so the response sets a fresh cookie.
            // hasSession() guards against the edge case where StartSession
            // middleware didn't run (shouldn't happen on web group, but safe).
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            $redirect = redirect()->route('login');

            // Preserve the email field on /login so the user doesn't retype.
            // password / _token deliberately dropped.
            if ($isLogin) {
                $redirect = $redirect->withInput(
                    $request->except(['password', 'password_confirmation', '_token'])
                );
            }

            // no-store: keep the browser from caching the redirect chain so
            // a stuck-cached 419 from before this handler shipped clears out.
            return $redirect->header('Cache-Control', 'no-store, max-age=0');
        });
    })->create();
