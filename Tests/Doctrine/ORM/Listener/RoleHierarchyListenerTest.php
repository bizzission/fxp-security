<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Doctrine\ORM\Listener;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Fxp\Component\Cache\Adapter\AdapterInterface;
use Fxp\Component\Security\Doctrine\ORM\Listener\RoleHierarchyListener;
use Fxp\Component\Security\Identity\CacheSecurityIdentityManagerInterface;
use Fxp\Component\Security\Model\GroupInterface;
use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\OrganizationUserInterface;
use Fxp\Component\Security\Model\RoleHierarchicalInterface;
use Fxp\Component\Security\Model\Traits\GroupableInterface;
use Fxp\Component\Security\Model\UserInterface;
use Fxp\Component\Security\Organizational\OrganizationalContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class RoleHierarchyListenerTest extends TestCase
{
    /**
     * @var CacheSecurityIdentityManagerInterface|MockObject
     */
    protected $sidManager;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $cache;

    /**
     * @var MockObject|OrganizationalContextInterface
     */
    protected $context;

    /**
     * @var EntityManagerInterface|MockObject
     */
    protected $em;

    /**
     * @var MockObject|UnitOfWork
     */
    protected $uow;

    /**
     * @var RoleHierarchyListener
     */
    protected $listener;

    protected function setUp(): void
    {
        $this->sidManager = $this->getMockBuilder(CacheSecurityIdentityManagerInterface::class)->getMock();
        $this->cache = $this->getMockBuilder(AdapterInterface::class)->getMock();
        $this->context = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->uow = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
        $this->listener = new RoleHierarchyListener($this->sidManager, $this->cache, $this->context);

        $this->em->expects(static::any())
            ->method('getUnitOfWork')
            ->willReturn($this->uow)
        ;

        static::assertCount(1, $this->listener->getSubscribedEvents());
    }

    public function testOnFLushWithUserObject(): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder(UserInterface::class)->getMock();
        $changeSet = [
            'roles' => [
                [],
                ['ROLE_TEST'],
            ],
        ];

        $args->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->allScheduledCollections([$object]);

        $this->uow->expects(static::once())
            ->method('getEntityChangeSet')
            ->with($object)
            ->willReturn($changeSet)
        ;

        $this->cache->expects(static::once())
            ->method('clearByPrefixes')
            ->with(['user__'])
        ;

        $this->sidManager->expects(static::once())
            ->method('invalidateCache')
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithUserObjectAndNotRequiredField(): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder(UserInterface::class)->getMock();
        $changeSet = [];

        $args->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->allScheduledCollections([$object]);

        $this->uow->expects(static::once())
            ->method('getEntityChangeSet')
            ->with($object)
            ->willReturn($changeSet)
        ;

        $this->cache->expects(static::never())
            ->method('clear')
        ;

        $this->cache->expects(static::never())
            ->method('clearByPrefixes')
        ;

        $this->sidManager->expects(static::never())
            ->method('invalidateCache')
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithRoleHierarchicalObject(): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder(RoleHierarchicalInterface::class)->getMock();
        $changeSet = [
            'roles' => [
                [],
                ['ROLE_TEST'],
            ],
        ];

        $args->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->allScheduledCollections([$object]);

        $this->uow->expects(static::once())
            ->method('getEntityChangeSet')
            ->with($object)
            ->willReturn($changeSet)
        ;

        $this->cache->expects(static::once())
            ->method('clearByPrefixes')
            ->with(['user__'])
        ;

        $this->sidManager->expects(static::once())
            ->method('invalidateCache')
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithGroupObject(): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder(GroupInterface::class)->getMock();
        $changeSet = [
            'roles' => [
                [],
                ['ROLE_TEST'],
            ],
        ];

        $args->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->allScheduledCollections([$object]);

        $this->uow->expects(static::once())
            ->method('getEntityChangeSet')
            ->with($object)
            ->willReturn($changeSet)
        ;

        $this->cache->expects(static::once())
            ->method('clearByPrefixes')
            ->with(['user__'])
        ;

        $this->sidManager->expects(static::once())
            ->method('invalidateCache')
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithOrganizationUserObject(): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $object = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();
        $changeSet = [
            'roles' => [
                [],
                ['ROLE_TEST'],
            ],
        ];

        $object->expects(static::once())
            ->method('getOrganization')
            ->willReturn($org)
        ;

        $org->expects(static::once())
            ->method('getId')
            ->willReturn(42)
        ;

        $args->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->allScheduledCollections([$object]);

        $this->uow->expects(static::once())
            ->method('getEntityChangeSet')
            ->with($object)
            ->willReturn($changeSet)
        ;

        $this->cache->expects(static::once())
            ->method('clearByPrefixes')
            ->with(['42__'])
        ;

        $this->sidManager->expects(static::once())
            ->method('invalidateCache')
        ;

        $this->listener->onFlush($args);
    }

    public function getCollectionInterfaces(): array
    {
        return [
            [RoleHierarchicalInterface::class, 'children'],
            [GroupableInterface::class, 'groups'],
        ];
    }

    /**
     * @dataProvider getCollectionInterfaces
     *
     * @param string $interface The interface name
     * @param string $fieldName The field name
     */
    public function testOnFLushWithPersistentCollection($interface, $fieldName): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder($interface)->getMock();
        /** @var Collection|MockObject $collection */
        $collection = $this->getMockBuilder(Collection::class)->disableOriginalConstructor()->getMock();
        /** @var ClassMetadata|MockObject $meta */
        $meta = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $persistCollection = new PersistentCollection($this->em, $meta, $collection);

        $persistCollection->setOwner($object, [
            'inversedBy' => '',
            'mappedBy' => '',
            'sourceEntity' => \get_class($object),
            'fieldName' => $fieldName,
        ]);

        $args->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->allScheduledCollections([$persistCollection]);

        $this->cache->expects(static::once())
            ->method('clearByPrefixes')
            ->with(['user__'])
        ;

        $this->sidManager->expects(static::once())
            ->method('invalidateCache')
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithOptionalPersistentCollection(): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder(\stdClass::class)->getMock();
        /** @var Collection|MockObject $collection */
        $collection = $this->getMockBuilder(Collection::class)->disableOriginalConstructor()->getMock();
        /** @var ClassMetadata|MockObject $meta */
        $meta = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $persistCollection = new PersistentCollection($this->em, $meta, $collection);

        $persistCollection->setOwner($object, [
            'fieldName' => '',
            'inversedBy' => '',
            'mappedBy' => '',
            'sourceEntity' => \get_class($object),
        ]);

        $args->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->allScheduledCollections([$persistCollection]);

        $this->cache->expects(static::never())
            ->method('clear')
        ;

        $this->cache->expects(static::never())
            ->method('clearByPrefixes')
        ;

        $this->sidManager->expects(static::never())
            ->method('invalidateCache')
        ;

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithoutOrganizationalContext(): void
    {
        /** @var MockObject|OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder(RoleHierarchicalInterface::class)->getMock();
        $changeSet = [
            'roles' => [
                [],
                ['ROLE_TEST'],
            ],
        ];

        $args->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->allScheduledCollections([$object]);

        $this->uow->expects(static::once())
            ->method('getEntityChangeSet')
            ->with($object)
            ->willReturn($changeSet)
        ;

        $this->cache->expects(static::once())
            ->method('clear')
            ->with()
        ;

        $this->sidManager->expects(static::once())
            ->method('invalidateCache')
        ;

        $listener = new RoleHierarchyListener($this->sidManager, $this->cache);
        $listener->onFlush($args);
    }

    /**
     * @param array $objects The objects
     */
    protected function allScheduledCollections(array $objects = []): void
    {
        $this->uow->expects(static::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([])
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn($objects)
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([])
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledCollectionUpdates')
            ->willReturn([])
        ;

        $this->uow->expects(static::once())
            ->method('getScheduledCollectionDeletions')
            ->willReturn([])
        ;
    }
}
