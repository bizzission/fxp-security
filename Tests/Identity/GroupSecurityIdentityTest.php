<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Identity;

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Security\Identity\GroupSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Model\GroupInterface;
use Sonatra\Component\Security\Model\Traits\GroupableInterface;
use Sonatra\Component\Security\Model\UserInterface;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockGroup;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GroupSecurityIdentityTest extends TestCase
{
    public function testDebugInfo()
    {
        $sid = new GroupSecurityIdentity(MockGroup::class, 'GROUP_TEST');

        $this->assertSame('GroupSecurityIdentity(GROUP_TEST)', (string) $sid);
    }

    public function testTypeAndIdentifier()
    {
        $identity = new GroupSecurityIdentity(MockGroup::class, 'identifier');

        $this->assertSame(MockGroup::class, $identity->getType());
        $this->assertSame('identifier', $identity->getIdentifier());
    }

    public function getIdentities()
    {
        $id3 = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();
        $id3->expects($this->any())->method('getType')->willReturn(MockGroup::class);
        $id3->expects($this->any())->method('getIdentifier')->willReturn('identifier');

        return array(
            array(new GroupSecurityIdentity(MockGroup::class, 'identifier'), true),
            array(new GroupSecurityIdentity(MockGroup::class, 'other'), false),
            array($id3, false),
        );
    }

    /**
     * @dataProvider getIdentities
     *
     * @param mixed $value  The value
     * @param bool  $result The expected result
     */
    public function testEquals($value, $result)
    {
        $identity = new GroupSecurityIdentity(MockGroup::class, 'identifier');

        $this->assertSame($result, $identity->equals($value));
    }

    public function testFromAccount()
    {
        /* @var GroupInterface|\PHPUnit_Framework_MockObject_MockObject $group */
        $group = $this->getMockBuilder(GroupInterface::class)->getMock();
        $group->expects($this->once())
            ->method('getGroup')
            ->willReturn('GROUP_TEST');

        $sid = GroupSecurityIdentity::fromAccount($group);

        $this->assertInstanceOf(GroupSecurityIdentity::class, $sid);
        $this->assertSame(get_class($group), $sid->getType());
        $this->assertSame('GROUP_TEST', $sid->getIdentifier());
    }

    public function testFormToken()
    {
        /* @var GroupInterface|\PHPUnit_Framework_MockObject_MockObject $group */
        $group = $this->getMockBuilder(GroupInterface::class)->getMock();
        $group->expects($this->once())
            ->method('getGroup')
            ->willReturn('GROUP_TEST');

        /* @var GroupableInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(GroupableInterface::class)->getMock();
        $user->expects($this->once())
            ->method('getGroups')
            ->willReturn(array($group));

        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $sids = GroupSecurityIdentity::fromToken($token);

        $this->assertCount(1, $sids);
        $this->assertInstanceOf(GroupSecurityIdentity::class, $sids[0]);
        $this->assertSame(get_class($group), $sids[0]->getType());
        $this->assertSame('GROUP_TEST', $sids[0]->getIdentifier());
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\InvalidArgumentException
     * @expectedExceptionMessage The user class must implement "Sonatra\Component\Security\Model\Traits\GroupableInterface"
     */
    public function testFormTokenWithInvalidInterface()
    {
        /* @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        GroupSecurityIdentity::fromToken($token);
    }
}
