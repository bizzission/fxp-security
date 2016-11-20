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

use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityRetrievalStrategyInterface;
use Sonatra\Component\Security\Model\OrganizationInterface;
use Sonatra\Component\Security\Organizational\OrganizationalContextInterface;
use Sonatra\Component\Security\Organizational\OrganizationalRole;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationalRoleTest extends \PHPUnit_Framework_TestCase
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
     * @var SecurityIdentityRetrievalStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidStrategy;

    /**
     * @var OrganizationalContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var OrganizationalRole
     */
    protected $orgRole;

    protected function setUp()
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $this->sidStrategy = $this->getMockBuilder(SecurityIdentityRetrievalStrategyInterface::class)->getMock();
        $this->context = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $this->orgRole = new OrganizationalRole($this->context, $this->sidStrategy, $this->tokenStorage);

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($this->token);
    }

    public function testHasRoleWithoutToken()
    {
        /* @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject $tokenStorage */
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $orgRole = new OrganizationalRole($this->context, $this->sidStrategy, $tokenStorage);

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertFalse($orgRole->hasRole('ROLE_ADMIN'));
    }

    public function testHasRoleWithoutSecurityIdentities()
    {
        $this->sidStrategy->expects($this->once())
            ->method('getSecurityIdentities')
            ->with($this->token)
            ->willReturn(array());

        $this->assertFalse($this->orgRole->hasRole('ROLE_ADMIN'));
    }

    public function testHasRoleWithoutOrganizationValidRole()
    {
        $sid = array(
            new RoleSecurityIdentity('ROLE_USER'),
            new RoleSecurityIdentity('ROLE_ADMIN'),
            new RoleSecurityIdentity('ROLE_USER__FOO'),
        );

        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $org->expects($this->atLeast(1))
            ->method('getName')
            ->willReturn('foo');

        $this->sidStrategy->expects($this->once())
            ->method('getSecurityIdentities')
            ->with($this->token)
            ->willReturn($sid);

        $this->context->expects($this->atLeast(1))
            ->method('getCurrentOrganization')
            ->willReturn($org);

        $this->assertFalse($this->orgRole->hasRole('ROLE_ADMIN'));
    }

    public function testHasRole()
    {
        $sid = array(
            new RoleSecurityIdentity('ROLE_USER'),
            new RoleSecurityIdentity('ROLE_ADMIN'),
            new RoleSecurityIdentity('ROLE_USER__FOO'),
            new RoleSecurityIdentity('ROLE_ADMIN__FOO'),
        );

        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $org->expects($this->atLeast(1))
            ->method('getName')
            ->willReturn('foo');

        $this->sidStrategy->expects($this->atLeast(2))
            ->method('getSecurityIdentities')
            ->with($this->token)
            ->willReturn($sid);

        $this->context->expects($this->atLeast(1))
            ->method('getCurrentOrganization')
            ->willReturn($org);

        $this->assertTrue($this->orgRole->hasRole('ROLE_ADMIN'));

        // execution cache
        $this->assertTrue($this->orgRole->hasRole('ROLE_ADMIN'));
    }
}
