<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Doctrine\ORM\Filter\Listener;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use Fxp\Component\Security\Doctrine\ORM\Filter\Listener\SharingFilterSubscriber;
use Fxp\Component\Security\Doctrine\ORM\Filter\SharingFilter;
use Fxp\Component\Security\Identity\RoleSecurityIdentity;
use Fxp\Component\Security\Identity\SecurityIdentityManagerInterface;
use Fxp\Component\Security\Sharing\SharingIdentityConfigInterface;
use Fxp\Component\Security\Sharing\SharingManagerInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockSharing;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SharingFilterSubscriberTest extends TestCase
{
    /**
     * @var EntityManagerInterface|MockObject
     */
    protected $entityManager;

    /**
     * @var FilterCollection|MockObject
     */
    protected $filterCollection;

    /**
     * @var EventDispatcherInterface|MockObject
     */
    protected $dispatcher;

    /**
     * @var MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var MockObject|SecurityIdentityManagerInterface
     */
    protected $sidManager;

    /**
     * @var MockObject|SharingManagerInterface
     */
    protected $sharingManager;

    /**
     * @var string
     */
    protected $sharingClass;

    /**
     * @var SharingFilter
     */
    protected $filter;

    /**
     * @var SharingFilterSubscriber
     */
    protected $listener;

    protected function setUp(): void
    {
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->filterCollection = $this->getMockBuilder(FilterCollection::class)->disableOriginalConstructor()->getMock();
        $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $this->sharingClass = MockSharing::class;
        $this->filter = new SharingFilter($this->entityManager);
        $this->listener = new SharingFilterSubscriber(
            $this->entityManager,
            $this->dispatcher,
            $this->tokenStorage,
            $this->sidManager,
            $this->sharingManager
        );
        $connection = $this->getMockBuilder(Connection::class)->getMock();
        $connection->expects($this->any())
            ->method('quote')
            ->willReturnCallback(static function ($v) {
                return $v;
            })
        ;

        $this->entityManager->expects($this->any())
            ->method('getFilters')
            ->willReturn($this->filterCollection)
        ;

        $this->entityManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection)
        ;

        $this->sharingManager->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true)
        ;

        $this->assertCount(4, SharingFilterSubscriber::getSubscribedEvents());
    }

    public function testOnSharingManagerChange(): void
    {
        $this->filterCollection->expects($this->once())
            ->method('getEnabledFilters')
            ->willReturn([
                'sharing' => $this->filter,
            ])
        ;

        $this->sharingManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true)
        ;

        $this->assertFalse($this->filter->hasParameter('sharing_manager_enabled'));
        $this->listener->onSharingManagerChange();
        $this->assertTrue($this->filter->hasParameter('sharing_manager_enabled'));
    }

    public function testOnEventWithoutSecurityIdentities(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->filterCollection->expects($this->once())
            ->method('getEnabledFilters')
            ->willReturn([
                'sharing' => $this->filter,
            ])
        ;

        $this->tokenStorage->expects($this->atLeastOnce())
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->sidManager->expects($this->once())
            ->method('getSecurityIdentities')
            ->with($token)
            ->willReturn([])
        ;

        $this->assertFalse($this->filter->hasParameter('has_security_identities'));
        $this->assertFalse($this->filter->hasParameter('map_security_identities'));
        $this->assertFalse($this->filter->hasParameter('user_id'));
        $this->assertFalse($this->filter->hasParameter('sharing_manager_enabled'));

        /** @var KernelEvent $event */
        $event = $this->getMockBuilder(KernelEvent::class)->disableOriginalConstructor()->getMock();
        $this->listener->onEvent($event);

        $this->assertTrue($this->filter->hasParameter('has_security_identities'));
        $this->assertTrue($this->filter->hasParameter('map_security_identities'));
        $this->assertTrue($this->filter->hasParameter('user_id'));
        $this->assertTrue($this->filter->hasParameter('sharing_manager_enabled'));

        $this->assertFalse($this->filter->getParameter('has_security_identities'));
        $this->assertSame([], $this->filter->getParameter('map_security_identities'));
        $this->assertNull($this->filter->getParameter('user_id'));
        $this->assertTrue($this->filter->getParameter('sharing_manager_enabled'));
    }

    public function testOnEvent(): void
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->filterCollection->expects($this->once())
            ->method('getEnabledFilters')
            ->willReturn([
                'sharing' => $this->filter,
            ])
        ;

        $this->tokenStorage->expects($this->atLeastOnce())
            ->method('getToken')
            ->willReturn($token)
        ;

        $this->sidManager->expects($this->once())
            ->method('getSecurityIdentities')
            ->with($token)
            ->willReturn([
                new RoleSecurityIdentity('role', 'ROLE_USER'),
                new RoleSecurityIdentity('role', 'ROLE_ADMIN'),
            ])
        ;

        $this->sharingManager->expects($this->atLeastOnce())
            ->method('getIdentityConfig')
            ->willReturnCallback(function ($v) {
                $config = $this->getMockBuilder(SharingIdentityConfigInterface::class)->getMock();
                $config->expects($this->atLeastOnce())
                    ->method('getType')
                    ->willReturnCallback(static function () use ($v) {
                        return 'role' === $v
                            ? MockRole::class
                            : 'foo';
                    })
                ;

                return $config;
            })
        ;

        $user = new MockUserRoleable();

        $token->expects($this->atLeastOnce())
            ->method('getUser')
            ->willReturn($user)
        ;

        $this->assertFalse($this->filter->hasParameter('has_security_identities'));
        $this->assertFalse($this->filter->hasParameter('map_security_identities'));
        $this->assertFalse($this->filter->hasParameter('user_id'));
        $this->assertFalse($this->filter->hasParameter('sharing_manager_enabled'));

        /** @var KernelEvent $event */
        $event = $this->getMockBuilder(KernelEvent::class)->disableOriginalConstructor()->getMock();
        $this->listener->onEvent($event);

        $this->assertTrue($this->filter->hasParameter('has_security_identities'));
        $this->assertTrue($this->filter->hasParameter('map_security_identities'));
        $this->assertTrue($this->filter->hasParameter('user_id'));
        $this->assertTrue($this->filter->hasParameter('sharing_manager_enabled'));

        $this->assertTrue($this->filter->getParameter('has_security_identities'));
        $this->assertSame([
            MockRole::class => 'ROLE_USER, ROLE_ADMIN',
        ], $this->filter->getParameter('map_security_identities'));
        $this->assertSame(50, $this->filter->getParameter('user_id'));
        $this->assertTrue($this->filter->getParameter('sharing_manager_enabled'));
    }
}
