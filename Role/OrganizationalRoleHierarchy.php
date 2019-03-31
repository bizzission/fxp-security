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
use Fxp\Component\Security\Model\RoleInterface;
use Fxp\Component\Security\Organizational\OrganizationalContextInterface;
use Fxp\Component\Security\Organizational\OrganizationalUtil;
use Psr\Cache\CacheItemPoolInterface;

/**
 * RoleHierarchy defines a role hierarchy.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class OrganizationalRoleHierarchy extends RoleHierarchy
{
    /**
     * @var OrganizationalContextInterface|null
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param array                               $hierarchy     An array defining the hierarchy
     * @param ManagerRegistryInterface            $registry      The doctrine registry
     * @param CacheItemPoolInterface|null         $cache         The cache
     * @param OrganizationalContextInterface|null $context       The organizational context
     * @param string                              $roleClassname The classname of role
     */
    public function __construct(array $hierarchy,
                                ManagerRegistryInterface $registry,
                                CacheItemPoolInterface $cache = null,
                                OrganizationalContextInterface $context = null,
                                $roleClassname = RoleInterface::class)
    {
        parent::__construct($hierarchy, $registry, $cache, $roleClassname);
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUniqueId(array $roleNames)
    {
        $id = parent::getUniqueId($roleNames);

        if (null !== $this->context && null !== ($org = $this->context->getCurrentOrganization())) {
            $id = ($org->isUserOrganization() ? 'user' : $org->getId()).'__'.$id;
        }

        return $id;
    }

    /**
     * {@inheritdoc}
     */
    protected function formatRoles(array $roles)
    {
        return array_map(static function ($role) {
            return OrganizationalUtil::format($role);
        }, $roles);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildRoleSuffix($role)
    {
        return null !== $role ? OrganizationalUtil::getSuffix($role) : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanRoleNames(array $roles)
    {
        return array_map(static function ($role) {
            return OrganizationalUtil::format($role);
        }, $roles);
    }

    /**
     * {@inheritdoc}
     */
    protected function formatCleanedRoleName($name)
    {
        return OrganizationalUtil::format($name);
    }
}
