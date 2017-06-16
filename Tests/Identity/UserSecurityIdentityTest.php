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
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\UserSecurityIdentity;
use Sonatra\Component\Security\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class UserSecurityIdentityTest extends TestCase
{
    public function testDebugInfo()
    {
        $sid = new UserSecurityIdentity('user.test');

        $this->assertSame('UserSecurityIdentity(user.test)', (string) $sid);
    }

    public function testTypeAndIdentifier()
    {
        $identity = new UserSecurityIdentity('identifier');

        $this->assertSame(UserSecurityIdentity::TYPE, $identity->getType());
        $this->assertSame('identifier', $identity->getIdentifier());
    }

    public function getIdentities()
    {
        $id3 = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();
        $id3->expects($this->any())->method('getType')->willReturn(UserSecurityIdentity::TYPE);
        $id3->expects($this->any())->method('getIdentifier')->willReturn('identifier');

        return array(
            array(new UserSecurityIdentity('identifier'), true),
            array(new UserSecurityIdentity('other'), false),
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
        $identity = new UserSecurityIdentity('identifier');

        $this->assertSame($result, $identity->equals($value));
    }

    public function testFromAccount()
    {
        /* @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects($this->once())
            ->method('getUsername')
            ->willReturn('user.test');

        $sid = UserSecurityIdentity::fromAccount($user);

        $this->assertInstanceOf(UserSecurityIdentity::class, $sid);
        $this->assertSame(UserSecurityIdentity::TYPE, $sid->getType());
        $this->assertSame('user.test', $sid->getIdentifier());
    }

    public function testFormToken()
    {
        /* @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects($this->once())
            ->method('getUsername')
            ->willReturn('user.test');

        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $sid = UserSecurityIdentity::fromToken($token);

        $this->assertInstanceOf(UserSecurityIdentity::class, $sid);
        $this->assertSame(UserSecurityIdentity::TYPE, $sid->getType());
        $this->assertSame('user.test', $sid->getIdentifier());
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\InvalidArgumentException
     * @expectedExceptionMessage The user class must implement "Sonatra\Component\Security\Model\UserInterface"
     */
    public function testFormTokenWithInvalidInterface()
    {
        /* @var AdvancedUserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(AdvancedUserInterface::class)->getMock();

        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        UserSecurityIdentity::fromToken($token);
    }
}
