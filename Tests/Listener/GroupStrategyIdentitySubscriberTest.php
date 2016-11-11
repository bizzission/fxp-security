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

use Sonatra\Component\Security\Event\AddSecurityIdentityEvent;
use Sonatra\Component\Security\Listener\GroupStrategyIdentitySubscriber;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockUserGroupable;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GroupStrategyIdentitySubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testAddGroupSecurityIdentitiesWithException()
    {
        $listener = new GroupStrategyIdentitySubscriber();
        $this->assertCount(1, $listener->getSubscribedEvents());

        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $sids = array();

        $event = new AddSecurityIdentityEvent($token, $sids);

        $listener->addGroupSecurityIdentities($event);
    }

    public function testAddGroupSecurityIdentities()
    {
        $listener = new GroupStrategyIdentitySubscriber();
        $this->assertCount(1, $listener->getSubscribedEvents());

        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $sids = array();

        $user = new MockUserGroupable();

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $event = new AddSecurityIdentityEvent($token, $sids);

        $listener->addGroupSecurityIdentities($event);
    }
}
