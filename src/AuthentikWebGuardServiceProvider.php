<?php

namespace VitorDeSousa\AuthentikWebGuard;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use VitorDeSousa\AuthentikWebGuard\Auth\Guard\AuthentikWebGuard;
use VitorDeSousa\AuthentikWebGuard\Auth\AuthentikWebUserProvider;
use VitorDeSousa\AuthentikWebGuard\Middleware\AuthentikAuthenticated;
use VitorDeSousa\AuthentikWebGuard\Middleware\AuthentikCan;
use VitorDeSousa\AuthentikWebGuard\Middleware\AuthentikCanOne;
use VitorDeSousa\AuthentikWebGuard\Services\AuthentikService;

class AuthentikWebGuardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Configuration
        $config = __DIR__ . '/../config/authentik-web.php';

        $this->publishes([$config => config_path('authentik-web.php')], 'config');
        $this->mergeConfigFrom($config, 'authentik-web');

        // User Provider
        Auth::provider('authentik-users', function($app, array $config) {
            return new AuthentikWebUserProvider($config['model']);
        });

        // Gate
        Gate::define('authentik-web', function ($user, $roles, $resource = '') {
            return $user->hasRole($roles, $resource) ?: null;
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Authentik Web Guard
        Auth::extend('authentik-web', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
            return new AuthentikWebGuard($provider, $app->request);
        });

        // Facades
        $this->app->bind('authentik-web', function($app) {
            return $app->make(AuthentikService::class);
        });

        // Routes
        $this->registerRoutes();

        // Middleware Group
        $this->app['router']->middlewareGroup('authentik-web', [
            StartSession::class,
            AuthentikAuthenticated::class,
        ]);

        // Add Middleware "authentik-web-can"
        $this->app['router']->aliasMiddleware('authentik-web-can', AuthentikCan::class);

        // Add Middleware "authentik-web-can-one
        $this->app['router']->aliasMiddleware('authentik-web-can-one', AuthentikCanOne::class);

        // Bind for client data
        $this->app->when(AuthentikService::class)->needs(ClientInterface::class)->give(function() {
            return new Client(Config::get('authentik-web.guzzle_options', []));
        });
    }

    /**
     * Register the authentication routes for authentik.
     *
     * @return void
     */
    private function registerRoutes()
    {
        $defaults = [
            'login' => 'login',
            'logout' => 'logout',
            'callback' => 'callback',
        ];

        $routes = Config::get('authentik-web.routes', []);
        $routes = array_merge($defaults, $routes);

        // Register Routes
        $router = $this->app->make('router');

        if (! empty($routes['login'])) {
            $router->middleware('web')->get($routes['login'], 'VitorDeSousa\AuthentikWebGuard\Controllers\AuthController@login')->name('authentik.login');
        }

        if (! empty($routes['logout'])) {
            $router->middleware('web')->get($routes['logout'], 'VitorDeSousa\AuthentikWebGuard\Controllers\AuthController@logout')->name('authentik.logout');
        }

        if (! empty($routes['callback'])) {
            $router->middleware('web')->get($routes['callback'], 'VitorDeSousa\AuthentikWebGuard\Controllers\AuthController@callback')->name('authentik.callback');
        }
    }
}
