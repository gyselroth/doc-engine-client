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
use Gyselroth\DocumentEngine\Client\Auth\AuthFactory;
use Gyselroth\DocumentEngine\Client\Auth\Basic;
use Gyselroth\DocumentEngine\Client\Auth\Bearer;
use Gyselroth\DocumentEngine\Client\Auth\None;

class AuthFactoryTest extends TestCase
{
    public function testGetBasicAuth() : void
    {
        // Execute SUT
        $auth = AuthFactory::GetAuth([
            'authenticationType' => 'basic',
            'username'  => 'user',
            'password'  => 'password',
        ]);

        $this->assertInstanceOf(Basic::class, $auth);
    }

    /**
    * @expectedException Gyselroth\DocumentEngine\Client\Auth\AuthException
    * @expectedExceptionMessage username must be given for auth type "basic"
    */
    public function testGetBasicAuthWithoutUsername() : void
    {
        // Execute SUT
        AuthFactory::GetAuth(['authenticationType' => 'basic']);
    }

    /**
    * @expectedException Gyselroth\DocumentEngine\Client\Auth\AuthException
    * @expectedExceptionMessage password must be given for auth type "basic"
    */
    public function testGetBasicAuthWithoutPassword() : void
    {
        // Execute SUT
        AuthFactory::GetAuth([
            'authenticationType' => 'basic',
            'username'           => 'user'
        ]);
    }

    public function testGetBearerAuth() : void
    {
        // Execute SUT
        $auth = AuthFactory::GetAuth([
            'authenticationType' => 'bearer',
            'token'              => 'xyz'
        ]);

        $this->assertInstanceOf(Bearer::class, $auth);
    }

    /**
    * @expectedException Gyselroth\DocumentEngine\Client\Auth\AuthException
    * @expectedExceptionMessage token must be given for authentication type "bearer"
    */
    public function testGetBearerAuthWithoutToken() : void
    {
        // Execute SUT
        AuthFactory::GetAuth(['authenticationType' => 'bearer']);
    }

    public function testGetNoneAuth() : void
    {
        // Execute SUT
        $auth = AuthFactory::GetAuth([
            'authenticationType' => 'none'
        ]);

        $this->assertInstanceOf(None::class, $auth);
    }

    public function testGetNoneAuthAlternative() : void
    {
        // Execute SUT
        $auth = AuthFactory::GetAuth([]);

        $this->assertInstanceOf(None::class, $auth);
    }
}
