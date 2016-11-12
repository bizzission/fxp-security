<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Psr\Cache\CacheItemPoolInterface;
use Sonatra\Component\Security\Acl\Domain\SecurityIdentityRetrievalStrategy;
use Sonatra\Component\Security\Core\Organizational\OrganizationalContextInterface;
use Sonatra\Component\Security\Model\GroupInterface;
use Sonatra\Component\Security\Model\OrganizationInterface;
use Sonatra\Component\Security\Model\OrganizationUserInterface;
use Sonatra\Component\Security\Model\RoleHierarchisableInterface;
use Sonatra\Component\Security\Model\Traits\GroupableInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Invalidate the role hierarchy cache when users, roles or groups is inserted,
 * updated or deleted.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RoleHierarchyListener implements EventSubscriber
{
    /**
     * @var SecurityIdentityRetrievalStrategyInterface
     */
    protected $strategy;

    /**
     * @var CacheItemPoolInterface|null
     */
    protected $cache;

    /**
     * @var OrganizationalContextInterface|null
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param SecurityIdentityRetrievalStrategyInterface $strategy
     * @param CacheItemPoolInterface|null                $cache
     * @param OrganizationalContextInterface|null        $context
     */
    public function __construct(SecurityIdentityRetrievalStrategyInterface $strategy,
                                CacheItemPoolInterface $cache = null,
                                OrganizationalContextInterface $context = null)
    {
        $this->strategy = $strategy;
        $this->cache = $cache;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(Events::onFlush);
    }

    /**
     * On flush action.
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();
        $collection = $this->getAllCollections($uow);
        $invalidates = array();

        // check all scheduled insertions
        foreach ($collection as $object) {
            $invalidate = $this->invalidateCache($uow, $object);

            if (is_string($invalidate)) {
                $invalidates[] = $invalidate;
            }
        }

        $this->flushCache(array_unique($invalidates));
    }

    /**
     * Flush the cache.
     *
     * @param array $invalidates The prefix must be invalidated
     */
    protected function flushCache(array $invalidates)
    {
        if (count($invalidates) > 0) {
            if (null !== $this->cache && null === $this->context) {
                $this->cache->clear();
            } elseif (null !== $this->cache) {
                $this->cache->deleteItems($invalidates);
            }

            if ($this->strategy instanceof SecurityIdentityRetrievalStrategy) {
                $this->strategy->invalidateCache();
            }
        }
    }

    /**
     * Get the merged collection of all scheduled collections.
     *
     * @param UnitOfWork $uow The unit of work
     *
     * @return array
     */
    protected function getAllCollections(UnitOfWork $uow)
    {
        return array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates(),
            $uow->getScheduledEntityDeletions(),
            $uow->getScheduledCollectionUpdates(),
            $uow->getScheduledCollectionDeletions()
        );
    }

    /**
     * Check if the role hierarchy cache must be invalidated.
     *
     * @param UnitOfWork $uow
     * @param object     $object
     *
     * @return string|false
     */
    protected function invalidateCache($uow, $object)
    {
        if ($this->isCacheableObject($object)) {
            $fields = array_keys($uow->getEntityChangeSet($object));
            $checkFields = array('roles');

            if ($object instanceof RoleHierarchisableInterface || $object instanceof OrganizationUserInterface) {
                $checkFields = array_merge($checkFields, array('name'));
            }

            foreach ($fields as $field) {
                if (in_array($field, $checkFields)) {
                    return $this->getPrefix($object);
                }
            }
        } elseif ($object instanceof PersistentCollection
                && $this->isRequireAssociation($object->getMapping())) {
            return $this->getPrefix($object->getOwner());
        }

        return false;
    }

    /**
     * Check if the object is cacheable or not.
     *
     * @param object $object The object
     *
     * @return bool
     */
    protected function isCacheableObject($object)
    {
        return $object instanceof UserInterface
            || $object instanceof RoleHierarchisableInterface
            || $object instanceof GroupInterface
            || $object instanceof OrganizationUserInterface;
    }

    /**
     * Check if the association must be flush the cache.
     *
     * @param array $mapping The mapping
     *
     * @return bool
     */
    protected function isRequireAssociation(array $mapping)
    {
        $ref = new \ReflectionClass($mapping['sourceEntity']);

        if (in_array(RoleHierarchisableInterface::class, $ref->getInterfaceNames())
                && 'children' === $mapping['fieldName']) {
            return true;
        } elseif (in_array(GroupableInterface::class, $ref->getInterfaceNames())
                && 'groups' === $mapping['fieldName']) {
            return true;
        }

        return false;
    }

    /**
     * Get the cache prefix key.
     *
     * @param object $object
     *
     * @return string
     */
    protected function getPrefix($object)
    {
        $id = 'user';

        if (method_exists($object, 'getOrganization')) {
            $org = $object->getOrganization();

            if ($org instanceof OrganizationInterface) {
                $id = (string) $org->getId();
            }
        }

        return $id.'__';
    }
}
