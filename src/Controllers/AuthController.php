<?php

namespace VitorDeSousa\AuthentikWebGuard\Controllers;

use Illuminate\Auth\Events\Logout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use VitorDeSousa\AuthentikWebGuard\Exceptions\AuthentikCallbackException;
use VitorDeSousa\AuthentikWebGuard\Facades\AuthentikWeb;

class AuthController extends Controller
{
    /**
     * Redirect to login
     *
     * @return RedirectResponse
     */
    public function login()
    {
        $url = AuthentikWeb::getLoginUrl();
        AuthentikWeb::saveState();

        return redirect($url);
    }

    /**
     * Redirect to logout
     *
     * @return RedirectResponse
     */
    public function logout()
    {
        $url = AuthentikWeb::getLogoutUrl();
        AuthentikWeb::forgetToken();
      
        event(new Logout(Auth::getDefaultDriver(), Auth()->user()));
      
        return redirect($url);
    }



    /**
     * Authentik callback page
     *
     * @throws AuthentikCallbackException
     *
     * @return RedirectResponse
     */
    public function callback(Request $request)
    {
        // Check for errors from Authentik
        if (! empty($request->input('error'))) {
            $error = $request->input('error_description');
            $error = ($error) ?: $request->input('error');

            throw new AuthentikCallbackException($error);
        }

        // Check given state to mitigate CSRF attack
        $state = $request->input('state');
        if (empty($state) || ! AuthentikWeb::validateState($state)) {
            AuthentikWeb::forgetState();

            throw new AuthentikCallbackException('Invalid state');
        }

        // Change code for token
        $code = $request->input('code');
        if (! empty($code)) {
            $token = AuthentikWeb::getAccessToken($code);

            if (Auth::validate($token)) {
                $url = config('authentik-web.redirect_url', '/admin');
                return redirect()->intended($url);
            }
        }

        return redirect(route('authentik.login'));
    }
}
