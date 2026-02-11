<?php

namespace VitorDeSousa\AuthentikWebGuard\Exceptions;

class AuthentikCallbackException extends \RuntimeException
{
    /**
     * Authentik Callback Error
     *
     * @param string|null     $message  [description]
     * @param \Throwable|null $previous [description]
     * @param array           $headers  [description]
     * @param int|integer     $code     [description]
     */
    public function __construct(string $error = '')
    {
        $message = '[Authentik Error] ' . $error;

        parent::__construct($message);
    }
}
