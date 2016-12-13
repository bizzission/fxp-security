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
use Doctrine\ORM\QueryBuilder;
use Sonatra\Component\Security\Model\Traits\OrganizationalInterface;

/**
 * The base of Doctrine Orm Provider.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractProvider
{
    /**
     * @var EntityRepository
     */
    protected $roleRepo;

    /**
     * @var string
     */
    protected $roleClass;

    /**
     * @var bool|null
     */
    protected $isOrganizational;

    /**
     * @var bool
     */
    protected $mergeOrganizationalRoles;

    /**
     * Constructor.
     *
     * @param EntityRepository $roleRepository           The role repository
     * @param bool             $mergeOrganizationalRoles Check if the organizational roles must be included with system roles
     */
    public function __construct(EntityRepository $roleRepository, $mergeOrganizationalRoles = true)
    {
        $this->roleRepo = $roleRepository;
        $this->roleClass = $this->roleRepo->getClassName();
        $this->mergeOrganizationalRoles = $mergeOrganizationalRoles;
    }

    /**
     * Add the where conditions.
     *
     * @param QueryBuilder $qb    The query builder
     * @param string[]     $roles The roles
     *
     * @return QueryBuilder
     */
    protected function addWhere(QueryBuilder $qb, array $roles)
    {
        if ($this->isOrganizational()) {
            $this->addWhereForOrganizationalRole($qb, $roles);
        } else {
            $this->addWhereForRole($qb, $roles);
        }

        return $qb;
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
            $where .= '(UPPER(r.name) in (:roles) AND r.organization IS NULL)';
            $parameters['roles'] = $this->mergeOrganizationalRoles($fRoles['roles'], $fRoles['org_roles']);
        }

        if (!empty($fRoles['org_roles'])) {
            $qb->leftJoin('r.organization', 'o');

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
     * Merge the organization user roles with the system roles.
     *
     * @param string[] $roles    The roles
     * @param array    $orgRoles The organization user roles by organization name
     *
     * @return string[]
     */
    private function mergeOrganizationalRoles(array $roles, array $orgRoles)
    {
        if ($this->mergeOrganizationalRoles && !empty($orgRoles)) {
            foreach ($orgRoles as $oRoles) {
                $roles = array_merge($roles, $oRoles);
            }

            $roles = array_values(array_unique($roles));
        }

        return $roles;
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
                $org = substr($role, $pos + 2);
                $fRoles['org_roles'][$org][] = substr($role, 0, $pos);
            } else {
                $fRoles['roles'][] = $role;
            }
        }

        return $fRoles;
    }
}
