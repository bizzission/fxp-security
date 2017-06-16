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
use Sonatra\Component\Security\Event\PreLoadPermissionsEvent;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PreLoadPermissionsEventTest extends TestCase
{
    public function testEvent()
    {
        $sids = array(
            $this->getMockBuilder(SecurityIdentityInterface::class)->getMock(),
            new RoleSecurityIdentity('ROLE_USER'),
        );
        $roles = array(
            'ROLE_USER',
        );

        $event = new PreLoadPermissionsEvent($sids, $roles);

        $this->assertSame($sids, $event->getSecurityIdentities());
        $this->assertSame($roles, $event->getRoles());
    }
}
