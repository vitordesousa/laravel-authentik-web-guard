<?php

return [
    /**
     * Authentik Url
     *
     * Generally https://your-server.com
     */
    'base_url' => env('AUTHENTIK_BASE_URL', ''),

    /**
     * Authentik Client ID
     */
    'client_id' => env('AUTHENTIK_CLIENT_ID', null),

    /**
     * Authentik Client Slug
     */
    'client_slug' => env('AUTHENTIK_CLIENT_SLUG', null),

    /**
     * Authentik Client Secret
     */
    'client_secret' => env('AUTHENTIK_CLIENT_SECRET', null),

    /**
     * The redirect URI to be used for authentication.
     */
    'redirect_uri' => env('AUTHENTIK_REDIRECT_URI', '/auth/callback'),

    /**
     * The authorization endpoint.
     */
    'authorization_endpoint' => env('AUTHENTIK_AUTHORIZATION_ENDPOINT', '/application/o/authorize/'),

    /**
     * The token endpoint.
     */
    'token_endpoint' => env('AUTHENTIK_TOKEN_ENDPOINT', '/application/o/token/'),

    /**
     * The userinfo endpoint.
     */
    'userinfo_endpoint' => env('AUTHENTIK_USERINFO_ENDPOINT', '/application/o/userinfo/'),

    /**
     * The logout endpoint.
     */
    'logout_endpoint' => env('AUTHENTIK_LOGOUT_ENDPOINT', '/application/o/end-session/'),

    /**
     * Page to redirect after callback if there's no "intent"
     *
     * @see VitorDeSousa\AuthentikWebGuard\Controllers\AuthController::callback()
     */
    'redirect_url' => env('AUTHENTIK_REDIRECT_URL', '/admin'),

    /**
     * The routes for authenticate
     *
     * Accept a string as the first parameter of route() or false to disable the route.
     *
     * The routes will receive the name "authentik.{route}" and login/callback are required.
     * So, if you make it false, you shoul register a named 'authentik.login' route and extend
     * the VitorDeSousa\AuthentikWebGuard\Controllers\AuthController controller.
     */
    'routes' => [
        'login' => 'login',
        'logout' => 'logout',
        'register' => 'register',
        'callback' => 'callback',
    ],

    /**
    * GuzzleHttp Client options
    *
    * @link http://docs.guzzlephp.org/en/stable/request-options.html
    */
   'guzzle_options' => [],

    /**
     * Authentik optional scopes
     *
     * array of strings
     */
    'scopes' => ['openid', 'profile', 'email'],
];
