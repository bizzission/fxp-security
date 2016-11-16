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
use Sonatra\Component\Security\Listener\DisablePermissionListener;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DisablePermissionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aclManager;

    protected function setUp()
    {
        $this->aclManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
    }

    public function testDisableAcl()
    {
        $listener = new DisablePermissionListener($this->aclManager);
        $this->assertCount(4, $listener->getSubscribedEvents());

        /* @var AbstractEditableSecurityEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockForAbstractClass(AbstractEditableSecurityEvent::class);

        $this->aclManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->aclManager->expects($this->once())
            ->method('disable');

        $listener->disablePermissionManager($event);
    }

    public function testEnableAcl()
    {
        $listener = new DisablePermissionListener($this->aclManager);
        $this->assertCount(4, $listener->getSubscribedEvents());

        $event = new PostReachableRoleEvent(array(), true);

        $this->aclManager->expects($this->once())
            ->method('enable');

        $listener->enablePermissionManager($event);
    }
}
