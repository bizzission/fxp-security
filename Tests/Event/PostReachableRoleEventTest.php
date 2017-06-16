<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Event;

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Security\Event\PostReachableRoleEvent;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PostReachableRoleEventTest extends TestCase
{
    public function testEvent()
    {
        $roles = array(
            new Role('ROLE_FOO'),
            new Role('ROLE_BAR'),
        );

        $event = new PostReachableRoleEvent($roles);
        $this->assertSame($roles, $event->getReachableRoles());
        $this->assertTrue($event->isPermissionEnabled());

        $roles[] = new Role('ROLE_BAZ');
        $event->setReachableRoles($roles);
        $this->assertSame($roles, $event->getReachableRoles());
    }
}
