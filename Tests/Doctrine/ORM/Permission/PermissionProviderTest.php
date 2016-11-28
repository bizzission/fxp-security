<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Doctrine\ORM\Permission;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Sonatra\Component\Security\Doctrine\ORM\Permission\PermissionProvider;
use Sonatra\Component\Security\Identity\SubjectIdentity;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockOrgOptionalRole;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockOrgRequiredRole;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockPermission;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockRole;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permissionRepo;

    /**
     * @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharingRepo;

    /**
     * @var QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $qb;

    /**
     * @var Query|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $query;

    protected function setUp()
    {
        $this->permissionRepo = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $this->sharingRepo = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $this->qb = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();

        $this->query = $this->getMockForAbstractClass(
            AbstractQuery::class,
            array(),
            '',
            false,
            false,
            true,
            array(
                'getResult',
            )
        );
    }

    public function testGetPermissions()
    {
        $roles = array(
            'ROLE_USER',
        );
        $result = array(
            new MockPermission(),
        );

        $this->permissionRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('leftJoin')
            ->with('p.roles', 'r')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('where')
            ->with('UPPER(r.name) IN (:roles)')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('setParameter')
            ->with('roles', $roles)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(6))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        $provider = new PermissionProvider($this->permissionRepo, $this->sharingRepo, MockRole::class);
        $this->assertSame($result, $provider->getPermissions($roles));
    }

    public function getOrganizationalRoleTypes()
    {
        return array(
            array(MockOrgRequiredRole::class),
            array(MockOrgOptionalRole::class),
        );
    }

    /**
     * @dataProvider getOrganizationalRoleTypes
     *
     * @param string $roleClass The classname of role
     */
    public function testGetPermissionsWithOrganizationalRole($roleClass)
    {
        $roles = array(
            'ROLE_USER',
            'ROLE_USER__FOO',
            'ROLE_ADMIN__BAZ',
        );
        $result = array(
            new MockPermission(),
        );

        $this->permissionRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('leftJoin')
            ->with('p.roles', 'r')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('where')
            ->with('(UPPER(r.name) in (:roles) AND r.organization = NULL) OR (UPPER(r.name) IN (:foo_roles) AND LOWER(o.name) = :foo_name) OR (UPPER(r.name) IN (:baz_roles) AND LOWER(o.name) = :baz_name)')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('setParameter')
            ->with('roles', array('ROLE_USER'))
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('setParameter')
            ->with('foo_roles', array('ROLE_USER'))
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('setParameter')
            ->with('foo_name', 'foo')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('setParameter')
            ->with('baz_roles', array('ROLE_ADMIN'))
            ->willReturn($this->qb);

        $this->qb->expects($this->at(6))
            ->method('setParameter')
            ->with('baz_name', 'baz')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(7))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(8))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(9))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(10))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        $provider = new PermissionProvider($this->permissionRepo, $this->sharingRepo, $roleClass);
        $this->assertSame($result, $provider->getPermissions($roles));
    }

    public function getOptimizationRoleTypes()
    {
        return array(
            array(MockRole::class),
            array(MockOrgRequiredRole::class),
            array(MockOrgOptionalRole::class),
        );
    }

    /**
     * @dataProvider getOptimizationRoleTypes
     *
     * @param string $roleClass The classname of role
     */
    public function testGetPermissionsOptimizationWithEmptyRoles($roleClass)
    {
        $this->permissionRepo->expects($this->never())
            ->method('createQueryBuilder');

        $provider = new PermissionProvider($this->permissionRepo, $this->sharingRepo, $roleClass);
        $this->assertSame(array(), $provider->getPermissions(array()));
    }

    public function testGetSharingEntries()
    {
        $subjects = array(
            SubjectIdentity::fromObject(new MockObject('foo', 42)),
            SubjectIdentity::fromObject(new MockObject('bar', 23)),
        );
        $result = array();

        $this->sharingRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(0))
            ->method('addSelect')
            ->with('p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(1))
            ->method('innerJoin')
            ->with('s.permissions', 'p')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('where')
            ->with('(s.subjectClass = :subject0_class AND s.subjectId = :subject0_id) OR (s.subjectClass = :subject1_class AND s.subjectId = :subject1_id)')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('setParameter')
            ->with('subject0_class', MockObject::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('setParameter')
            ->with('subject0_id', 42)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('setParameter')
            ->with('subject1_class', MockObject::class)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(6))
            ->method('setParameter')
            ->with('subject1_id', 23)
            ->willReturn($this->qb);

        $this->qb->expects($this->at(7))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(8))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(9))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(10))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        $provider = new PermissionProvider($this->permissionRepo, $this->sharingRepo, MockRole::class);
        $this->assertSame($result, $provider->getSharingEntries($subjects));
    }

    public function testGetSharingEntriesWithEmptySubjects()
    {
        $this->sharingRepo->expects($this->never())
            ->method('createQueryBuilder');

        $provider = new PermissionProvider($this->permissionRepo, $this->sharingRepo, MockRole::class);
        $this->assertSame(array(), $provider->getSharingEntries(array()));
    }
}
