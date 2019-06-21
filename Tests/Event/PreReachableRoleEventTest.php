<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Event;

use Fxp\Component\Security\Event\PreReachableRoleEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class PreReachableRoleEventTest extends TestCase
{
    public function testEvent(): void
    {
        $roles = [
            'ROLE_FOO',
            'ROLE_BAR',
        ];

        $event = new PreReachableRoleEvent($roles);
        static::assertSame($roles, $event->getReachableRoleNames());
        static::assertTrue($event->isPermissionEnabled());

        $roles[] = 'ROLE_BAZ';
        $event->setReachableRoleNames($roles);
        $event->setPermissionEnabled(false);
        static::assertSame($roles, $event->getReachableRoleNames());
        static::assertFalse($event->isPermissionEnabled());
    }
}
