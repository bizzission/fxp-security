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
use Sonatra\Component\Security\Permission\PermissionConfig;
use Sonatra\Component\Security\Permission\PermissionManager;
use Sonatra\Component\Security\Permission\PermissionProviderInterface;
use Sonatra\Component\Security\PermissionEvents;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Sonatra\Component\Security\SharingTypes;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockPermission;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockRole;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockSharing;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
     * @var PermissionManager
     */
    protected $pm;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->provider = $this->getMockBuilder(PermissionProviderInterface::class)->getMock();
        $this->pm = new PermissionManager($this->dispatcher, $this->provider);
    }

    public function testIsEnabled()
    {
        $this->assertTrue($this->pm->isEnabled());

        $this->pm->disable();
        $this->assertFalse($this->pm->isEnabled());

        $this->pm->enable();
        $this->assertTrue($this->pm->isEnabled());
    }

    public function testHasConfig()
    {
        $pm = new PermissionManager($this->dispatcher, $this->provider, null, array(
            new PermissionConfig(MockObject::class),
        ));

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
        $perm = new MockPermission();
        $perm->setOperation('test');
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setSubjectId(42);
        $sharing->getPermissions()->add($perm);

        $this->provider->expects($this->once())
            ->method('getSharingEntries')
            ->with(array(SubjectIdentity::fromObject($object)))
            ->willReturn(array($sharing));

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(array('ROLE_USER'))
            ->willReturn(array());

        $this->pm->addConfig(new PermissionConfig(MockObject::class, array(), SharingTypes::TYPE_PRIVATE));

        $this->assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testIsGrantedWithSharingPermissionAndRole()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
        );
        $object = new MockObject('foo');
        $permission = 'test';
        $perm = new MockPermission();
        $perm->setOperation('test');
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setSubjectId(42);
        $sharing->getPermissions()->add($perm);
        $sharing2 = new MockSharing();
        $sharing2->setSubjectClass(MockObject::class);
        $sharing2->setSubjectId(42);
        $sharing2->setIdentityClass(MockUserRoleable::class);
        $sharing2->setIdentityName('user.test');
        $sharing2->setRoles(array('ROLE_FOO'));
        $rolePerm = new MockPermission();
        $rolePerm->setOperation('baz');
        $roleUser = new MockRole('ROLE_FOO');
        $roleUser->getPermissions()->add($rolePerm);

        $this->provider->expects($this->once())
            ->method('getSharingEntries')
            ->with(array(SubjectIdentity::fromObject($object)))
            ->willReturn(array($sharing, $sharing2));

        $this->provider->expects($this->once())
            ->method('getPermissions')
            ->with(array('ROLE_USER'))
            ->willReturn(array());

        $this->provider->expects($this->once())
            ->method('getPermissionRoles')
            ->with(array('ROLE_FOO'))
            ->willReturn(array($roleUser));

        /* @var SharingManagerInterface|\PHPUnit_Framework_MockObject_MockObject $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects($this->once())
            ->method('hasIdentityPermissible')
            ->willReturn(true);

        $sharingManager->expects($this->once())
            ->method('hasIdentityRoleable')
            ->willReturn(true);

        $this->pm = new PermissionManager($this->dispatcher, $this->provider, $sharingManager);
        $this->pm->addConfig(new PermissionConfig(MockObject::class, array(), SharingTypes::TYPE_PRIVATE));

        $this->assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testPreloadPermissions()
    {
        $this->provider->expects($this->once())
            ->method('getSharingEntries')
            ->with(array())
            ->willReturn(array());

        $pm = $this->pm->preloadPermissions(array());

        $this->assertSame($this->pm, $pm);
    }

    public function testPreloadPermissionsWithSharing()
    {
        $objects = array(
            new MockObject('foo'),
        );
        $permission = new MockPermission();
        $permission->setOperation('test');
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setSubjectId(42);
        $sharing->getPermissions()->add($permission);

        $this->provider->expects($this->once())
            ->method('getSharingEntries')
            ->with(array(SubjectIdentity::fromObject($objects[0])))
            ->willReturn(array($sharing));

        $this->pm->addConfig(new PermissionConfig(MockObject::class, array(), SharingTypes::TYPE_PRIVATE));

        $this->pm->preloadPermissions($objects);
    }

    public function testPreloadPermissionsWithSharingPermissionsAndRoles()
    {
        $objects = array(
            new MockObject('foo'),
        );
        $permission = new MockPermission();
        $permission->setOperation('test');
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setSubjectId(42);
        $sharing->getPermissions()->add($permission);
        $sharing2 = new MockSharing();
        $sharing2->setSubjectClass(MockObject::class);
        $sharing2->setSubjectId(42);
        $sharing2->setIdentityClass(MockUserRoleable::class);
        $sharing2->setIdentityName('user.test');
        $sharing2->setRoles(array('ROLE_FOO'));

        $this->provider->expects($this->once())
            ->method('getSharingEntries')
            ->with(array(SubjectIdentity::fromObject($objects[0])))
            ->willReturn(array($sharing, $sharing2));

        /* @var SharingManagerInterface|\PHPUnit_Framework_MockObject_MockObject $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects($this->once())
            ->method('hasIdentityPermissible')
            ->willReturn(true);

        $sharingManager->expects($this->once())
            ->method('hasIdentityRoleable')
            ->willReturn(true);

        $this->pm = new PermissionManager($this->dispatcher, $this->provider, $sharingManager);
        $this->pm->addConfig(new PermissionConfig(MockObject::class, array(), SharingTypes::TYPE_PRIVATE));

        $this->pm->preloadPermissions($objects);
    }

    public function testResetPreloadPermissions()
    {
        $objects = array(
            new MockObject('foo'),
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

        $this->provider->expects($this->once())
            ->method('getSharingEntries')
            ->with(array())
            ->willReturn(array(new MockSharing()));

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

        $this->provider->expects($this->once())
            ->method('getSharingEntries')
            ->with(array())
            ->willReturn(array(new MockSharing()));

        $this->assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->assertTrue($checkPerm);
    }
}
