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

use Fxp\Component\Security\Identity\CacheSecurityIdentityManager;
use Fxp\Component\Security\Tests\Fixtures\Listener\MockCacheSecurityIdentitySubscriber;
use Fxp\Component\Security\Tests\Fixtures\Token\MockToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class CacheSecurityIdentityManagerTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var MockObject|RoleHierarchyInterface
     */
    protected $roleHierarchy;

    /**
     * @var AuthenticationTrustResolverInterface|MockObject
     */
    protected $authenticationTrustResolver;

    /**
     * @var CacheSecurityIdentityManager
     */
    protected $sidManager;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
        $this->roleHierarchy = $this->getMockBuilder(RoleHierarchy::class)->disableOriginalConstructor()->getMock();
        $this->authenticationTrustResolver = $this->getMockBuilder(AuthenticationTrustResolverInterface::class)->getMock();

        $this->sidManager = new CacheSecurityIdentityManager(
            $this->dispatcher,
            $this->roleHierarchy,
            $this->authenticationTrustResolver
        );
    }

    public function testGetSecurityIdentities(): void
    {
        $token = new MockToken();

        $this->roleHierarchy->expects($this->exactly(2))
            ->method('getReachableRoleNames')
            ->with([])
            ->willReturn([])
        ;

        $this->authenticationTrustResolver->expects($this->exactly(2))
            ->method('isFullFledged')
            ->with($token)
            ->willReturn(false)
        ;

        $this->authenticationTrustResolver->expects($this->exactly(2))
            ->method('isRememberMe')
            ->with($token)
            ->willReturn(false)
        ;

        $this->authenticationTrustResolver->expects($this->exactly(2))
            ->method('isAnonymous')
            ->with($token)
            ->willReturn(true)
        ;

        $this->dispatcher->addSubscriber(new MockCacheSecurityIdentitySubscriber());

        $sids = $this->sidManager->getSecurityIdentities($token);
        $cacheSids = $this->sidManager->getSecurityIdentities($token);

        $this->sidManager->invalidateCache();

        $newSids = $this->sidManager->getSecurityIdentities($token);

        $this->assertSame($sids, $cacheSids);
        $this->assertEquals($sids, $newSids);
    }

    public function testGetSecurityIdentitiesWithoutToken(): void
    {
        $this->roleHierarchy->expects($this->never())
            ->method('getReachableRoles')
        ;

        $sids = $this->sidManager->getSecurityIdentities();

        $this->assertCount(0, $sids);
    }
}
