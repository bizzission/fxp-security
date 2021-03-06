<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Token;

use Fxp\Component\Security\Token\ConsoleToken;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ConsoleTokenTest extends TestCase
{
    public function testConsoleToken(): void
    {
        $token = new ConsoleToken('key', 'username', [
            'ROLE_TEST',
        ]);

        static::assertSame('', $token->getCredentials());
        static::assertSame('key', $token->getKey());

        $tokenSerialized = $token->serialize();
        $value = \is_string($tokenSerialized);
        static::assertTrue($value);

        $token2 = new ConsoleToken('', '');
        $token2->unserialize($tokenSerialized);

        static::assertEquals($token, $token2);
    }
}
