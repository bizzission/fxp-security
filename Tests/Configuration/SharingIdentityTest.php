<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Configuration;

use Fxp\Component\Security\Configuration\SharingIdentity;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SharingIdentityTest extends TestCase
{
    public function testConstructor(): void
    {
        $config = new SharingIdentity([
            'alias' => 'foo',
            'roleable' => true,
            'permissible' => true,
        ]);

        static::assertSame('foo', $config->getAlias());
        static::assertTrue($config->getRoleable());
        static::assertTrue($config->getPermissible());
    }
}
