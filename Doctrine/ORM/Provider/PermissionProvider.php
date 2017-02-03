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
use Doctrine\ORM\QueryBuilder;
use Sonatra\Component\Security\Exception\InvalidArgumentException;
use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Permission\PermissionConfigInterface;
use Sonatra\Component\Security\Permission\PermissionProviderInterface;
use Sonatra\Component\Security\Permission\PermissionUtils;

/**
 * The Doctrine Orm Permission Provider.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionProvider implements PermissionProviderInterface
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
     * @param EntityRepository $permissionRepository The permission repository
     * @param ManagerRegistry  $registry             The doctrine registry
     */
    public function __construct(EntityRepository $permissionRepository,
                                ManagerRegistry $registry)
    {
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

        $permissions = $qb
            ->where('UPPER(r.name) IN (:roles)')
            ->setParameter('roles', $roles)
            ->orderBy('p.class', 'asc')
            ->addOrderBy('p.field', 'asc')
            ->addOrderBy('p.operation', 'asc')
            ->getQuery()
            ->getResult();

        return $permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionsBySubject($subject = null)
    {
        /* @var SubjectIdentityInterface|null $subject */
        list($subject, $field) = PermissionUtils::getSubjectAndField($subject, true);

        $qb = $this->permissionRepo->createQueryBuilder('p')
            ->orderBy('p.class', 'asc')
            ->addOrderBy('p.field', 'asc')
            ->addOrderBy('p.operation', 'asc');

        $this->addWhereOptionalField($qb, 'class', null !== $subject ? $subject->getType() : null);
        $this->addWhereOptionalField($qb, 'field', $field);
        $permissions = $qb->getQuery()->getResult();

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
     * @param ObjectManager|null        $om     The doctrine object manager
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

    /**
     * Add the optional field condition.
     *
     * @param QueryBuilder $qb    The query builder
     * @param string       $field The field name
     * @param mixed|null   $value The value
     */
    private function addWhereOptionalField(QueryBuilder $qb, $field, $value)
    {
        if (null === $value) {
            $qb->andWhere('p.'.$field.' IS NULL');
        } else {
            $qb->andWhere('p.'.$field.' = :'.$field)->setParameter($field, $value);
        }
    }
}
