<?php

namespace VitorDeSousa\AuthentikWebGuard\Auth\Guard;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use VitorDeSousa\AuthentikWebGuard\Auth\AuthentikAccessToken;
use VitorDeSousa\AuthentikWebGuard\Exceptions\AuthentikCallbackException;
use VitorDeSousa\AuthentikWebGuard\Models\AuthentikUser;
use VitorDeSousa\AuthentikWebGuard\Facades\AuthentikWeb;
use Illuminate\Contracts\Auth\UserProvider;

class AuthentikWebGuard implements Guard
{
    /**
     * @var null|Authenticatable|AuthentikUser
     */
    protected $user;

    /**
     * @var UserProvider
     */
    protected $provider;

    /**
     * @var Request
     */
    protected $request;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return (bool) $this->user();
    }

    /**
     * @return bool
     */
    public function hasUser()
    {
        return (bool) $this->user;
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (empty($this->user)) {
            $this->authenticate();
        }

        return $this->user;
    }

    /**
     * Set the current user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return void
     */
    public function setUser(?Authenticatable $user)
    {
        $this->user = $user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        $user = $this->user();
        return $user->id ?? null;
    }

    /**
    * Disable viaRemember methode used by some bundles (like filament)
    *
    * @return bool
    */
    public function viaRemember()
    {
        return false;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     *
     * @throws BadMethodCallException
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials['access_token']) || empty($credentials['id_token'])) {
            return false;
        }

        /**
         * Store the section
         */
        $credentials['refresh_token'] = $credentials['refresh_token'] ?? '';
        AuthentikWeb::saveToken($credentials);

        return $this->authenticate();
    }

    /**
     * Try to authenticate the user
     *
     * @throws AuthentikCallbackException
     * @return bool
     */
    public function authenticate()
    {
        // Get Credentials
        $credentials = AuthentikWeb::retrieveToken();
        if (empty($credentials)) {
            return false;
        }

        $user = AuthentikWeb::getUserProfile($credentials);
        if (empty($user)) {
            AuthentikWeb::forgetToken();

            if (Config::get('app.debug', false)) {
                throw new AuthentikCallbackException('User cannot be authenticated.');
            }

            return false;
        }

        // Provide User
        $user = $this->provider->retrieveByCredentials($user);
        $this->setUser($user);

        event(new Authenticated(Auth::getDefaultDriver(), Auth()->user()));

        return true;
    }

    /**
     * Check user is authenticated and return his resource roles
     *
     * @param string $resource Default is empty: point to client_id
     *
     * @return bool|array
    */
    public function roles($resource = '')
    {
        if (empty($resource)) {
            $resource = Config::get('authentik-web.client_id');
        }

        if (! $this->check()) {
            return false;
        }

        $token = AuthentikWeb::retrieveToken();

        if (empty($token) || empty($token['access_token'])) {
            return false;
        }

        $token = new AuthentikAccessToken($token);
        $token = $token->parseAccessToken();

        $resourceRoles = $token['resource_access'] ?? [];
        $resourceRoles = $resourceRoles[ $resource ] ?? [];
        $resourceRoles = $resourceRoles['roles'] ?? [];

        $realmRoles = $token['realm_access'] ?? [];
        $realmRoles = $realmRoles['roles'] ?? [];

        return array_merge($resourceRoles, $realmRoles);
    }

    /**
     * Check user has a role
     *
     * @param array|string $roles
     * @param string $resource Default is empty: point to client_id
     *
     * @return bool
     */
    public function hasRole($roles, $resource = '')
    {
        return empty(array_diff((array) $roles, $this->roles($resource)));
    }
}
