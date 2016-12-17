<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Doctrine\ORM\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Sonatra\Component\Security\Doctrine\ORM\Provider\PermissionProvider;
use Sonatra\Component\Security\Permission\PermissionConfigInterface;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockOrganizationUser;
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
    protected $roleRepo;

    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

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
        $this->roleRepo = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $this->registry = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();
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

        $provider = $this->createProvider();
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
            'ROLE_USER__foo',
            'ROLE_ADMIN__baz',
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
            ->method('leftJoin')
            ->with('r.organization', 'o')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(2))
            ->method('where')
            ->with('(UPPER(r.name) in (:roles) AND r.organization IS NULL) OR (UPPER(r.name) IN (:foo_roles) AND LOWER(o.name) = :foo_name) OR (UPPER(r.name) IN (:baz_roles) AND LOWER(o.name) = :baz_name)')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(3))
            ->method('setParameter')
            ->with('roles', array('ROLE_USER', 'ROLE_ADMIN'))
            ->willReturn($this->qb);

        $this->qb->expects($this->at(4))
            ->method('setParameter')
            ->with('foo_roles', array('ROLE_USER'))
            ->willReturn($this->qb);

        $this->qb->expects($this->at(5))
            ->method('setParameter')
            ->with('foo_name', 'foo')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(6))
            ->method('setParameter')
            ->with('baz_roles', array('ROLE_ADMIN'))
            ->willReturn($this->qb);

        $this->qb->expects($this->at(7))
            ->method('setParameter')
            ->with('baz_name', 'baz')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(8))
            ->method('orderBy')
            ->with('p.class', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(9))
            ->method('addOrderBy')
            ->with('p.field', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(10))
            ->method('addOrderBy')
            ->with('p.operation', 'asc')
            ->willReturn($this->qb);

        $this->qb->expects($this->at(11))
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        $provider = $this->createProvider($roleClass);
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

        $provider = $this->createProvider($roleClass);
        $this->assertSame(array(), $provider->getPermissions(array()));
    }

    public function testGetMasterClass()
    {
        $om = $this->getMockBuilder(ObjectManager::class)->getMock();

        /* @var PermissionConfigInterface|\PHPUnit_Framework_MockObject_MockObject $permConfig */
        $permConfig = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();

        $permConfig->expects($this->once())
            ->method('getType')
            ->willReturn(MockOrganizationUser::class);

        $permConfig->expects($this->atLeast(2))
            ->method('getMaster')
            ->willReturn('organization');

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($om);

        $meta = $this->getMockBuilder(ClassMetadata::class)->getMock();

        $meta->expects($this->once())
            ->method('getAssociationTargetClass')
            ->with('organization')
            ->willReturn(MockOrganization::class);

        $om->expects($this->once())
            ->method('getClassMetadata')
            ->with(MockOrganizationUser::class)
            ->willReturn($meta);

        $provider = $this->createProvider();
        $this->assertSame(MockOrganization::class, $provider->getMasterClass($permConfig));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The doctrine object manager is not found for the class "Sonatra\Component\Security\Tests\Fixtures\Model\MockObject"
     */
    public function testGetMasterClassWithoutObjectManagerForClass()
    {
        /* @var PermissionConfigInterface|\PHPUnit_Framework_MockObject_MockObject $permConfig */
        $permConfig = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();

        $permConfig->expects($this->atLeast(2))
            ->method('getType')
            ->willReturn(MockObject::class);

        $provider = $this->createProvider();
        $provider->getMasterClass($permConfig);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The permission master association is not configured for the class "Sonatra\Component\Security\Tests\Fixtures\Model\MockObject"
     */
    public function testGetMasterClassWithoutMaster()
    {
        $om = $this->getMockBuilder(ObjectManager::class)->getMock();

        /* @var PermissionConfigInterface|\PHPUnit_Framework_MockObject_MockObject $permConfig */
        $permConfig = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();

        $permConfig->expects($this->atLeast(2))
            ->method('getType')
            ->willReturn(MockObject::class);

        $permConfig->expects($this->once())
            ->method('getMaster')
            ->willReturn(null);

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($om);

        $provider = $this->createProvider();
        $provider->getMasterClass($permConfig);
    }

    protected function createProvider($roleClass = MockRole::class)
    {
        $this->roleRepo->expects($this->once())
            ->method('getClassName')
            ->willReturn($roleClass);

        return new PermissionProvider(
            $this->roleRepo,
            $this->permissionRepo,
            $this->registry
        );
    }
}
