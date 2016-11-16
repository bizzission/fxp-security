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

use Sonatra\Component\Security\Identity\CacheSecurityIdentityRetrievalStrategy;
use Sonatra\Component\Security\Tests\Fixtures\Listener\MockEventStrategyIdentity;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CacheSecurityIdentityRetrievalStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var RoleHierarchyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $roleHierarchy;

    /**
     * @var AuthenticationTrustResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authenticationTrustResolver;

    /**
     * @var CacheSecurityIdentityRetrievalStrategy
     */
    protected $sidStrategy;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->roleHierarchy = $this->getMockBuilder(RoleHierarchyInterface::class)->getMock();
        $this->authenticationTrustResolver = $this->getMockBuilder(AuthenticationTrustResolverInterface::class)->getMock();

        $this->sidStrategy = new CacheSecurityIdentityRetrievalStrategy(
            $this->dispatcher,
            $this->roleHierarchy,
            $this->authenticationTrustResolver
        );
    }

    /**
     * @group fxp
     */
    public function testGetSecurityIdentities()
    {
        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn(null);

        $token->expects($this->exactly(2))
            ->method('getRoles')
            ->willReturn(array());

        $this->roleHierarchy->expects($this->exactly(2))
            ->method('getReachableRoles')
            ->with(array())
            ->willReturn(array());

        $this->authenticationTrustResolver->expects($this->exactly(2))
            ->method('isFullFledged')
            ->with($token)
            ->willReturn(false);

        $this->authenticationTrustResolver->expects($this->exactly(2))
            ->method('isRememberMe')
            ->with($token)
            ->willReturn(false);

        $this->authenticationTrustResolver->expects($this->exactly(2))
            ->method('isAnonymous')
            ->with($token)
            ->willReturn(true);

        $this->dispatcher->addSubscriber(new MockEventStrategyIdentity());

        $sids = $this->sidStrategy->getSecurityIdentities($token);
        $cacheSids = $this->sidStrategy->getSecurityIdentities($token);

        $this->sidStrategy->invalidateCache();

        $newSids = $this->sidStrategy->getSecurityIdentities($token);

        $this->assertSame($sids, $cacheSids);
        $this->assertEquals($sids, $newSids);
    }
}
