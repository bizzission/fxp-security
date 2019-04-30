<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Identity;

use Fxp\Component\Security\Identity\GroupSecurityIdentity;
use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Fxp\Component\Security\Model\GroupInterface;
use Fxp\Component\Security\Model\Traits\GroupableInterface;
use Fxp\Component\Security\Model\UserInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockGroup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class GroupSecurityIdentityTest extends TestCase
{
    public function testDebugInfo(): void
    {
        $sid = new GroupSecurityIdentity(MockGroup::class, 'GROUP_TEST');

        $this->assertSame('GroupSecurityIdentity(GROUP_TEST)', (string) $sid);
    }

    public function testTypeAndIdentifier(): void
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

        return [
            [new GroupSecurityIdentity(MockGroup::class, 'identifier'), true],
            [new GroupSecurityIdentity(MockGroup::class, 'other'), false],
            [$id3, false],
        ];
    }

    /**
     * @dataProvider getIdentities
     *
     * @param mixed $value  The value
     * @param bool  $result The expected result
     */
    public function testEquals($value, $result): void
    {
        $identity = new GroupSecurityIdentity(MockGroup::class, 'identifier');

        $this->assertSame($result, $identity->equals($value));
    }

    public function testFromAccount(): void
    {
        /** @var GroupInterface|\PHPUnit_Framework_MockObject_MockObject $group */
        $group = $this->getMockBuilder(GroupInterface::class)->getMock();
        $group->expects($this->once())
            ->method('getName')
            ->willReturn('GROUP_TEST')
        ;

        $sid = GroupSecurityIdentity::fromAccount($group);

        $this->assertInstanceOf(GroupSecurityIdentity::class, $sid);
        $this->assertSame(\get_class($group), $sid->getType());
        $this->assertSame('GROUP_TEST', $sid->getIdentifier());
    }

    public function testFormToken(): void
    {
        /** @var GroupInterface|\PHPUnit_Framework_MockObject_MockObject $group */
        $group = $this->getMockBuilder(GroupInterface::class)->getMock();
        $group->expects($this->once())
            ->method('getName')
            ->willReturn('GROUP_TEST')
        ;

        /** @var GroupableInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(GroupableInterface::class)->getMock();
        $user->expects($this->once())
            ->method('getGroups')
            ->willReturn([$group])
        ;

        /** @var \PHPUnit_Framework_MockObject_MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $sids = GroupSecurityIdentity::fromToken($token);

        $this->assertCount(1, $sids);
        $this->assertInstanceOf(GroupSecurityIdentity::class, $sids[0]);
        $this->assertSame(\get_class($group), $sids[0]->getType());
        $this->assertSame('GROUP_TEST', $sids[0]->getIdentifier());
    }

    public function testFormTokenWithInvalidInterface(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The user class must implement "Fxp\\Component\\Security\\Model\\Traits\\GroupableInterface"');

        /** @var \PHPUnit_Framework_MockObject_MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        GroupSecurityIdentity::fromToken($token);
    }
}
