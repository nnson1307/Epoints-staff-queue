<?php

namespace App\Http;

use App\Http\Middleware\CheckOutletAccept;
use App\Http\Middleware\SwitchDatabaseTenant;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use MyCore\Http\Middleware\DetectLanguage;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        //\App\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's routes middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            //\App\Http\Middleware\EncryptCookies::class,
            //\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            //\Illuminate\View\Middleware\ShareErrorsFromSession::class,
            //\App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            DetectLanguage::class,
            SwitchDatabaseTenant::class,
            //'throttle:60,1',
            'bindings',
        ],

        'auth' => [
            \Tymon\JWTAuth\Http\Middleware\AuthenticateAndRenew::class,
            CheckOutletAccept::class
        ]
    ];

    /**
     * The application's routes middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        //'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        //'auth' => \Tymon\JWTAuth\Http\Middleware\AuthenticateAndRenew::class, // check authen and renew jwt
        //'auth' => \MyCore\Http\Middleware\JWTAuthenticateRedis::class, // check authen and renew jwt
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'trust_ip' => \App\Http\Middleware\TrustIP::class,
        'multi_tenant' => \App\Http\Middleware\MultiTenantDatabase::class
    ];
}
