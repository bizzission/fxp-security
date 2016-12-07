<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Listener;

use Sonatra\Component\Security\Event\AbstractEditableSecurityEvent;
use Sonatra\Component\Security\Event\PostReachableRoleEvent;
use Sonatra\Component\Security\Listener\DisablePermissionSubscriber;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DisablePermissionSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permManager;

    protected function setUp()
    {
        $this->permManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
    }

    public function testDisable()
    {
        $listener = new DisablePermissionSubscriber($this->permManager);
        $this->assertCount(4, $listener->getSubscribedEvents());

        /* @var AbstractEditableSecurityEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockForAbstractClass(AbstractEditableSecurityEvent::class);

        $this->permManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->permManager->expects($this->once())
            ->method('setEnabled')
            ->with(false);

        $listener->disablePermissionManager($event);
    }

    public function testEnable()
    {
        $listener = new DisablePermissionSubscriber($this->permManager);
        $this->assertCount(4, $listener->getSubscribedEvents());

        $event = new PostReachableRoleEvent(array(), true);

        $this->permManager->expects($this->once())
            ->method('setEnabled')
            ->with(true);

        $listener->enablePermissionManager($event);
    }
}
