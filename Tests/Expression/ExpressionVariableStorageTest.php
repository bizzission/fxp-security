<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Expression;

use Sonatra\Component\Security\Event\GetExpressionVariablesEvent;
use Sonatra\Component\Security\Expression\ExpressionVariableStorage;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityRetrievalStrategyInterface;
use Sonatra\Component\Security\Organizational\OrganizationalContextInterface;
use Sonatra\Component\Security\Organizational\OrganizationalRoleInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ExpressionVariableStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuthenticationTrustResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $trustResolver;

    /**
     * @var SecurityIdentityRetrievalStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidStrategy;

    /**
     * @var OrganizationalContextInterface
     */
    protected $context;

    /**
     * @var OrganizationalRoleInterface
     */
    protected $orgRole;

    /**
     * @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $token;

    protected function setUp()
    {
        $this->trustResolver = $this->getMockBuilder(AuthenticationTrustResolverInterface::class)->getMock();
        $this->sidStrategy = $this->getMockBuilder(SecurityIdentityRetrievalStrategyInterface::class)->getMock();
        $this->token = $this->getMockBuilder(TokenInterface::class)->getMock();
    }

    public function testSetVariablesWithSecurityIdentityStrategy()
    {
        $event = new GetExpressionVariablesEvent($this->token);
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
            new RoleSecurityIdentity(AuthenticatedVoter::IS_AUTHENTICATED_FULLY),
        );

        $this->token->expects($this->never())
            ->method('getRoles');

        $this->sidStrategy->expects($this->once())
            ->method('getSecurityIdentities')
            ->with($this->token)
            ->willReturn($sids);

        $variableStorage = new ExpressionVariableStorage(
            $this->trustResolver,
            $this->sidStrategy,
            array(
                'organizational_context' => $this->context,
                'organizational_role' => $this->orgRole,
            )
        );
        $variableStorage->setVariables($event);

        $variables = $event->getVariables();
        $this->assertCount(6, $variables);
        $this->assertArrayHasKey('token', $variables);
        $this->assertArrayHasKey('user', $variables);
        $this->assertArrayHasKey('roles', $variables);
        $this->assertArrayHasKey('trust_resolver', $variables);
        $this->assertArrayHasKey('organizational_context', $variables);
        $this->assertArrayHasKey('organizational_role', $variables);
        $this->assertEquals(array('ROLE_USER'), $variables['roles']);
        $this->assertCount(1, $variableStorage->getSubscribedEvents());
    }

    public function testSetVariablesWithoutSecurityIdentityStrategy()
    {
        $this->token->expects($this->once())
            ->method('getRoles')
            ->willReturn(array(
                new Role('ROLE_USER'),
            ));

        $event = new GetExpressionVariablesEvent($this->token);
        $variableStorage = new ExpressionVariableStorage($this->trustResolver);
        $variableStorage->setVariables($event);

        $variables = $event->getVariables();
        $this->assertCount(4, $variables);
        $this->assertArrayHasKey('token', $variables);
        $this->assertArrayHasKey('user', $variables);
        $this->assertArrayHasKey('roles', $variables);
        $this->assertArrayHasKey('trust_resolver', $variables);
        $this->assertEquals(array('ROLE_USER'), $variables['roles']);
        $this->assertCount(1, $variableStorage->getSubscribedEvents());
    }
}
