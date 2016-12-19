<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Role;

use Doctrine\Common\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Sonatra\Component\Security\Event\PostReachableRoleEvent;
use Sonatra\Component\Security\Event\PreReachableRoleEvent;
use Sonatra\Component\Security\Model\RoleHierarchicalInterface;
use Sonatra\Component\Security\Model\RoleInterface;
use Sonatra\Component\Security\ReachableRoleEvents;
use Symfony\Component\Security\Core\Role\RoleHierarchy as BaseRoleHierarchy;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sonatra\Component\Security\Exception\SecurityException;

/**
 * RoleHierarchy defines a role hierarchy.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RoleHierarchy extends BaseRoleHierarchy
{
    /**
     * @var ManagerRegistryInterface
     */
    private $registry;

    /**
     * @var string
     */
    private $roleClassname;

    /**
     * @var array
     */
    private $cacheExec;

    /**
     * @var CacheItemPoolInterface|null
     */
    private $cache;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Constructor.
     *
     * @param array                       $hierarchy     An array defining the hierarchy
     * @param ManagerRegistryInterface    $registry      The doctrine registry
     * @param string                      $roleClassname The classname of role
     * @param CacheItemPoolInterface|null $cache         The cache
     */
    public function __construct(array $hierarchy,
                                ManagerRegistryInterface $registry,
                                $roleClassname,
                                CacheItemPoolInterface $cache = null)
    {
        parent::__construct($hierarchy);

        $this->registry = $registry;
        $this->roleClassname = $roleClassname;
        $this->cacheExec = array();
        $this->cache = $cache;
    }

    /**
     * Set event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    /**
     * Returns an array of all roles reachable by the given ones.
     *
     * @param Role[] $roles An array of role instances
     *
     * @return Role[] An array of role instances
     *
     * @throws SecurityException When the role class is not an instance of '\Symfony\Component\Security\Core\Role\Role'
     */
    public function getReachableRoles(array $roles)
    {
        if (0 === count($roles)) {
            return $roles;
        }

        $item = null;
        $roles = $this->formatRoles($roles);
        $id = $this->getUniqueId(array_keys($roles));

        if (null !== ($reachableRoles = $this->getCachedReachableRoles($id, $item))) {
            return $reachableRoles;
        }

        // build hierarchy
        /* @var Role[] $reachableRoles */
        $reachableRoles = parent::getReachableRoles(array_values($roles));
        $isPermEnabled = true;

        if (null !== $this->eventDispatcher) {
            $event = new PreReachableRoleEvent($reachableRoles);
            $this->eventDispatcher->dispatch(ReachableRoleEvents::PRE, $event);
            $reachableRoles = $event->getReachableRoles();
            $isPermEnabled = $event->isPermissionEnabled();
        }

        return $this->getAllRoles($reachableRoles, $roles, $id, $item, $isPermEnabled);
    }

    /**
     * Get the unique id.
     *
     * @param array $roleNames The role names
     *
     * @return string
     */
    protected function getUniqueId(array $roleNames)
    {
        return sha1(implode('|', $roleNames));
    }

    /**
     * Get the reachable roles in cache if available.
     *
     * @param string $id   The cache id
     * @param null   $item The cache item variable passed by reference
     *
     * @return Role[]|null
     */
    private function getCachedReachableRoles($id, &$item)
    {
        $roles = null;

        // find the hierarchy in execution cache
        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        // find the hierarchy in cache
        if (null !== $this->cache) {
            $item = $this->cache->getItem($id);
            $reachableRoles = $item->get();

            if ($item->isHit() && null !== $reachableRoles) {
                $roles = $reachableRoles;
            }
        }

        return $roles;
    }

    /**
     * Get all roles.
     *
     * @param Role[]                  $reachableRoles The reachable roles
     * @param Role[]                  $roles          The roles
     * @param string                  $id             The cache item id
     * @param CacheItemInterface|null $item           The cache item
     * @param bool                    $isPermEnabled  Check if the permission manager is enabled
     *
     * @return Role[]
     */
    private function getAllRoles(array $reachableRoles, array $roles, $id, $item, $isPermEnabled)
    {
        $reachableRoles = $this->findRecords($reachableRoles, array_keys($roles));
        $reachableRoles = $this->getCleanedRoles($reachableRoles);

        // insert in cache
        if (null !== $this->cache && $item instanceof CacheItemInterface) {
            $item->set($reachableRoles);
            $this->cache->save($item);
        }

        $this->cacheExec[$id] = $reachableRoles;

        if (null !== $this->eventDispatcher) {
            $event = new PostReachableRoleEvent($reachableRoles, $isPermEnabled);
            $this->eventDispatcher->dispatch(ReachableRoleEvents::POST, $event);
            $reachableRoles = $event->getReachableRoles();
        }

        return $reachableRoles;
    }

    /**
     * Format the roles.
     *
     * @param Role[] $roles The roles
     *
     * @return Role[]
     *
     * @throws SecurityException When the role is not a string or an instance of RoleInterface
     */
    private function formatRoles(array $roles)
    {
        $nRoles = array();

        foreach ($roles as $role) {
            if (!is_string($role) && !($role instanceof Role)) {
                throw new SecurityException(sprintf('The Role class must be an instance of "%s"', Role::class));
            }

            $name = ($role instanceof Role) ? $role->getRole() : $role;
            $nRoles[$name] = ($role instanceof Role) ? $role : new Role($name);
        }

        return $nRoles;
    }

    /**
     * Find the roles in database.
     *
     * @param Role[]   $reachableRoles The reachable roles
     * @param string[] $roleNames      The role names
     *
     * @return Role[]
     */
    private function findRecords(array $reachableRoles, array $roleNames)
    {
        $recordRoles = array();
        $om = $this->registry->getManagerForClass($this->roleClassname);
        $repo = $om->getRepository($this->roleClassname);

        $filters = $this->disableFilters($om);

        if (count($roleNames) > 0) {
            $recordRoles = $repo->findBy(array('name' => $roleNames));
        }

        /* @var RoleHierarchicalInterface $eRole */
        foreach ($recordRoles as $eRole) {
            $reachableRoles = array_merge($reachableRoles, $this->getReachableRoles($eRole->getChildren()->toArray()));
        }

        $this->enableFilters($om, $filters);

        return $reachableRoles;
    }

    /**
     * Cleaning the double roles.
     *
     * @param Role[] $reachableRoles The reachable roles
     *
     * @return Role[]
     */
    private function getCleanedRoles(array $reachableRoles)
    {
        $existingRoles = array();
        $finalRoles = array();

        foreach ($reachableRoles as $role) {
            if (!in_array($role->getRole(), $existingRoles)) {
                if (!($role instanceof Role) || $role instanceof RoleInterface) {
                    $role = new Role($role->getRole());
                }

                $existingRoles[] = $role->getRole();
                $finalRoles[] = $role;
            }
        }

        return $finalRoles;
    }

    /**
     * Disable and get the orm filters.
     *
     * @param ObjectManager|null $om The object manager
     *
     * @return string[]
     */
    private function disableFilters($om)
    {
        $filters = array();

        if (interface_exists('Doctrine\ORM\EntityManagerInterface')
                && $om instanceof EntityManagerInterface) {
            $filters = array_keys($om->getFilters()->getEnabledFilters());

            foreach ($filters as $name) {
                $om->getFilters()->disable($name);
            }
        }

        return $filters;
    }

    /**
     * Enable the orm filters.
     *
     * @param ObjectManager|null $om      The object manager
     * @param string[]           $filters The filter names
     */
    private function enableFilters($om, array $filters)
    {
        if (count($filters) > 0 && interface_exists('Doctrine\ORM\EntityManagerInterface')
                && $om instanceof EntityManagerInterface) {
            foreach ($filters as $name) {
                $om->getFilters()->enable($name);
            }
        }
    }
}
