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

interface AuthInterface
{
    /**
     * @param  array $options
     * @uses   $options[] to set request options according to the authentication type
     * @return array
     */
    public function addAuthentication(array $options) : array;
}
