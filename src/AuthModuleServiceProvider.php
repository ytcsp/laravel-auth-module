<?php

namespace Strichpunkt\LaravelAuthModule;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Strichpunkt\LaravelAuthModule\Http\Middleware\AuthModule;
use Strichpunkt\LaravelAuthModule\Http\Middleware\JwtMiddleware;
use Strichpunkt\LaravelAuthModule\Services\AuthService;
use Strichpunkt\LaravelAuthModule\Services\PasswordResetService;

class AuthModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/auth-module.php', 'auth-module');
        
        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService();
        });

        $this->app->singleton(PasswordResetService::class, function ($app) {
            return new PasswordResetService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'auth-module');

        $this->publishes([
            __DIR__.'/../config/auth-module.php' => config_path('auth-module.php'),
        ], 'auth-module-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'auth-module-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/auth-module'),
        ], 'auth-module-views');

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('auth.module', AuthModule::class);
        $router->aliasMiddleware('jwt.auth', JwtMiddleware::class);
        
        // Register middleware groups
        $router->middlewareGroup('auth-api', [
            'throttle:60,1',
            'bindings',
        ]);
    }
} 