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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Sonatra\Component\Security\Exception\InvalidArgumentException;
use Sonatra\Component\Security\Permission\PermissionConfigInterface;
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
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * Constructor.
     *
     * @param EntityRepository $roleRepository           The role repository
     * @param EntityRepository $permissionRepository     The permission repository
     * @param ManagerRegistry  $registry                 The doctrine registry
     * @param bool             $mergeOrganizationalRoles Check if the organizational roles must be included with system roles
     */
    public function __construct(EntityRepository $roleRepository,
                                EntityRepository $permissionRepository,
                                ManagerRegistry $registry,
                                $mergeOrganizationalRoles = true)
    {
        parent::__construct($roleRepository, $mergeOrganizationalRoles);

        $this->permissionRepo = $permissionRepository;
        $this->registry = $registry;
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

    /**
     * {@inheritdoc}
     */
    public function getMasterClass(PermissionConfigInterface $config)
    {
        $type = $config->getType();
        $om = $this->registry->getManagerForClass($type);
        $this->validateMaster($config, $om);
        $meta = $om->getClassMetadata($type);

        return $meta->getAssociationTargetClass($config->getMaster());
    }

    /**
     * Validate the master config.
     *
     * @param PermissionConfigInterface $config The permission config
     * @param ObjectManager             $om     The doctrine object manager
     */
    private function validateMaster(PermissionConfigInterface $config, $om)
    {
        if (null === $om) {
            $msg = 'The doctrine object manager is not found for the class "%s"';

            throw new InvalidArgumentException(sprintf($msg, $config->getType()));
        }

        if (null === $config->getMaster()) {
            $msg = 'The permission master association is not configured for the class "%s"';

            throw new InvalidArgumentException(sprintf($msg, $config->getType()));
        }
    }
}
