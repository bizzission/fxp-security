<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Token;

use Sonatra\Component\Security\Token\ConsoleToken;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ConsoleTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testConsoleToken()
    {
        $token = new ConsoleToken('key', 'username', array(
            'ROLE_TEST',
        ));

        $this->assertSame('', $token->getCredentials());
        $this->assertSame('key', $token->getKey());

        $tokenSerialized = $token->serialize();
        $this->assertTrue(is_string($tokenSerialized));

        $token2 = new ConsoleToken('', '');
        $token2->unserialize($tokenSerialized);

        $this->assertEquals($token, $token2);
    }
}
