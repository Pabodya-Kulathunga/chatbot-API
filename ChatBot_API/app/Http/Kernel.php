<?php

namespace App\Http;

use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware will be run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    // protected $middleware = [
    //     \App\Http\Middleware\TrustProxies::class,
    //     \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
    //     \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    //     \App\Http\Middleware\RedirectIfAuthenticated::class,
    //     \Illuminate\Session\Middleware\StartSession::class,
    //     \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    //     \App\Http\Middleware\Authenticate::class,
    //     \Illuminate\Routing\Middleware\SubstituteBindings::class,
    // ];

    /**
     * The application's route middleware groups.
     *
     * These middleware can be applied to groups of routes.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            
            \App\Http\Middleware\VerifyCsrfToken::class, // Default CSRF middleware for web routes
           
        ],

    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to specific routes.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        // Custom route middleware
        'csrf' => \App\Http\Middleware\VerifyCsrfToken::class, // Register custom CSRF middleware
    ];
}
