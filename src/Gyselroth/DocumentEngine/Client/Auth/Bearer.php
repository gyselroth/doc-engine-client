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

class Bearer implements AuthInterface
{
    /** @var string */
    public const AUTH_TYPE = 'bearer';

    /** @var string */
    private $token;

    /**
     * Constructor
     *
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * @param  array $options
     * @uses   $options['headers'] to set request options for bearer auth
     * @return array
     */
    public function addAuthentication(array $options) : array
    {
        $options['headers']['Authorization'] = 'Bearer ' . $this->token;
        return $options;
    }
}
