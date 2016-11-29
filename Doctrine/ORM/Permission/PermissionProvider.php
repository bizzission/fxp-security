<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Doctrine\ORM\Permission;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Sonatra\Component\Security\Identity\IdentityUtils;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SecurityIdentityManagerInterface;
use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Model\Traits\OrganizationalInterface;
use Sonatra\Component\Security\Permission\PermissionProviderInterface;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * @var EntityRepository
     */
    protected $roleRepo;

    /**
     * @var SecurityIdentityManagerInterface
     */
    protected $sidManager;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var EntityRepository|null
     */
    protected $sharingRepo;

    /**
     * @var SharingManagerInterface|null
     */
    protected $sharingManager;

    /**
     * @var string
     */
    protected $roleClass;

    /**
     * @var bool|null
     */
    protected $isOrganizational;

    /**
     * Constructor.
     *
     * @param EntityRepository                 $permissionRepository The permission repository
     * @param EntityRepository                 $roleRepository       The role repository
     * @param SecurityIdentityManagerInterface $sidManager           The security identity manager
     * @param TokenStorageInterface            $tokenStorage         The token storage
     * @param EntityRepository|null            $sharingRepository    The sharing repository
     * @param SharingManagerInterface|null     $sharingManager       The sharing manager
     */
    public function __construct(EntityRepository $permissionRepository,
                                EntityRepository $roleRepository,
                                SecurityIdentityManagerInterface $sidManager,
                                TokenStorageInterface $tokenStorage,
                                EntityRepository $sharingRepository = null,
                                SharingManagerInterface $sharingManager = null)
    {
        $this->permissionRepo = $permissionRepository;
        $this->roleRepo = $roleRepository;
        $this->sidManager = $sidManager;
        $this->tokenStorage = $tokenStorage;
        $this->sharingRepo = $sharingRepository;
        $this->sharingManager = $sharingManager;
        $this->roleClass = $this->roleRepo->getClassName();
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
    public function getPermissionRoles(array $roles)
    {
        if (empty($roles)) {
            return array();
        }

        $qb = $this->roleRepo->createQueryBuilder('r')
            ->addSelect('p')
            ->leftJoin('r.permissions', 'p');

        $pRoles = $this->addWhere($qb, $roles)
            ->orderBy('p.class', 'asc')
            ->addOrderBy('p.field', 'asc')
            ->addOrderBy('p.operation', 'asc')
            ->getQuery()
            ->getResult();

        $this->permissionRepo->clear();

        return $pRoles;
    }

    /**
     * {@inheritdoc}
     */
    public function getSharingEntries(array $subjects, $sids = null)
    {
        if (empty($subjects) || null === $this->sharingRepo) {
            return array();
        }

        $sids = $this->getSecurityIdentities($sids);
        $qb = $this->sharingRepo->createQueryBuilder('s')
            ->addSelect('p')
            ->leftJoin('s.permissions', 'p');

        $sharingEntries = $this->addWhereForSharing($qb, $subjects, $sids)
            ->andWhere('s.enabled = TRUE')
            ->orderBy('p.class', 'asc')
            ->addOrderBy('p.field', 'asc')
            ->addOrderBy('p.operation', 'asc')
            ->getQuery()
            ->getResult();

        $this->permissionRepo->clear();

        return $sharingEntries;
    }

    /**
     * Add the where conditions.
     *
     * @param QueryBuilder $qb    The query builder
     * @param string[]     $roles The roles
     *
     * @return QueryBuilder
     */
    private function addWhere(QueryBuilder $qb, array $roles)
    {
        if ($this->isOrganizational()) {
            $this->addWhereForOrganizationalRole($qb, $roles);
        } else {
            $this->addWhereForRole($qb, $roles);
        }

        return $qb;
    }

    /**
     * Add where condition for role.
     *
     * @param QueryBuilder $qb    The query builder
     * @param string[]     $roles The roles
     */
    private function addWhereForRole(QueryBuilder $qb, array $roles)
    {
        $fRoles = $this->getRoles($roles);
        $qb
            ->where('UPPER(r.name) IN (:roles)')
            ->setParameter('roles', $fRoles['roles']);
    }

    /**
     * Add where condition for organizational role.
     *
     * @param QueryBuilder $qb    The query builder
     * @param string[]     $roles The roles
     */
    private function addWhereForOrganizationalRole(QueryBuilder $qb, array $roles)
    {
        $fRoles = $this->getRoles($roles);
        $where = '';
        $parameters = array();

        if (!empty($fRoles['roles'])) {
            $where .= '(UPPER(r.name) in (:roles) AND r.organization = NULL)';
            $parameters['roles'] = $fRoles['roles'];
        }

        if (!empty($fRoles['org_roles'])) {
            foreach ($fRoles['org_roles'] as $org => $orgRoles) {
                $orgName = str_replace(array('.', '-'), '_', $org);
                $where .= '' === $where ? '' : ' OR ';
                $where .= sprintf('(UPPER(r.name) IN (:%s) AND LOWER(o.name) = :%s)', $orgName.'_roles', $orgName.'_name');
                $parameters[$orgName.'_roles'] = $orgRoles;
                $parameters[$orgName.'_name'] = $org;
            }
        }

        $qb->where($where);

        foreach ($parameters as $name => $value) {
            $qb->setParameter($name, $value);
        }
    }

    /**
     * Add where condition for sharing.
     *
     * @param QueryBuilder                $qb       The query builder
     * @param SubjectIdentityInterface[]  $subjects The subjects
     * @param SecurityIdentityInterface[] $sids     The security identities
     *
     * @return QueryBuilder
     */
    private function addWhereForSharing(QueryBuilder $qb, array $subjects, array $sids)
    {
        $where = '';
        $parameters = array();

        foreach ($subjects as $i => $subject) {
            $class = 'subject'.$i.'_class';
            $id = 'subject'.$i.'_id';
            $parameters[$class] = $subject->getType();
            $parameters[$id] = $subject->getIdentifier();
            $where .= '' === $where ? '' : ' OR ';
            $where .= sprintf('(s.subjectClass = :%s AND s.subjectId = :%s)', $class, $id);
        }

        $qb->where($where);

        foreach ($parameters as $key => $value) {
            $qb->setParameter($key, $value);
        }

        return $this->addWhereSecurityIdentitiesForSharing($qb, $sids);
    }

    /**
     * Add security identities where condition for sharing.
     *
     * @param QueryBuilder                $qb   The query builder
     * @param SecurityIdentityInterface[] $sids The security identities
     *
     * @return QueryBuilder
     */
    private function addWhereSecurityIdentitiesForSharing(QueryBuilder $qb, array $sids)
    {
        if (!empty($sids)) {
            $where = '';
            $parameters = array();
            $groupSids = $this->groupSecurityIdentities($sids);
            $i = 0;

            foreach ($groupSids as $type => $identifiers) {
                $qClass = 'sid'.$i.'_class';
                $qIdentifiers = 'sid'.$i.'_ids';
                $parameters[$qClass] = $type;
                $parameters[$qIdentifiers] = $identifiers;
                $where .= '' === $where ? '' : ' OR ';
                $where .= sprintf('(s.identityClass = :%s AND s.identityName IN (:%s))', $qClass, $qIdentifiers);
                ++$i;
            }

            $qb->andWhere($where);

            foreach ($parameters as $key => $identifiers) {
                $qb->setParameter($key, $identifiers);
            }
        }

        return $qb;
    }

    /**
     * Group the security identities definition.
     *
     * @param SecurityIdentityInterface[] $sids The security identities
     *
     * @return array
     */
    private function groupSecurityIdentities(array $sids)
    {
        $groupSids = array();

        foreach ($sids as $sid) {
            if (IdentityUtils::isValid($sid)) {
                $type = $this->sharingManager->getIdentityConfig($sid->getType())->getType();
                $groupSids[$type][] = $sid->getIdentifier();
            }
        }

        return $groupSids;
    }

    /**
     * Get the roles and organization roles.
     *
     * @param string[] $roles The roles
     *
     * @return array
     */
    private function getRoles(array $roles)
    {
        $fRoles = array(
            'roles' => array(),
            'org_roles' => array(),
        );

        foreach ($roles as $role) {
            if (false !== ($pos = strrpos($role, '__'))) {
                $org = strtolower(substr($role, $pos + 2));
                $fRoles['org_roles'][$org][] = strtoupper(substr($role, 0, $pos));
            } else {
                $fRoles['roles'][] = strtoupper($role);
            }
        }

        return $fRoles;
    }

    /**
     * Check if the role is an organizational role.
     *
     * @return bool
     */
    private function isOrganizational()
    {
        if (null === $this->isOrganizational) {
            $ref = new \ReflectionClass($this->roleClass);
            $interfaces = $ref->getInterfaceNames();

            $this->isOrganizational = in_array(OrganizationalInterface::class, $interfaces);
        }

        return $this->isOrganizational;
    }

    /**
     * Get the security identities.
     *
     * @param SecurityIdentityInterface[]|null $sids The security identities to filter the sharing entries
     *
     * @return SecurityIdentityInterface[]
     */
    private function getSecurityIdentities($sids = null)
    {
        if (null === $sids && null !== $this->sharingManager) {
            $sids = $this->sidManager->getSecurityIdentities($this->tokenStorage->getToken());
        }

        return null !== $sids
            ? $sids
            : array();
    }
}
