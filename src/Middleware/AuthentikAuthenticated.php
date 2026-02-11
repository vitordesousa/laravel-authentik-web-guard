<?php

namespace VitorDeSousa\AuthentikWebGuard\Middleware;

use Illuminate\Auth\Middleware\Authenticate;

class AuthentikAuthenticated extends Authenticate
{
    /**
     * Redirect user if it's not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        return route('authentik.login');
    }
}
