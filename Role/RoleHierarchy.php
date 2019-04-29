<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Role;

use Doctrine\Common\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Fxp\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Fxp\Component\DoctrineExtra\Util\ManagerUtils;
use Fxp\Component\Security\Event\PostReachableRoleEvent;
use Fxp\Component\Security\Event\PreReachableRoleEvent;
use Fxp\Component\Security\Model\RoleHierarchicalInterface;
use Fxp\Component\Security\Model\RoleInterface;
use Fxp\Component\Security\ReachableRoleEvents;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy as BaseRoleHierarchy;

/**
 * RoleHierarchy defines a role hierarchy.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
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
     * @param CacheItemPoolInterface|null $cache         The cache
     * @param string                      $roleClassname The classname of role
     */
    public function __construct(array $hierarchy,
                                ManagerRegistryInterface $registry,
                                CacheItemPoolInterface $cache = null,
                                $roleClassname = RoleInterface::class)
    {
        parent::__construct($hierarchy);

        $this->registry = $registry;
        $this->roleClassname = $roleClassname;
        $this->cacheExec = [];
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
     * {@inheritdoc}
     */
    public function getReachableRoles(array $roles)
    {
        return RoleUtil::formatRoles($this->doGetReachableRoles(RoleUtil::formatNames($roles)));
    }

    /**
     * Returns an array of all roles reachable by the given ones.
     *
     * @param string[] $roles  An array of roles
     * @param string   $suffix The role name suffix
     *
     * @return string[] An array of role instances
     */
    protected function doGetReachableRoles(array $roles, $suffix = '')
    {
        if (0 === \count($roles)) {
            return $roles;
        }

        $item = null;
        $roles = $this->formatRoles($roles);
        $id = $this->getUniqueId($roles);

        if (null !== ($reachableRoles = $this->getCachedReachableRoles($id, $item))) {
            return $reachableRoles;
        }

        // build hierarchy
        /* @var string[] $reachableRoles */
        $reachableRoles = RoleUtil::formatNames(parent::getReachableRoles(RoleUtil::formatRoles($roles)));
        $isPermEnabled = true;

        if (null !== $this->eventDispatcher) {
            $event = new PreReachableRoleEvent($reachableRoles);
            $this->eventDispatcher->dispatch(ReachableRoleEvents::PRE, $event);
            $reachableRoles = $event->getReachableRoles();
            $isPermEnabled = $event->isPermissionEnabled();
        }

        return $this->getAllRoles($reachableRoles, $roles, $id, $item, $isPermEnabled, $suffix);
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
     * Format the roles.
     *
     * @param string[] $roles The roles
     *
     * @return string[]
     */
    protected function formatRoles(array $roles)
    {
        return $roles;
    }

    /**
     * Build the suffix of role.
     *
     * @param string|null $role The role
     *
     * @return string
     */
    protected function buildRoleSuffix($role)
    {
        return '';
    }

    /**
     * Clean the role names.
     *
     * @param string[] $roles The role names
     *
     * @return string[]
     */
    protected function cleanRoleNames(array $roles)
    {
        return $roles;
    }

    /**
     * Format the cleaned role name.
     *
     * @param string $name The role name
     *
     * @return string
     */
    protected function formatCleanedRoleName($name)
    {
        return $name;
    }

    /**
     * Get the reachable roles in cache if available.
     *
     * @param string $id   The cache id
     * @param null   $item The cache item variable passed by reference
     *
     * @return string[]|null
     *
     * @throws
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
     * @param string[]                $reachableRoles The reachable roles
     * @param string[]                $roles          The roles
     * @param string                  $id             The cache item id
     * @param CacheItemInterface|null $item           The cache item
     * @param bool                    $isPermEnabled  Check if the permission manager is enabled
     * @param string                  $suffix         The role name suffix
     *
     * @return string[]
     */
    private function getAllRoles(array $reachableRoles, array $roles, $id, $item, $isPermEnabled, $suffix = '')
    {
        $reachableRoles = $this->findRecords($reachableRoles, $roles);
        $reachableRoles = $this->getCleanedRoles($reachableRoles, $suffix);

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
     * Find the roles in database.
     *
     * @param string[] $reachableRoles The reachable roles
     * @param string[] $roles          The role names
     *
     * @return string[]
     *
     * @throws
     */
    private function findRecords(array $reachableRoles, array $roles)
    {
        $recordRoles = [];
        $om = ManagerUtils::getManager($this->registry, $this->roleClassname);
        $repo = $om->getRepository($this->roleClassname);

        $filters = SqlFilterUtil::findFilters($om, [], true);
        SqlFilterUtil::disableFilters($om, $filters);

        if (\count($roles) > 0) {
            $recordRoles = $repo->findBy(['name' => $this->cleanRoleNames($roles)]);
        }

        $loopReachableRoles = [$reachableRoles];

        /* @var RoleHierarchicalInterface $eRole */
        foreach ($recordRoles as $eRole) {
            $suffix = $this->buildRoleSuffix($roles[$eRole->getName()] ?? null);
            $subRoles = RoleUtil::formatNames($eRole->getChildren()->toArray());
            $loopReachableRoles[] = $this->doGetReachableRoles($subRoles, $suffix);
        }

        SqlFilterUtil::enableFilters($om, $filters);

        return array_merge(...$loopReachableRoles);
    }

    /**
     * Cleaning the double roles.
     *
     * @param string[] $reachableRoles The reachable roles
     * @param string   $suffix         The role name suffix
     *
     * @return string[]
     */
    private function getCleanedRoles(array $reachableRoles, $suffix = '')
    {
        $existingRoles = [];
        $finalRoles = [];

        foreach ($reachableRoles as $role) {
            $name = $this->formatCleanedRoleName($role);

            if (!\in_array($name, $existingRoles)) {
                $rSuffix = 'ROLE_USER' !== $name && 'ORGANIZATION_ROLE_USER' !== $name ? $suffix : '';
                $existingRoles[] = $name;
                $finalRoles[] = $role.$rSuffix;
            }
        }

        return $finalRoles;
    }
}
