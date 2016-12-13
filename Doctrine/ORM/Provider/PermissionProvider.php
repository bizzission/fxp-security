<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Doctrine\ORM\Provider;

use Doctrine\ORM\EntityRepository;
use Sonatra\Component\Security\Permission\PermissionProviderInterface;

/**
 * The Doctrine Orm Permission Provider.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionProvider extends AbstractProvider implements PermissionProviderInterface
{
    /**
     * @var EntityRepository
     */
    protected $permissionRepo;

    /**
     * Constructor.
     *
     * @param EntityRepository $roleRepository           The role repository
     * @param EntityRepository $permissionRepository     The permission repository
     * @param bool             $mergeOrganizationalRoles Check if the organizational roles must be included with system roles
     */
    public function __construct(EntityRepository $roleRepository,
                                EntityRepository $permissionRepository,
                                $mergeOrganizationalRoles = true)
    {
        parent::__construct($roleRepository, $mergeOrganizationalRoles);

        $this->permissionRepo = $permissionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions(array $roles)
    {
        if (empty($roles)) {
            return array();
        }

        $qb = $this->permissionRepo->createQueryBuilder('p')
            ->leftJoin('p.roles', 'r');

        $permissions = $this->addWhere($qb, $roles)
            ->orderBy('p.class', 'asc')
            ->addOrderBy('p.field', 'asc')
            ->addOrderBy('p.operation', 'asc')
            ->getQuery()
            ->getResult();

        $this->permissionRepo->clear();

        return $permissions;
    }
}
