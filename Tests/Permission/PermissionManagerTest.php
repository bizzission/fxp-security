<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Permission;

use Fxp\Component\Security\Event\CheckPermissionEvent;
use Fxp\Component\Security\Event\PostLoadPermissionsEvent;
use Fxp\Component\Security\Event\PreLoadPermissionsEvent;
use Fxp\Component\Security\Identity\RoleSecurityIdentity;
use Fxp\Component\Security\Identity\SubjectIdentity;
use Fxp\Component\Security\Identity\UserSecurityIdentity;
use Fxp\Component\Security\Model\PermissionChecking;
use Fxp\Component\Security\Permission\FieldVote;
use Fxp\Component\Security\Permission\Loader\ConfigurationLoader;
use Fxp\Component\Security\Permission\PermissionConfig;
use Fxp\Component\Security\Permission\PermissionFactory;
use Fxp\Component\Security\Permission\PermissionFieldConfig;
use Fxp\Component\Security\Permission\PermissionManager;
use Fxp\Component\Security\Permission\PermissionProviderInterface;
use Fxp\Component\Security\Sharing\SharingManagerInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrganizationUser;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrgOptionalRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrgRequiredRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockPermission;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class PermissionManagerTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var PermissionProviderInterface|\PHPUnit\Framework\MockObject\MockObject
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

    protected function setUp(): void
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

    public function testIsEnabled(): void
    {
        static::assertTrue($this->pm->isEnabled());

        $this->pm->setEnabled(false);
        static::assertFalse($this->pm->isEnabled());

        $this->pm->setEnabled(true);
        static::assertTrue($this->pm->isEnabled());
    }

    public function testSetEnabledWithSharingManager(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|SharingManagerInterface $sm */
        $sm = $this->getMockBuilder(SharingManagerInterface::class)->getMock();

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            null,
            $sm
        );

        $sm->expects(static::once())
            ->method('setEnabled')
            ->with(false)
        ;

        $this->pm->setEnabled(false);
    }

    public function testHasConfig(): void
    {
        $pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            new PermissionFactory(new ConfigurationLoader([
                new PermissionConfig(MockObject::class),
            ]))
        );

        static::assertTrue($pm->hasConfig(MockObject::class));
    }

    public function testHasNotConfig(): void
    {
        static::assertFalse($this->pm->hasConfig(MockObject::class));
    }

    public function testAddConfig(): void
    {
        static::assertFalse($this->pm->hasConfig(MockObject::class));

        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        static::assertTrue($this->pm->hasConfig(MockObject::class));
    }

    public function testGetConfig(): void
    {
        $config = new PermissionConfig(MockObject::class);
        $this->pm->addConfig($config);

        static::assertTrue($this->pm->hasConfig(MockObject::class));
        static::assertSame($config, $this->pm->getConfig(MockObject::class));
    }

    public function testGetConfigWithNotManagedClass(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\PermissionConfigNotFoundException::class);
        $this->expectExceptionMessage('The permission configuration for the class "Fxp\\Component\\Security\\Tests\\Fixtures\\Model\\MockObject" is not found');

        $this->pm->getConfig(MockObject::class);
    }

    public function testGetConfigs(): void
    {
        $expected = [
            MockObject::class => new PermissionConfig(MockObject::class),
        ];

        $this->pm->addConfig($expected[MockObject::class]);

        static::assertSame($expected, $this->pm->getConfigs());
    }

    public function testIsManaged(): void
    {
        $this->pm->addConfig(new PermissionConfig(MockObject::class));
        $object = new MockObject('foo');

        static::assertTrue($this->pm->isManaged($object));
    }

    public function testIsManagedWithInvalidSubject(): void
    {
        $object = new \stdClass();

        static::assertFalse($this->pm->isManaged($object));
    }

    public function testIsManagedWithNonExistentSubject(): void
    {
        static::assertFalse($this->pm->isManaged('FooBar'));
    }

    public function testIsManagedWithUnexpectedTypeException(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "FieldVote|SubjectIdentityInterface|object|string", "NULL"');

        static::assertFalse($this->pm->isManaged(null));
    }

    public function testIsManagedWithNonManagedClass(): void
    {
        static::assertFalse($this->pm->isManaged(MockObject::class));
    }

    public function testIsFieldManaged(): void
    {
        $this->pm->addConfig(new PermissionConfig(MockObject::class, [], [], [
            new PermissionFieldConfig('name'),
        ]));

        $object = new MockObject('foo');
        $field = 'name';

        static::assertTrue($this->pm->isFieldManaged($object, $field));
    }

    public function testIsGranted(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = MockObject::class;
        $permission = 'view';

        static::assertTrue($this->pm->isGranted($sids, $permission, $object));
    }

    public function testIsGrantedWithNonExistentSubject(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = 'FooBar';
        $permission = 'view';

        static::assertFalse($this->pm->isGranted($sids, $permission, $object));
    }

    public function testIsGrantedWithGlobalPermission(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = null;
        $permission = 'foo';
        $perm = new MockPermission();
        $perm->setOperation('foo');

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([$perm])
        ;

        static::assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testIsGrantedWithGlobalPermissionAndMaster(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new UserSecurityIdentity(MockUserRoleable::class, 'user.test'),
        ];
        $org = new MockOrganization('foo');
        $user = new MockUserRoleable();
        $orgUser = new MockOrganizationUser($org, $user);
        $permission = 'view';
        $perm = new MockPermission();
        $perm->setClass(MockOrganization::class);
        $perm->setOperation('view');

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([$perm])
        ;

        $this->pm->addConfig(new PermissionConfig(MockOrganization::class));
        $this->pm->addConfig(new PermissionConfig(MockOrganizationUser::class, [], [], [], 'organization'));

        static::assertTrue($this->pm->isGranted($sids, $permission, $orgUser));
        $this->pm->clear();
    }

    public function testIsGrantedWithGlobalPermissionAndMasterWithEmptyObjectOfSubject(): void
    {
        $permConfigOrg = new PermissionConfig(MockOrganization::class);
        $permConfigOrgUser = new PermissionConfig(MockOrganizationUser::class, [], [], [], 'organization');

        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new UserSecurityIdentity(MockUserRoleable::class, 'user.test'),
        ];
        $object = new SubjectIdentity(MockOrganizationUser::class, 42);
        $permission = 'view';
        $perm = new MockPermission();
        $perm->setClass(MockOrganization::class);
        $perm->setOperation('view');

        $this->provider->expects(static::once())
            ->method('getMasterClass')
            ->with($permConfigOrgUser)
            ->willReturn(MockOrganization::class)
        ;

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([$perm])
        ;

        $this->pm->addConfig($permConfigOrg);
        $this->pm->addConfig($permConfigOrgUser);

        $res = $this->pm->isGranted($sids, $permission, $object);
        static::assertTrue($res);
    }

    public function testIsGrantedWithGlobalPermissionWithoutGrant(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER__foo'),
            new RoleSecurityIdentity(MockRole::class, 'ROLE_ADMIN__foo'),
        ];
        $object = null;
        $permission = 'bar';
        $perm = new MockPermission();
        $perm->setOperation('baz');

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER', 'ROLE_ADMIN'])
            ->willReturn([$perm])
        ;

        static::assertFalse($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testIsFieldGranted(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = new MockObject('foo');
        $field = 'name';
        $permission = 'view';

        static::assertTrue($this->pm->isFieldGranted($sids, $permission, $object, $field));
    }

    public function testIsGrantedWithSharingPermission(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = new MockObject('foo');
        $permission = 'test';

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([])
        ;

        /** @var \PHPUnit\Framework\MockObject\MockObject|SharingManagerInterface $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects(static::once())
            ->method('preloadRolePermissions')
            ->with([SubjectIdentity::fromObject($object)])
        ;

        $sharingManager->expects(static::once())
            ->method('isGranted')
            ->with($permission, SubjectIdentity::fromObject($object))
            ->willReturn(true)
        ;

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            null,
            $sharingManager
        );
        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        static::assertTrue($this->pm->isGranted($sids, $permission, $object));
        $this->pm->clear();
    }

    public function testIsGrantedWithSystemPermission(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
            new UserSecurityIdentity(MockUserRoleable::class, 'user.test'),
        ];
        $org = new MockOrganization('foo');
        $user = new MockUserRoleable();
        $orgUser = new MockOrganizationUser($org, $user);

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([])
        ;

        $this->pm->addConfig(new PermissionConfig(
            MockOrganization::class,
            [
                'view',
                'create',
                'update',
            ],
            [],
            [
                new PermissionFieldConfig('name', ['read']),
            ]
        ));
        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            [],
            [],
            [
                new PermissionFieldConfig('organization', ['edit']),
            ],
            'organization',
            [
                'create' => 'edit',
                'update' => 'edit',
            ]
        ));

        static::assertTrue($this->pm->isGranted($sids, 'view', $org));
        static::assertTrue($this->pm->isGranted($sids, 'view', $orgUser));
        static::assertTrue($this->pm->isFieldGranted($sids, 'read', $org, 'name'));
        static::assertFalse($this->pm->isFieldGranted($sids, 'edit', $org, 'name'));
        static::assertFalse($this->pm->isFieldGranted($sids, 'read', $orgUser, 'organization'));
        static::assertTrue($this->pm->isFieldGranted($sids, 'edit', $orgUser, 'organization'));
        $this->pm->clear();
    }

    public function getRoles(): array
    {
        return [
            [new MockRole('ROLE_TEST')],
            [new MockOrgOptionalRole('ROLE_TEST')],
            [new MockOrgRequiredRole('ROLE_TEST')],
        ];
    }

    /**
     * @dataProvider getRoles
     *
     * @param MockRole $role
     */
    public function testGetRolePermissions(MockRole $role): void
    {
        $subject = null;
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], false),
        ];

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_TEST'])
            ->willReturn([])
        ;

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn($permissions)
        ;

        $res = $this->pm->getRolePermissions($role, $subject);

        static::assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     *
     * @param MockRole $role
     */
    public function testGetRolePermissionsWithConfigPermissions(MockRole $role): void
    {
        $subject = MockOrganizationUser::class;
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], true, true),
        ];

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_TEST'])
            ->willReturn([])
        ;

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn($permissions)
        ;

        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            ['test'],
            [],
            [
                new PermissionFieldConfig('organization', ['edit']),
            ]
        ));

        $res = $this->pm->getRolePermissions($role, $subject);

        static::assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     *
     * @param MockRole $role
     */
    public function testGetRolePermissionsWithClassConfigPermission(MockRole $role): void
    {
        $subject = MockOrganizationUser::class;
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permission->setClass(PermissionProviderInterface::CONFIG_CLASS);
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], true, true),
        ];

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn([])
        ;

        $this->provider->expects(static::once())
            ->method('getConfigPermissions')
            ->with()
            ->willReturn($permissions)
        ;

        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            ['test']
        ));

        $res = $this->pm->getRolePermissions($role, $subject);

        static::assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     *
     * @param MockRole $role
     */
    public function testGetRolePermissionsWithFieldConfigPermission(MockRole $role): void
    {
        $subject = new FieldVote(MockOrganizationUser::class, 'organization');
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permission->setClass(PermissionProviderInterface::CONFIG_CLASS);
        $permission->setField(PermissionProviderInterface::CONFIG_FIELD);
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], true, true),
        ];

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn([])
        ;

        $this->provider->expects(static::once())
            ->method('getConfigPermissions')
            ->with()
            ->willReturn($permissions)
        ;

        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            [],
            [],
            [
                new PermissionFieldConfig('organization', ['test']),
            ]
        ));

        $res = $this->pm->getRolePermissions($role, $subject);

        static::assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     *
     * @param MockRole $role
     */
    public function testGetRolePermissionsWithFieldConfigPermissionAndMaster(MockRole $role): void
    {
        $subject = new FieldVote(MockOrganizationUser::class, 'organization');
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permission->setClass(PermissionProviderInterface::CONFIG_CLASS);
        $permission->setField(PermissionProviderInterface::CONFIG_FIELD);
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], false, true),
        ];

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn([])
        ;

        $this->provider->expects(static::once())
            ->method('getConfigPermissions')
            ->with()
            ->willReturn($permissions)
        ;

        $this->pm->addConfig(new PermissionConfig(MockOrganization::class));
        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            [],
            [],
            [
                new PermissionFieldConfig('organization', ['test']),
            ],
            'organization'
        ));

        $res = $this->pm->getRolePermissions($role, $subject);

        static::assertEquals($expected, $res);
    }

    /**
     * @dataProvider getRoles
     *
     * @param MockRole $role
     */
    public function testGetRolePermissionsWithRequiredConfigPermission(MockRole $role): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\PermissionNotFoundException::class);
        $this->expectExceptionMessage('The permission "test" for "Fxp\\Component\\Security\\Tests\\Fixtures\\Model\\MockOrganizationUser" is not found ant it required by the permission configuration');

        $subject = MockOrganizationUser::class;
        $permissions = [];

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with($subject)
            ->willReturn($permissions)
        ;

        $this->provider->expects(static::once())
            ->method('getConfigPermissions')
            ->with()
            ->willReturn([])
        ;

        $this->pm->addConfig(new PermissionConfig(
            MockOrganizationUser::class,
            ['test']
        ));

        $this->pm->getRolePermissions($role, $subject);
    }

    public function testGetFieldRolePermissions(): void
    {
        $role = new MockRole('ROLE_TEST');
        $subject = MockObject::class;
        $field = 'name';
        $permission = new MockPermission();
        $permission->setOperation('test');
        $permission->setClass($subject);
        $permission->setField($field);
        $permissions = [
            $permission,
        ];
        $expected = [
            new PermissionChecking($permissions[0], true),
        ];

        $this->provider->expects(static::once())
            ->method('getPermissionsBySubject')
            ->with(new FieldVote($subject, $field))
            ->willReturn($permissions)
        ;

        $res = $this->pm->getRoleFieldPermissions($role, $subject, $field);

        static::assertEquals($expected, $res);
    }

    public function testPreloadPermissions(): void
    {
        $objects = [new MockObject('foo')];

        $pm = $this->pm->preloadPermissions($objects);

        static::assertSame($this->pm, $pm);
    }

    public function testPreloadPermissionsWithSharing(): void
    {
        $objects = [new MockObject('foo')];

        /** @var \PHPUnit\Framework\MockObject\MockObject|SharingManagerInterface $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects(static::once())
            ->method('preloadPermissions')
            ->with($objects)
        ;

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            null,
            $sharingManager
        );

        $pm = $this->pm->preloadPermissions($objects);

        static::assertSame($this->pm, $pm);
    }

    public function testResetPreloadPermissions(): void
    {
        $objects = [
            new MockObject('foo'),
        ];

        $pm = $this->pm->resetPreloadPermissions($objects);

        static::assertSame($this->pm, $pm);
    }

    public function testResetPreloadPermissionsWithSharing(): void
    {
        $objects = [new MockObject('foo')];

        /** @var \PHPUnit\Framework\MockObject\MockObject|SharingManagerInterface $sharingManager */
        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $sharingManager->expects(static::once())
            ->method('resetPreloadPermissions')
            ->with($objects)
        ;

        $this->pm = new PermissionManager(
            $this->dispatcher,
            $this->provider,
            $this->propertyAccessor,
            null,
            $sharingManager
        );

        $pm = $this->pm->resetPreloadPermissions($objects);

        static::assertSame($this->pm, $pm);
    }

    public function testEvents(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = MockObject::class;
        $permission = 'view';
        $perm = new MockPermission();
        $perm->setOperation($permission);
        $perm->setClass(MockObject::class);
        $preLoad = false;
        $postLoad = false;
        $checkPerm = false;

        $this->dispatcher->addListener(PreLoadPermissionsEvent::class, function (PreLoadPermissionsEvent $event) use ($sids, &$preLoad): void {
            $preLoad = true;
            $this->assertSame($sids, $event->getSecurityIdentities());
        });

        $this->dispatcher->addListener(PostLoadPermissionsEvent::class, function (PostLoadPermissionsEvent $event) use ($sids, &$postLoad): void {
            $postLoad = true;
            $this->assertSame($sids, $event->getSecurityIdentities());
        });

        $this->dispatcher->addListener(CheckPermissionEvent::class, function (CheckPermissionEvent $event) use ($sids, &$checkPerm): void {
            $checkPerm = true;
            $this->assertSame($sids, $event->getSecurityIdentities());
        });

        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([$perm])
        ;

        static::assertTrue($this->pm->isGranted($sids, $permission, $object));
        static::assertTrue($preLoad);
        static::assertTrue($postLoad);
        static::assertTrue($checkPerm);
    }

    public function testOverrideGrantValueWithEvent(): void
    {
        $sids = [
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        ];
        $object = MockObject::class;
        $permission = 'view';
        $checkPerm = false;

        $this->dispatcher->addListener(CheckPermissionEvent::class, static function (CheckPermissionEvent $event) use (&$checkPerm): void {
            $checkPerm = true;
            $event->setGranted(true);
        });

        $this->pm->addConfig(new PermissionConfig(MockObject::class));

        $this->provider->expects(static::once())
            ->method('getPermissions')
            ->with(['ROLE_USER'])
            ->willReturn([])
        ;

        static::assertTrue($this->pm->isGranted($sids, $permission, $object));
        static::assertTrue($checkPerm);
    }
}
