<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Permission;

use Sonatra\Component\Security\Event\CheckPermissionEvent;
use Sonatra\Component\Security\Event\PostLoadPermissionsEvent;
use Sonatra\Component\Security\Event\PreLoadPermissionsEvent;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SubjectIdentity;
use Sonatra\Component\Security\Identity\UserSecurityIdentity;
use Sonatra\Component\Security\Permission\PermissionConfig;
use Sonatra\Component\Security\Permission\PermissionManager;
use Sonatra\Component\Security\Permission\PermissionProviderInterface;
use Sonatra\Component\Security\PermissionEvents;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockOrganizationUser;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockPermission;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var PermissionProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $provider;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var PermissionManager
     */
    protected $pm;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->provider = $this->getMockBuilder(PermissionProviderInterface::class)->getMock();
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor
        );
    }

    public function testIsEnabled()
    {
        $this->assertTrue($this->pm->isEnabled());

        $this->pm->setEnabled(false);
        $this->assertFalse($this->pm->isEnabled());

        $this->pm->setEnabled(true);
        $this->assertTrue($this->pm->isEnabled());
    }

    public function testHasConfig()
    {
        $pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            null,
            array(
                new PermissionConfig(MockObject::class),
            )
        );

        $this->assertTrue($pm->hasConfig(MockObject::class));
    }

    public function testHasNotConfig()
    {
        $this->assertFalse($this->pm->hasConfig(MockObject::class));
    }

    public function testAddConfig()
    {
        $this->assertFalse($this->pm->hasConfig(MockObject::class));

        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        $this->assertTrue($this->pm->hasConfig(MockObject::class));
    }

    public function testGetConfig()
    {
        $config = new PermissionConfig(MockObject::class);
        $this->pm->addConfig($config);

        $this->assertTrue($this->pm->hasConfig(MockObject::class));
        $this->assertSame($config, $this->pm->getConfig(MockObject::class));
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\PermissionConfigNotFoundException
     * @expectedExceptionMessage The permission configuration for the class "Sonatra\Component\Security\Tests\Fixtures\Model\MockObject" is not found
     */
    public function testGetConfigWithNotManagedClass()
    {
        $this->pm->getConfig(MockObject::class);
    }

    public function testIsManaged()
    {
        $this->pm->addConfig(new PermissionConfig(MockObject::class));
        $object = new MockObject('foo');

        $this->assertTrue($this->pm->isManaged($object));
    }

    public function testIsManagedWithInvalidSubject()
    {
        $object = new \stdClass();

        $this->assertFalse($this->pm->isManaged($object));
    }

    public function testIsManagedWithNonExistentSubject()
    {
        $this->assertFalse($this->pm->isManaged('FooBar'));
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "FieldVote|SubjectIdentityInterface|object|string", "NULL"
     */
    public function testIsManagedWithUnexpectedTypeException()
    {
        $this->assertFalse($this->pm->isManaged(null));
    }

    public function testIsManagedWithNonManagedClass()
    {
        $this->assertFalse($this->pm->isManaged(MockObject::class));
    }

    public function testIsFieldManaged()
    {
        $this->pm->addConfig(new PermissionConfig(MockObject::class, array('name')));

        $object = new MockObject('foo');
        $field = 'name';

        $this->assertTrue($this->pm->isFieldManaged($object, $field));
    }

    public function testIsGranted()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
        );
        $object = MockObject::class;
        $permission = 'view';

        $this->assertTrue($this->pm->isGranted($sids, $permission, $object));
    }

    public function testIsGrantedWithNonExistentSubject()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
        );
        $object = 'FooBar';
        $permission = 'view';

        $this->assertFalse($this->pm->isGranted($sids, $permission, $object));
    }

    public function testIsGrantedWithGlobalPermission()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
        );
        $object = null;
        $permission = 'foo';
        $perm = new MockPermission();
        $perm->setOperation('foo');

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(array('ROLE_USER'))
            ->willReturn(array($perm));

        $this->assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testIsGrantedWithGlobalPermissionAndMaster()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
            new UserSecurityIdentity('user.test'),
        );
        $org = new MockOrganization('foo');
        $user = new MockUserRoleable();
        $orgUser = new MockOrganizationUser($org, $user);
        $permission = 'view';
        $perm = new MockPermission();
        $perm->setClass(MockOrganization::class);
        $perm->setOperation('view');

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(array('ROLE_USER'))
            ->willReturn(array($perm));

        $this->pm->addConfig(new PermissionConfig(MockOrganization::class));
        $this->pm->addConfig(new PermissionConfig(MockOrganizationUser::class, array(), 'organization'));

        $this->assertTrue($this->pm->isGranted($sids, $permission, $orgUser));
        $this->pm->clear();
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\InvalidArgumentException
     * @expectedExceptionMessage The permission configuration of "Sonatra\Component\Security\Tests\Fixtures\Model\MockOrganizationUser" class has a master relation, only object instance can be used
     */
    public function testIsGrantedWithGlobalPermissionAndMasterWithEmptyObjectOsSubject()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
            new UserSecurityIdentity('user.test'),
        );
        $object = new SubjectIdentity(MockOrganizationUser::class, 42);
        $permission = 'view';
        $perm = new MockPermission();
        $perm->setClass(MockOrganization::class);
        $perm->setOperation('view');

        $this->provider->expects($this->never())
            ->method('getPermissions');

        $this->pm->addConfig(new PermissionConfig(MockOrganization::class));
        $this->pm->addConfig(new PermissionConfig(MockOrganizationUser::class, array(), 'organization'));

        $this->pm->isGranted($sids, $permission, $object);
    }

    public function testIsGrantedWithGlobalPermissionWithoutGrant()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
            new RoleSecurityIdentity('ROLE_USER__FOO'),
        );
        $object = null;
        $permission = 'bar';
        $perm = new MockPermission();
        $perm->setOperation('baz');

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(array('ROLE_USER', 'ROLE_USER__FOO'))
            ->willReturn(array($perm));

        $this->assertFalse($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testIsFieldGranted()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
        );
        $object = new MockObject('foo');
        $field = 'name';
        $permission = 'view';

        $this->assertTrue($this->pm->isFieldGranted($sids, $permission, $object, $field));
    }

    public function testIsGrantedWithSharingPermission()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
        );
        $object = new MockObject('foo');
        $permission = 'test';

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(array('ROLE_USER'))
            ->willReturn(array());

        /* @var SharingManagerInterface|\PHPUnit_Framework_MockObject_MockObject $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects($this->once())
            ->method('preloadRolePermissions')
            ->with(array(SubjectIdentity::fromObject($object)));

        $sharingManager->expects($this->once())
            ->method('isGranted')
            ->with($permission, SubjectIdentity::fromObject($object))
            ->willReturn(true);

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            $sharingManager
        );
        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        $this->assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testPreloadPermissions()
    {
        $objects = array(new MockObject('foo'));

        $pm = $this->pm->preloadPermissions($objects);

        $this->assertSame($this->pm, $pm);
    }

    public function testPreloadPermissionsWithSharing()
    {
        $objects = array(new MockObject('foo'));

        /* @var SharingManagerInterface|\PHPUnit_Framework_MockObject_MockObject $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects($this->once())
            ->method('preloadPermissions')
            ->with($objects);

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            $sharingManager
        );

        $pm = $this->pm->preloadPermissions($objects);

        $this->assertSame($this->pm, $pm);
    }

    public function testResetPreloadPermissions()
    {
        $objects = array(
            new MockObject('foo'),
        );

        $pm = $this->pm->resetPreloadPermissions($objects);

        $this->assertSame($this->pm, $pm);
    }

    public function testResetPreloadPermissionsWithSharing()
    {
        $objects = array(new MockObject('foo'));

        /* @var SharingManagerInterface|\PHPUnit_Framework_MockObject_MockObject $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects($this->once())
            ->method('resetPreloadPermissions')
            ->with($objects);

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            $sharingManager
        );

        $pm = $this->pm->resetPreloadPermissions($objects);

        $this->assertSame($this->pm, $pm);
    }

    public function testEvents()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
        );
        $object = MockObject::class;
        $permission = 'view';
        $perm = new MockPermission();
        $perm->setOperation($permission);
        $perm->setClass(MockObject::class);
        $preLoad = false;
        $postLoad = false;
        $checkPerm = false;

        $this->dispatcher->addListener(PermissionEvents::PRE_LOAD, function (PreLoadPermissionsEvent $event) use ($sids, &$preLoad) {
            $preLoad = true;
            $this->assertSame($sids, $event->getSecurityIdentities());
        });

        $this->dispatcher->addListener(PermissionEvents::POST_LOAD, function (PostLoadPermissionsEvent $event) use ($sids, &$postLoad) {
            $postLoad = true;
            $this->assertSame($sids, $event->getSecurityIdentities());
        });

        $this->dispatcher->addListener(PermissionEvents::CHECK_PERMISSION, function (CheckPermissionEvent $event) use ($sids, &$checkPerm) {
            $checkPerm = true;
            $this->assertSame($sids, $event->getSecurityIdentities());
        });

        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(array('ROLE_USER'))
            ->willReturn(array($perm));

        $this->assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->assertTrue($preLoad);
        $this->assertTrue($postLoad);
        $this->assertTrue($checkPerm);
    }

    public function testOverrideGrantValueWithEvent()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
        );
        $object = MockObject::class;
        $permission = 'view';
        $checkPerm = false;

        $this->dispatcher->addListener(PermissionEvents::CHECK_PERMISSION, function (CheckPermissionEvent $event) use ($sids, &$checkPerm) {
            $checkPerm = true;
            $event->setGranted(true);
        });

        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(array('ROLE_USER'))
            ->willReturn(array());

        $this->assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->assertTrue($checkPerm);
    }
}
