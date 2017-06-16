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
use Sonatra\Component\Security\Event\CheckPermissionEvent;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CheckPermissionEventTest extends TestCase
{
    public function testEvent()
    {
        $sids = array(
            $this->getMockBuilder(SecurityIdentityInterface::class)->getMock(),
            new RoleSecurityIdentity('ROLE_USER'),
        );
        $permissionMap = array(
            '_global' => array(
                'test' => true,
            ),
        );
        $operation = 'test';
        $subject = MockObject::class;
        $field = 'name';

        $event = new CheckPermissionEvent($sids, $permissionMap, $operation, $subject, $field);

        $this->assertSame($sids, $event->getSecurityIdentities());
        $this->assertSame($permissionMap, $event->getPermissionMap());
        $this->assertSame($operation, $event->getOperation());
        $this->assertSame($subject, $event->getSubject());
        $this->assertSame($field, $event->getField());
        $this->assertNull($event->isGranted());

        $event->setGranted(true);

        $this->assertTrue($event->isGranted());
    }
}
