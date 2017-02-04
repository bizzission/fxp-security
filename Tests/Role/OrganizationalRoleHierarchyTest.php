<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Role;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Query\FilterCollection;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Sonatra\Component\Security\Model\OrganizationInterface;
use Sonatra\Component\Security\Model\RoleHierarchicalInterface;
use Sonatra\Component\Security\Organizational\OrganizationalContextInterface;
use Sonatra\Component\Security\Role\OrganizationalRoleHierarchy;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockRole;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationalRoleHierarchyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ManagerRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var string
     */
    protected $roleClassname;

    /**
     * @var CacheItemPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cache;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var ObjectRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repo;

    /**
     * @var FilterCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filters;

    /**
     * @var OrganizationalContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var OrganizationalRoleHierarchy
     */
    protected $roleHierarchy;

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder(ManagerRegistryInterface::class)->getMock();
        $this->roleClassname = MockRole::class;
        $this->cache = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $this->context = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->repo = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $this->filters = $this->getMockBuilder(FilterCollection::class)->disableOriginalConstructor()->getMock();

        $hierarchy = array(
            'ROLE_ADMIN' => array(
                'ROLE_USER',
            ),
        );
        $this->roleHierarchy = new OrganizationalRoleHierarchy(
            $hierarchy,
            $this->registry,
            $this->roleClassname,
            $this->cache,
            $this->context
        );

        $this->roleHierarchy->setEventDispatcher($this->eventDispatcher);

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->roleClassname)
            ->willReturn($this->em);

        $this->em->expects($this->any())
            ->method('getRepository')
            ->with($this->roleClassname)
            ->willReturn($this->repo);

        $this->em->expects($this->any())
            ->method('getFilters')
            ->willReturn($this->filters);
    }

    public function getOrganizationContextType()
    {
        return array(
            array(null),
            array('user'),
            array('organization'),
        );
    }

    /**
     * @dataProvider getOrganizationContextType
     *
     * @param string|null $orgContextType The organization context type
     */
    public function testGetReachableRolesWithCustomRoles($orgContextType)
    {
        $this->initOrgContextType($orgContextType);

        $roles = array(
            new MockRole('ROLE_ADMIN'),
        );
        $validRoles = array(
            new Role('ROLE_ADMIN'),
            new Role('ROLE_USER'),
        );

        $cacheItem = $this->getMockBuilder(CacheItemInterface::class)->getMock();

        $this->cache->expects($this->once())
            ->method('getItem')
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('get')
            ->with()
            ->willReturn(null);

        $cacheItem->expects($this->once())
            ->method('isHit')
            ->with()
            ->willReturn(false);

        $this->eventDispatcher->expects($this->atLeastOnce())
            ->method('dispatch');

        $sqlFilters = array(
            'test_filter' => $this->getMockForAbstractClass(SQLFilter::class, array(), '', false),
        );

        $this->filters->expects($this->once())
            ->method('getEnabledFilters')
            ->willReturn($sqlFilters);

        $this->filters->expects($this->once())
            ->method('disable')
            ->with('test_filter');

        $dbRole = $this->getMockBuilder(RoleHierarchicalInterface::class)->getMock();
        $dbRoleChildren = $this->getMockBuilder(Collection::class)->getMock();

        $dbRole->expects($this->any())
            ->method('getRole')
            ->willReturn('ROLE_ADMIN');

        $dbRole->expects($this->any())
            ->method('getName')
            ->willReturn('ROLE_ADMIN');

        $dbRole->expects($this->once())
            ->method('getChildren')
            ->willReturn($dbRoleChildren);

        $dbRoleChildren->expects($this->once())
            ->method('toArray')
            ->willReturn(array());

        $this->repo->expects($this->once())
            ->method('findBy')
            ->with(array('name' => array('ROLE_ADMIN')))
            ->willReturn(array($dbRole));

        $this->filters->expects($this->once())
            ->method('enable')
            ->with('test_filter');

        $cacheItem->expects($this->once())
            ->method('set');

        $this->cache->expects($this->once())
            ->method('save');

        $fullRoles = $this->roleHierarchy->getReachableRoles($roles);

        $this->assertCount(2, $fullRoles);
        $this->assertEquals($validRoles, $fullRoles);
    }

    /**
     * Init the organization context type.
     *
     * @param string|null $orgContextType The organization context type
     */
    protected function initOrgContextType($orgContextType)
    {
        $org = null;

        if (in_array($orgContextType, array('user', 'organization'))) {
            $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
            $org->expects($this->once())
                ->method('isUserOrganization')
                ->willReturn('user' === $orgContextType);
        }

        $this->context->expects($this->once())
            ->method('getCurrentOrganization')
            ->willReturn($org);
    }
}
