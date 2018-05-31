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

class Basic implements AuthInterface
{
    /** @var string */
    public const AUTH_TYPE = 'basic';

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /**
     * Constructor
     *
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param  array $options
     * @uses   $options['auth'] to set request options for basic auth
     * @return array
     */
    public function addAuthentication(array $options) : array
    {
        $options['auth'] = [$this->username, $this->password];
        return $options;
    }
}
