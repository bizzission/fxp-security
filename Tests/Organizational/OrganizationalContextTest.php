<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Organizational;

use Sonatra\Component\Security\Model\OrganizationInterface;
use Sonatra\Component\Security\Model\OrganizationUserInterface;
use Sonatra\Component\Security\Model\UserInterface;
use Sonatra\Component\Security\Organizational\OrganizationalContext;
use Sonatra\Component\Security\OrganizationalTypes;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockUserOrganizationUsers;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationalContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $token;

    /**
     * @var OrganizationalContext
     */
    protected $context;

    protected function setUp()
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $this->context = new OrganizationalContext($this->tokenStorage);

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($this->token);
    }

    public function testSetDisabledCurrentOrganization()
    {
        $this->context->setCurrentOrganization(false);

        $this->assertNull($this->context->getCurrentOrganization());
    }

    public function testSetCurrentOrganization()
    {
        /* @var OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();

        $this->context->setCurrentOrganization($org);
        $this->assertSame($org, $this->context->getCurrentOrganization());
    }

    public function testGetCurrentOrganizationWithoutSetterAndWithTokenUser()
    {
        /* @var OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $user = $this->getMockBuilder(MockUserOrganizationUsers::class)->getMock();

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $user->expects($this->once())
            ->method('getOrganization')
            ->willReturn($org);

        $this->assertSame($org, $this->context->getCurrentOrganization());
    }

    public function testGetCurrentOrganizationWithoutSetterAndWithTokenUserAndEmptyOrganization()
    {
        $user = $this->getMockBuilder(MockUserOrganizationUsers::class)->getMock();

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $user->expects($this->once())
            ->method('getOrganization')
            ->willReturn(null);

        $this->assertNull($this->context->getCurrentOrganization());
    }

    public function testGetCurrentOrganizationWithoutSetterAndWithTokenUserWithoutOrganizationField()
    {
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertNull($this->context->getCurrentOrganization());
    }

    public function testGetCurrentOrganizationWithoutSetterAndWithoutTokenUser()
    {
        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->assertNull($this->context->getCurrentOrganization());
    }

    public function testSetCurrentOrganizationUser()
    {
        /* @var OrganizationInterface|\PHPUnit_Framework_MockObject_MockObject $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        /* @var OrganizationUserInterface|\PHPUnit_Framework_MockObject_MockObject $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();
        /* @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $orgUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $orgUser->expects($this->once())
            ->method('getOrganization')
            ->willReturn($org);

        $user->expects($this->atLeast(2))
            ->method('getUsername')
            ->willReturn('user.test');

        $this->context->setCurrentOrganization($org);
        $this->context->setCurrentOrganizationUser($orgUser);

        $this->assertSame($orgUser, $this->context->getCurrentOrganizationUser());
        $this->assertSame($org, $this->context->getCurrentOrganization());
    }

    public function testIsOrganization()
    {
        /* @var OrganizationInterface|\PHPUnit_Framework_MockObject_MockObject $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        /* @var OrganizationUserInterface|\PHPUnit_Framework_MockObject_MockObject $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();
        /* @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $orgUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $orgUser->expects($this->once())
            ->method('getOrganization')
            ->willReturn($org);

        $user->expects($this->atLeast(2))
            ->method('getUsername')
            ->willReturn('user.test');

        $org->expects($this->once())
            ->method('isUserOrganization')
            ->willReturn(false);

        $this->context->setCurrentOrganization($org);
        $this->context->setCurrentOrganizationUser($orgUser);

        $this->assertTrue($this->context->isOrganization());
    }

    public function testSetOptionalFilterType()
    {
        $this->assertSame(OrganizationalTypes::OPTIONAL_FILTER_WITH_ORG, $this->context->getOptionalFilterType());
        $this->assertFalse($this->context->isOptionalFilterType(OrganizationalTypes::OPTIONAL_FILTER_ALL));

        $this->context->setOptionalFilterType(OrganizationalTypes::OPTIONAL_FILTER_ALL);

        $this->assertSame(OrganizationalTypes::OPTIONAL_FILTER_ALL, $this->context->getOptionalFilterType());
        $this->assertTrue($this->context->isOptionalFilterType(OrganizationalTypes::OPTIONAL_FILTER_ALL));
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\RuntimeException
     * @expectedExceptionMessage The current organization cannot be added in security token because the security token is empty
     */
    public function testInvalidTokenForUser()
    {
        /* @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject $tokenStorage */
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $context = new OrganizationalContext($tokenStorage);
        $context->setCurrentOrganization(null);
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\RuntimeException
     * @expectedExceptionMessage The current organization user cannot be added in security token because the security token is empty
     */
    public function testInvalidTokenForOrganizationUser()
    {
        /* @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject $tokenStorage */
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $context = new OrganizationalContext($tokenStorage);
        $context->setCurrentOrganizationUser(null);
    }
}
