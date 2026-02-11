<?php

namespace VitorDeSousa\AuthentikWebGuard\Exceptions;

use Illuminate\Auth\AuthenticationException;

class AuthentikCanException extends AuthenticationException
{
    /**
     * Authentik Callback Error
     *
     * @param string|null     $message  [description]
     * @param \Throwable|null $previous [description]
     * @param array           $headers  [description]
     * @param int|integer     $code     [description]
     */
    public function sss__construct(string $error = '')
    {

    }
}
