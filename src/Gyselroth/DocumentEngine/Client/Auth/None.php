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

class None implements AuthInterface
{
    /** @var string */
    public const AUTH_TYPE = 'none';

    /**
     * Add no authentication
     *
     * @param  array $options
     * @return array
     */
    public function addAuthentication(array $options) : array
    {
        return $options;
    }
}
