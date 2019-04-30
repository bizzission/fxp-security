<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Organizational;

use Fxp\Component\Security\Event\SetCurrentOrganizationEvent;
use Fxp\Component\Security\Event\SetCurrentOrganizationUserEvent;
use Fxp\Component\Security\Event\SetOrganizationalOptionalFilterTypeEvent;
use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\OrganizationUserInterface;
use Fxp\Component\Security\Model\UserInterface;
use Fxp\Component\Security\Organizational\OrganizationalContext;
use Fxp\Component\Security\OrganizationalContextEvents;
use Fxp\Component\Security\OrganizationalTypes;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserOrganizationUsers;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 * @coversNothing
 */
final class OrganizationalContextTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TokenInterface
     */
    protected $token;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dispatcher;

    /**
     * @var OrganizationalContext
     */
    protected $context;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $this->context = new OrganizationalContext($this->tokenStorage, $this->dispatcher);

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($this->token)
        ;
    }

    public function testSetDisabledCurrentOrganization(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(OrganizationalContextEvents::SET_CURRENT_ORGANIZATION, new SetCurrentOrganizationEvent(false))
        ;

        $this->context->setCurrentOrganization(false);

        $this->assertNull($this->context->getCurrentOrganization());
    }

    public function testSetCurrentOrganization(): void
    {
        /** @var OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(OrganizationalContextEvents::SET_CURRENT_ORGANIZATION, new SetCurrentOrganizationEvent($org))
        ;

        $this->context->setCurrentOrganization($org);
        $this->assertSame($org, $this->context->getCurrentOrganization());
    }

    public function testGetCurrentOrganizationWithoutSetterAndWithTokenUser(): void
    {
        /** @var OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $user = $this->getMockBuilder(MockUserOrganizationUsers::class)->getMock();

        $this->dispatcher->expects($this->never())
            ->method('dispatch')
        ;

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $user->expects($this->once())
            ->method('getOrganization')
            ->willReturn($org)
        ;

        $this->assertSame($org, $this->context->getCurrentOrganization());
    }

    public function testGetCurrentOrganizationWithoutSetterAndWithTokenUserAndEmptyOrganization(): void
    {
        $user = $this->getMockBuilder(MockUserOrganizationUsers::class)->getMock();

        $this->dispatcher->expects($this->never())
            ->method('dispatch')
        ;

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $user->expects($this->once())
            ->method('getOrganization')
            ->willReturn(null)
        ;

        $this->assertNull($this->context->getCurrentOrganization());
    }

    public function testGetCurrentOrganizationWithoutSetterAndWithTokenUserWithoutOrganizationField(): void
    {
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        $this->dispatcher->expects($this->never())
            ->method('dispatch')
        ;

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $this->assertNull($this->context->getCurrentOrganization());
    }

    public function testGetCurrentOrganizationWithoutSetterAndWithoutTokenUser(): void
    {
        $this->dispatcher->expects($this->never())
            ->method('dispatch')
        ;

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn(null)
        ;

        $this->assertNull($this->context->getCurrentOrganization());
    }

    public function testSetCurrentOrganizationUser(): void
    {
        /** @var OrganizationInterface|\PHPUnit_Framework_MockObject_MockObject $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        /** @var OrganizationUserInterface|\PHPUnit_Framework_MockObject_MockObject $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        $this->dispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(OrganizationalContextEvents::SET_CURRENT_ORGANIZATION, new SetCurrentOrganizationEvent($org))
        ;

        $this->dispatcher->expects($this->at(1))
            ->method('dispatch')
            ->with(OrganizationalContextEvents::SET_CURRENT_ORGANIZATION_USER, new SetCurrentOrganizationUserEvent($orgUser))
        ;

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $orgUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $orgUser->expects($this->once())
            ->method('getOrganization')
            ->willReturn($org)
        ;

        $user->expects($this->atLeast(2))
            ->method('getUsername')
            ->willReturn('user.test')
        ;

        $this->context->setCurrentOrganization($org);
        $this->context->setCurrentOrganizationUser($orgUser);

        $this->assertSame($orgUser, $this->context->getCurrentOrganizationUser());
        $this->assertSame($org, $this->context->getCurrentOrganization());
    }

    public function testIsOrganization(): void
    {
        /** @var OrganizationInterface|\PHPUnit_Framework_MockObject_MockObject $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        /** @var OrganizationUserInterface|\PHPUnit_Framework_MockObject_MockObject $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        $this->dispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(OrganizationalContextEvents::SET_CURRENT_ORGANIZATION, new SetCurrentOrganizationEvent($org))
        ;

        $this->dispatcher->expects($this->at(1))
            ->method('dispatch')
            ->with(OrganizationalContextEvents::SET_CURRENT_ORGANIZATION_USER, new SetCurrentOrganizationUserEvent($orgUser))
        ;

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $orgUser->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $orgUser->expects($this->once())
            ->method('getOrganization')
            ->willReturn($org)
        ;

        $user->expects($this->atLeast(2))
            ->method('getUsername')
            ->willReturn('user.test')
        ;

        $org->expects($this->once())
            ->method('isUserOrganization')
            ->willReturn(false)
        ;

        $this->context->setCurrentOrganization($org);
        $this->context->setCurrentOrganizationUser($orgUser);

        $this->assertTrue($this->context->isOrganization());
    }

    public function testSetOptionalFilterType(): void
    {
        $this->assertSame(OrganizationalTypes::OPTIONAL_FILTER_WITH_ORG, $this->context->getOptionalFilterType());
        $this->assertFalse($this->context->isOptionalFilterType(OrganizationalTypes::OPTIONAL_FILTER_ALL));

        $this->dispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(
                OrganizationalContextEvents::SET_OPTIONAL_FILTER_TYPE,
                new SetOrganizationalOptionalFilterTypeEvent(OrganizationalTypes::OPTIONAL_FILTER_ALL)
            )
        ;

        $this->context->setOptionalFilterType(OrganizationalTypes::OPTIONAL_FILTER_ALL);

        $this->assertSame(OrganizationalTypes::OPTIONAL_FILTER_ALL, $this->context->getOptionalFilterType());
        $this->assertTrue($this->context->isOptionalFilterType(OrganizationalTypes::OPTIONAL_FILTER_ALL));
    }

    public function testValidEmptyTokenForUser(): void
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->expects($this->atLeastOnce())
            ->method('getToken')
            ->willReturn(null)
        ;

        $context = new OrganizationalContext($tokenStorage);
        $context->setCurrentOrganization(null);
        $this->assertNull($context->getCurrentOrganization());
    }

    public function testInvalidTokenForUser(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\RuntimeException::class);
        $this->expectExceptionMessage('The current organization cannot be added in security token because the security token is empty');

        /** @var OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $context = new OrganizationalContext($tokenStorage);
        $context->setCurrentOrganization($org);
    }

    public function testValidEmptyTokenForOrganizationUser(): void
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->expects($this->atLeastOnce())
            ->method('getToken')
            ->willReturn(null)
        ;

        $this->dispatcher->expects($this->never())
            ->method('dispatch')
        ;

        $context = new OrganizationalContext($tokenStorage);
        $context->setCurrentOrganizationUser(null);
        $this->assertNull($context->getCurrentOrganizationUser());
    }

    public function testInvalidTokenForOrganizationUser(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\RuntimeException::class);
        $this->expectExceptionMessage('The current organization user cannot be added in security token because the security token is empty');

        /** @var OrganizationUserInterface $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $this->dispatcher->expects($this->never())
            ->method('dispatch')
        ;

        $context = new OrganizationalContext($tokenStorage);
        $context->setCurrentOrganizationUser($orgUser);
    }
}
