<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/public/auth/login',
            '/api/public/auth/login',
            'api/public/auth/send-otp',
            'api/public/auth/check-otp',
            'api/public/auth/forgot-password',
            'api/public/auth/logout',
            '/api/public/auth/logout',
            'api/public/registration',
            '/api/public/registration',
            'api/public/apply-fees/success',
            'api/public/apply-fees/fail',
            'api/public/apply-fees/cancel',
            'api/public/apply-fees/ipn',
            'api/public/certificate/success',
            'api/public/certificate/fail',
            'api/public/certificate/cancel',
            'api/public/certificate/ipn',
            'api/public/online-admission/success',
            'api/public/online-admission/fail',
            'api/public/online-admission/cancel',
            'api/public/online-admission/ipn',
            'api/public/student/pay-now/success',
            'api/public/student/pay-now/fail',
            'api/public/student/pay-now/cancel',
            'api/public/student/pay-now/ipn',
        ]);

        $middleware->alias([
            'auth.access' => App\Http\Middleware\AdminAccess::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin/*')) {
                return route('admin.loginme');
            }

            return route('admin.loginme');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
