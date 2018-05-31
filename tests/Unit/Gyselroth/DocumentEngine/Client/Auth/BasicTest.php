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
use Gyselroth\DocumentEngine\Client\Auth\Basic;

class BasicTest extends TestCase
{
    public function testAddAuthentication() : void
    {
        // Setup
        $username = 'user';
        $password = 'password';
        $auth     = new Basic($username, $password);

        // Fixture
        $options = ['canary' => 'foo'];

        // Execute SUT
        $authOptions = $auth->addAuthentication($options);

        // Assertion
        $options['auth'] = [$username, $password];
        $this->assertEquals($options, $authOptions);
    }
}
