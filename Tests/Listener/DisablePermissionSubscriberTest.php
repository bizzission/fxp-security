<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Listener;

use Fxp\Component\Security\Event\AbstractEditableSecurityEvent;
use Fxp\Component\Security\Event\PostReachableRoleEvent;
use Fxp\Component\Security\Listener\DisablePermissionSubscriber;
use Fxp\Component\Security\Permission\PermissionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class DisablePermissionSubscriberTest extends TestCase
{
    /**
     * @var MockObject|PermissionManagerInterface
     */
    protected $permManager;

    protected function setUp(): void
    {
        $this->permManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
    }

    /**
     * @throws
     */
    public function testDisable(): void
    {
        $listener = new DisablePermissionSubscriber($this->permManager);
        static::assertCount(4, DisablePermissionSubscriber::getSubscribedEvents());

        /** @var AbstractEditableSecurityEvent|MockObject $event */
        $event = $this->getMockForAbstractClass(AbstractEditableSecurityEvent::class);

        $this->permManager->expects(static::once())
            ->method('isEnabled')
            ->willReturn(true)
        ;

        $this->permManager->expects(static::once())
            ->method('setEnabled')
            ->with(false)
        ;

        $listener->disablePermissionManager($event);
    }

    public function testEnable(): void
    {
        $listener = new DisablePermissionSubscriber($this->permManager);
        static::assertCount(4, DisablePermissionSubscriber::getSubscribedEvents());

        $event = new PostReachableRoleEvent([], true);

        $this->permManager->expects(static::once())
            ->method('setEnabled')
            ->with(true)
        ;

        $listener->enablePermissionManager($event);
    }
}
