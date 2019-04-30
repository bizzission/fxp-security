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

use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Fxp\Component\Security\Identity\UserSecurityIdentity;
use Fxp\Component\Security\Model\UserInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 * @coversNothing
 */
final class UserSecurityIdentityTest extends TestCase
{
    public function testDebugInfo(): void
    {
        $sid = new UserSecurityIdentity(MockUserRoleable::class, 'user.test');

        $this->assertSame('UserSecurityIdentity(user.test)', (string) $sid);
    }

    public function testTypeAndIdentifier(): void
    {
        $identity = new UserSecurityIdentity(MockUserRoleable::class, 'identifier');

        $this->assertSame(MockUserRoleable::class, $identity->getType());
        $this->assertSame('identifier', $identity->getIdentifier());
    }

    public function getIdentities()
    {
        $id3 = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();
        $id3->expects($this->any())->method('getType')->willReturn(MockUserRoleable::class);
        $id3->expects($this->any())->method('getIdentifier')->willReturn('identifier');

        return [
            [new UserSecurityIdentity(MockUserRoleable::class, 'identifier'), true],
            [new UserSecurityIdentity(MockUserRoleable::class, 'other'), false],
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
        $identity = new UserSecurityIdentity(MockUserRoleable::class, 'identifier');

        $this->assertSame($result, $identity->equals($value));
    }

    public function testFromAccount(): void
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects($this->once())
            ->method('getUsername')
            ->willReturn('user.test')
        ;

        $sid = UserSecurityIdentity::fromAccount($user);

        $this->assertInstanceOf(UserSecurityIdentity::class, $sid);
        $this->assertSame(\get_class($user), $sid->getType());
        $this->assertSame('user.test', $sid->getIdentifier());
    }

    public function testFormToken(): void
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects($this->once())
            ->method('getUsername')
            ->willReturn('user.test')
        ;

        /** @var \PHPUnit_Framework_MockObject_MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $sid = UserSecurityIdentity::fromToken($token);

        $this->assertInstanceOf(UserSecurityIdentity::class, $sid);
        $this->assertSame(\get_class($user), $sid->getType());
        $this->assertSame('user.test', $sid->getIdentifier());
    }

    public function testFormTokenWithInvalidInterface(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The user class must implement "Fxp\\Component\\Security\\Model\\UserInterface"');

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Security\Core\User\UserInterface $user */
        $user = $this->getMockBuilder(\Symfony\Component\Security\Core\User\UserInterface::class)->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        UserSecurityIdentity::fromToken($token);
    }
}
