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

use Sonatra\Component\Security\Event\AddSecurityIdentityEvent;
use Sonatra\Component\Security\Event\PostSecurityIdentityEvent;
use Sonatra\Component\Security\Event\PreSecurityIdentityEvent;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SecurityIdentityRetrievalStrategy;
use Sonatra\Component\Security\IdentityRetrievalEvents;
use Sonatra\Component\Security\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SecurityIdentityRetrievalStrategyTest extends \PHPUnit_Framework_TestCase
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
     * @var SecurityIdentityRetrievalStrategy
     */
    protected $sidStrategy;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->roleHierarchy = $this->getMockBuilder(RoleHierarchyInterface::class)->getMock();
        $this->authenticationTrustResolver = $this->getMockBuilder(AuthenticationTrustResolverInterface::class)->getMock();

        $this->sidStrategy = new SecurityIdentityRetrievalStrategy(
            $this->dispatcher,
            $this->roleHierarchy,
            $this->authenticationTrustResolver
        );
    }

    public function getAuthenticationTrustResolverStatus()
    {
        return array(
            array('isFullFledged', 6),
            array('isRememberMe', 5),
            array('isAnonymous', 4),
        );
    }

    /**
     * @dataProvider getAuthenticationTrustResolverStatus
     *
     * @param string $trustMethod  The method for the authentication trust resolver
     * @param int    $sidFinalSize The final size of security identities list
     */
    public function testGetSecurityIdentities($trustMethod, $sidFinalSize)
    {
        $preEventAction = false;
        $addEventAction = false;
        $postEventAction = false;

        $customSid = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();

        $this->dispatcher->addListener(IdentityRetrievalEvents::PRE, function (PreSecurityIdentityEvent $event) use (&$objects, &$preEventAction) {
            $preEventAction = true;
            $this->assertCount(0, $event->getSecurityIdentities());
        });

        $this->dispatcher->addListener(IdentityRetrievalEvents::ADD, function (AddSecurityIdentityEvent $event) use (&$objects, &$addEventAction, $customSid) {
            $addEventAction = true;
            $this->assertCount(2, $event->getSecurityIdentities());

            $sids = $event->getSecurityIdentities();
            $sids[] = $customSid;
            $event->setSecurityIdentities($sids);

            $this->assertCount(3, $event->getSecurityIdentities());
        });

        $this->dispatcher->addListener(IdentityRetrievalEvents::POST, function (PostSecurityIdentityEvent $event) use (&$objects, &$postEventAction, $sidFinalSize) {
            $postEventAction = true;
            $this->assertCount($sidFinalSize, $event->getSecurityIdentities());
        });

        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects($this->once())
            ->method('getUsername')
            ->willReturn('user.test');

        $tokenRoles = array(
            new Role('ROLE_TOKEN'),
        );

        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $token->expects($this->once())
            ->method('getRoles')
            ->willReturn($tokenRoles);

        $this->roleHierarchy->expects($this->once())
            ->method('getReachableRoles')
            ->with($tokenRoles)
            ->willReturn($tokenRoles);

        if (in_array($trustMethod, array('isRememberMe', 'isAnonymous'))) {
            $this->authenticationTrustResolver->expects($this->once())
                ->method('isFullFledged')
                ->with($token)
                ->willReturn(false);
        }

        if (in_array($trustMethod, array('isAnonymous'))) {
            $this->authenticationTrustResolver->expects($this->once())
                ->method('isRememberMe')
                ->with($token)
                ->willReturn(false);
        }

        $this->authenticationTrustResolver->expects($this->once())
            ->method($trustMethod)
            ->with($token)
            ->willReturn(true);

        $this->sidStrategy->getSecurityIdentities($token);

        $this->assertTrue($preEventAction);
        $this->assertTrue($addEventAction);
        $this->assertTrue($postEventAction);
    }
}
