<?php

/**
 * Copyright (c) 2017-2018 gyselroth™  (http://www.gyselroth.net)
 *
 * @package \gyselroth\DocumentEngine
 * @link    http://www.gyselroth.com
 * @author  gyselroth™  (http://www.gyselroth.com)
 * @license Apache-2.0
 */

namespace Gyselroth\DocumentEngine\Tests\Unit\Client\Auth;;

use PHPUnit\Framework\TestCase;
use Gyselroth\DocumentEngine\Client\Auth\Bearer;

class BearerTest extends TestCase
{
    public function testAddAuthentication() : void
    {
        // Setup
        $token = 'xyz';
        $auth  = new Bearer($token);

        // Fixture
        $options = ['canary' => 'foo'];

        // Execute SUT
        $authOptions = $auth->addAuthentication($options);

        // Assertion
        $options['headers'] = ['Authorization' => 'Bearer ' . $token];
        $this->assertEquals($options, $authOptions);
    }
}
