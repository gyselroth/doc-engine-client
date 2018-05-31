<?php

/**
 * Copyright (c) 2017-2018 gyselroth™  (http://www.gyselroth.net)
 *
 * @package \gyselroth\DocumentEngine
 * @link    http://www.gyselroth.com
 * @author  gyselroth™  (http://www.gyselroth.com)
 * @license Apache-2.0
 */

namespace Gyselroth\DocumentEngine\Client\Auth;

class AuthFactory
{
    /**
     * @param  array $config
     * @uses   $config['authenticationType'] to set up a specific authentication implementation
     * @uses   $config['username'] to set up basic authentication
     * @uses   $config['password'] to set up basic authentication
     * @uses   $config['token'] to set up bearer authentication
     * @return AuthInterface|Basic|Bearer|None
     * @throws AuthException
     */
    public static function GetAuth(array $config) : AuthInterface
    {
        if (empty($config['authenticationType'])) {
            return new None();
        }

        switch ($config['authenticationType']) {
            case Basic::AUTH_TYPE:
                if (empty($config['username'])) {
                    throw new AuthException('username must be given for auth type "' . Basic::AUTH_TYPE . '"');
                }
                if (empty($config['password'])) {
                    throw new AuthException('password must be given for auth type "' . Basic::AUTH_TYPE . '"');
                }
                return new Basic($config['username'], $config['password']);
            case Bearer::AUTH_TYPE:
                if (empty($config['token'])) {
                    throw new AuthException('token must be given for authentication type "' . Bearer::AUTH_TYPE . '"');
                }
                return new Bearer($config['token']);
            case None::AUTH_TYPE:
                return new None();
            default:
                throw new AuthException('unsupported authentication type "' . $config['authenticationType'] . '"');
        }
    }
}
