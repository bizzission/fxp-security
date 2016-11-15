<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Doctrine\ORM\Listener;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Psr\Cache\CacheItemPoolInterface;
use Sonatra\Component\Security\Organizational\OrganizationalContextInterface;
use Sonatra\Component\Security\Doctrine\ORM\Listener\RoleHierarchyListener;
use Sonatra\Component\Security\Identity\SecurityIdentityRetrievalStrategyInterface;
use Sonatra\Component\Security\Model\GroupInterface;
use Sonatra\Component\Security\Model\OrganizationInterface;
use Sonatra\Component\Security\Model\OrganizationUserInterface;
use Sonatra\Component\Security\Model\RoleHierarchicalInterface;
use Sonatra\Component\Security\Model\Traits\GroupableInterface;
use Sonatra\Component\Security\Model\UserInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RoleHierarchyListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SecurityIdentityRetrievalStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $strategy;

    /**
     * @var CacheItemPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cache;

    /**
     * @var OrganizationalContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var UnitOfWork|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uow;

    /**
     * @var RoleHierarchyListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->strategy = $this->getMockBuilder(SecurityIdentityRetrievalStrategyInterface::class)->getMock();
        $this->cache = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $this->context = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->uow = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
        $this->listener = new RoleHierarchyListener($this->strategy, $this->cache, $this->context);

        $this->em->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $this->assertCount(1, $this->listener->getSubscribedEvents());
    }

    public function testOnFLushWithUserObject()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder(UserInterface::class)->getMock();
        $changeSet = array(
            'roles' => array(
                array(),
                array('ROLE_TEST'),
            ),
        );

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->allScheduledCollections(array($object));

        $this->uow->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($object)
            ->willReturn($changeSet);

        $this->cache->expects($this->once())
            ->method('deleteItems')
            ->with(array('user__'));

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithUserObjectAndNotRequiredField()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder(UserInterface::class)->getMock();
        $changeSet = array();

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->allScheduledCollections(array($object));

        $this->uow->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($object)
            ->willReturn($changeSet);

        $this->cache->expects($this->never())
            ->method('clear');

        $this->cache->expects($this->never())
            ->method('deleteItems');

        $this->strategy->expects($this->never())
            ->method('invalidateCache');

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithRoleHierarchicalObject()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder(RoleHierarchicalInterface::class)->getMock();
        $changeSet = array(
            'roles' => array(
                array(),
                array('ROLE_TEST'),
            ),
        );

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->allScheduledCollections(array($object));

        $this->uow->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($object)
            ->willReturn($changeSet);

        $this->cache->expects($this->once())
            ->method('deleteItems')
            ->with(array('user__'));

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithGroupObject()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder(GroupInterface::class)->getMock();
        $changeSet = array(
            'roles' => array(
                array(),
                array('ROLE_TEST'),
            ),
        );

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->allScheduledCollections(array($object));

        $this->uow->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($object)
            ->willReturn($changeSet);

        $this->cache->expects($this->once())
            ->method('deleteItems')
            ->with(array('user__'));

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithOrganizationUserObject()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $object = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();
        $changeSet = array(
            'roles' => array(
                array(),
                array('ROLE_TEST'),
            ),
        );

        $object->expects($this->once())
            ->method('getOrganization')
            ->willReturn($org);

        $org->expects($this->once())
            ->method('getId')
            ->willReturn(42);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->allScheduledCollections(array($object));

        $this->uow->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($object)
            ->willReturn($changeSet);

        $this->cache->expects($this->once())
            ->method('deleteItems')
            ->with(array('42__'));

        $this->listener->onFlush($args);
    }

    public function getCollectionInterfaces()
    {
        return array(
            array(RoleHierarchicalInterface::class, 'children'),
            array(GroupableInterface::class, 'groups'),
        );
    }

    /**
     * @dataProvider getCollectionInterfaces
     *
     * @param string $interface The interface name
     * @param string $fieldName The field name
     */
    public function testOnFLushWithPersistentCollection($interface, $fieldName)
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder($interface)->getMock();
        /* @var Collection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->getMockBuilder(Collection::class)->disableOriginalConstructor()->getMock();
        /* @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $meta */
        $meta = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $persistCollection = new PersistentCollection($this->em, $meta, $collection);

        $persistCollection->setOwner($object, array(
            'inversedBy' => '',
            'mappedBy' => '',
            'sourceEntity' => get_class($object),
            'fieldName' => $fieldName,
        ));

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->allScheduledCollections(array($persistCollection));

        $this->cache->expects($this->once())
            ->method('deleteItems')
            ->with(array('user__'));

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithOptionalPersistentCollection()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder(\stdClass::class)->getMock();
        /* @var Collection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->getMockBuilder(Collection::class)->disableOriginalConstructor()->getMock();
        /* @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $meta */
        $meta = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $persistCollection = new PersistentCollection($this->em, $meta, $collection);

        $persistCollection->setOwner($object, array(
            'inversedBy' => '',
            'mappedBy' => '',
            'sourceEntity' => get_class($object),
        ));

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->allScheduledCollections(array($persistCollection));

        $this->cache->expects($this->never())
            ->method('clear');

        $this->cache->expects($this->never())
            ->method('deleteItems');

        $this->strategy->expects($this->never())
            ->method('invalidateCache');

        $this->listener->onFlush($args);
    }

    public function testOnFLushWithoutOrganizationalContext()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $object = $this->getMockBuilder(RoleHierarchicalInterface::class)->getMock();
        $changeSet = array(
            'roles' => array(
                array(),
                array('ROLE_TEST'),
            ),
        );

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->allScheduledCollections(array($object));

        $this->uow->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($object)
            ->willReturn($changeSet);

        $this->cache->expects($this->once())
            ->method('clear')
            ->with();

        $listener = new RoleHierarchyListener($this->strategy, $this->cache);
        $listener->onFlush($args);
    }

    /**
     * @param array $objects The objects
     */
    protected function allScheduledCollections(array $objects = array())
    {
        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn(array());

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn($objects);

        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn(array());

        $this->uow->expects($this->once())
            ->method('getScheduledCollectionUpdates')
            ->willReturn(array());

        $this->uow->expects($this->once())
            ->method('getScheduledCollectionDeletions')
            ->willReturn(array());
    }
}
