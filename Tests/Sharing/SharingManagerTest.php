<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Sharing;

use Fxp\Component\Security\Event\SharingDisabledEvent;
use Fxp\Component\Security\Event\SharingEnabledEvent;
use Fxp\Component\Security\Identity\SubjectIdentity;
use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\Sharing\Loader\ConfigurationLoader;
use Fxp\Component\Security\Sharing\SharingFactory;
use Fxp\Component\Security\Sharing\SharingIdentityConfig;
use Fxp\Component\Security\Sharing\SharingManager;
use Fxp\Component\Security\Sharing\SharingProviderInterface;
use Fxp\Component\Security\Sharing\SharingSubjectConfig;
use Fxp\Component\Security\SharingVisibilities;
use Fxp\Component\Security\Tests\Fixtures\Model\MockGroup;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockPermission;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockSharing;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SharingManagerTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|SharingProviderInterface
     */
    protected $provider;

    /**
     * @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dispatcher;

    /**
     * @var SharingManager
     */
    protected $sm;

    protected function setUp(): void
    {
        $this->provider = $this->getMockBuilder(SharingProviderInterface::class)->getMock();
        $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        $this->provider->expects($this->atLeastOnce())
            ->method('setSharingManager')
        ;

        $this->sm = new SharingManager($this->provider);
        $this->sm->setEventDispatcher($this->dispatcher);
    }

    public function testIsEnabled(): void
    {
        $this->dispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(new SharingDisabledEvent())
        ;

        $this->dispatcher->expects($this->at(1))
            ->method('dispatch')
            ->with(new SharingEnabledEvent())
        ;

        $this->assertTrue($this->sm->isEnabled());

        $this->sm->setEnabled(false);
        $this->assertFalse($this->sm->isEnabled());

        $this->sm->setEnabled(true);
        $this->assertTrue($this->sm->isEnabled());
    }

    public function testHasSubjectConfig(): void
    {
        $pm = new SharingManager($this->provider, new SharingFactory(new ConfigurationLoader([
            new SharingSubjectConfig(MockObject::class),
        ])));

        $this->assertTrue($pm->hasSubjectConfig(MockObject::class));
    }

    public function testHasIdentityConfig(): void
    {
        $pm = new SharingManager($this->provider, new SharingFactory(new ConfigurationLoader([], [
            new SharingIdentityConfig(MockRole::class),
        ])));

        $this->assertTrue($pm->hasIdentityConfig(MockRole::class));
    }

    public function testHasNotSubjectConfig(): void
    {
        $this->assertFalse($this->sm->hasSubjectConfig(MockObject::class));
    }

    public function testHasNotIdentityConfig(): void
    {
        $this->assertFalse($this->sm->hasIdentityConfig(MockRole::class));
    }

    public function testAddSubjectConfig(): void
    {
        $this->assertFalse($this->sm->hasSubjectConfig(MockObject::class));

        $this->sm->addSubjectConfig(new SharingSubjectConfig(MockObject::class));

        $this->assertTrue($this->sm->hasSubjectConfig(MockObject::class));
    }

    public function testAddIdentityConfig(): void
    {
        $this->assertFalse($this->sm->hasIdentityConfig(MockRole::class));

        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockRole::class));

        $this->assertTrue($this->sm->hasIdentityConfig(MockRole::class));
    }

    public function testAddIdentityConfigWithAlreadyExistingAlias(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\AlreadyConfigurationAliasExistingException::class);
        $this->expectExceptionMessage('The alias "foo" of sharing identity configuration for the class "Fxp\\Component\\Security\\Tests\\Fixtures\\Model\\MockGroup');

        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockRole::class, 'foo'));
        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockGroup::class, 'foo'));
    }

    public function testGetSubjectConfig(): void
    {
        $config = new SharingSubjectConfig(MockObject::class);
        $this->sm->addSubjectConfig($config);

        $this->assertTrue($this->sm->hasSubjectConfig(MockObject::class));
        $this->assertSame($config, $this->sm->getSubjectConfig(MockObject::class));
    }

    public function testGetIdentityConfig(): void
    {
        $config = new SharingIdentityConfig(MockRole::class, 'role');
        $this->sm->addIdentityConfig($config);

        $this->assertTrue($this->sm->hasIdentityConfig(MockRole::class));
        $this->assertSame($config, $this->sm->getIdentityConfig(MockRole::class));
        $this->assertTrue($this->sm->hasIdentityConfig('role'));
        $this->assertSame($config, $this->sm->getIdentityConfig('role'));
    }

    public function testGetSubjectConfigWithNotManagedClass(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\SharingSubjectConfigNotFoundException::class);
        $this->expectExceptionMessage('The sharing subject configuration for the class "Fxp\\Component\\Security\\Tests\\Fixtures\\Model\\MockRole" is not found');

        $this->sm->getSubjectConfig(MockRole::class);
    }

    public function testGetIdentityConfigWithNotManagedClass(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\SharingIdentityConfigNotFoundException::class);
        $this->expectExceptionMessage('The sharing identity configuration for the class "Fxp\\Component\\Security\\Tests\\Fixtures\\Model\\MockRole" is not found');

        $this->sm->getIdentityConfig(MockRole::class);
    }

    public function testGetSubjectConfigs(): void
    {
        $config = new SharingSubjectConfig(MockRole::class);
        $this->sm->addSubjectConfig($config);

        $this->assertSame([$config], $this->sm->getSubjectConfigs());
    }

    public function testGetIdentityConfigs(): void
    {
        $config = new SharingIdentityConfig(MockRole::class);
        $this->sm->addIdentityConfig($config);

        $this->assertSame([$config], $this->sm->getIdentityConfigs());
    }

    public function testHasIdentityRoleable(): void
    {
        $this->assertFalse($this->sm->hasIdentityRoleable());

        $config = new SharingIdentityConfig(MockRole::class, null, true);
        $this->sm->addIdentityConfig($config);

        $this->assertTrue($this->sm->hasIdentityRoleable());
    }

    public function testHasIdentityPermissible(): void
    {
        $this->assertFalse($this->sm->hasIdentityPermissible());

        $config = new SharingIdentityConfig(MockRole::class, null, false, true);
        $this->sm->addIdentityConfig($config);

        $this->assertTrue($this->sm->hasIdentityPermissible());
    }

    public function testHasSharingVisibilityWithoutConfig(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|SubjectIdentityInterface $subject */
        $subject = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();
        $subject->expects($this->once())
            ->method('getType')
            ->willReturn(MockObject::class)
        ;

        $this->assertFalse($this->sm->hasSharingVisibility($subject));
    }

    public function getSharingVisibilities(): array
    {
        return [
            [SharingVisibilities::TYPE_NONE, false],
            [SharingVisibilities::TYPE_PUBLIC, true],
            [SharingVisibilities::TYPE_PRIVATE, true],
        ];
    }

    /**
     * @dataProvider getSharingVisibilities
     *
     * @param string $visibility The sharing visibility
     * @param bool   $result     The result
     */
    public function testHasSharingVisibility($visibility, $result): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|SubjectIdentityInterface $subject */
        $subject = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();
        $subject->expects($this->once())
            ->method('getType')
            ->willReturn(MockObject::class)
        ;

        $this->sm->addSubjectConfig(new SharingSubjectConfig(MockObject::class, $visibility));
        $this->sm->addSubjectConfig(new SharingSubjectConfig(MockObject::class, $visibility));

        $this->assertSame($result, $this->sm->hasSharingVisibility($subject));
    }

    public function testResetPreloadPermissions(): void
    {
        $object = new MockObject('foo', 42);
        $sm = $this->sm->resetPreloadPermissions([$object]);

        $this->assertSame($sm, $this->sm);
    }

    public function testResetPreloadPermissionsWithInvalidSubjectIdentity(): void
    {
        $sm = $this->sm->resetPreloadPermissions([42]);

        $this->assertSame($sm, $this->sm);
    }

    public function testClear(): void
    {
        $sm = $this->sm->clear();

        $this->assertSame($sm, $this->sm);
    }

    public function testIsGranted(): void
    {
        $operation = 'view';
        $field = null;
        $object = new MockObject('foo', 42);
        $subject = SubjectIdentity::fromObject($object);

        $perm = new MockPermission();
        $perm->setOperation('view');
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setSubjectId(42);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->setIdentityName('ROLE_USER');
        $sharing->getPermissions()->add($perm);

        $sharing2 = new MockSharing();
        $sharing2->setSubjectClass(MockObject::class);
        $sharing2->setSubjectId(42);
        $sharing2->setIdentityClass(MockUserRoleable::class);
        $sharing2->setIdentityName('user.test');
        $sharing2->setRoles(['ROLE_TEST']);

        $this->provider->expects($this->once())
            ->method('getSharingEntries')
            ->with([SubjectIdentity::fromObject($object)])
            ->willReturn([$sharing, $sharing2])
        ;

        $roleTest = new MockRole('ROLE_TEST');
        $perm2 = new MockPermission();
        $perm2->setOperation('test');
        $roleTest->addPermission($perm2);

        $this->provider->expects($this->once())
            ->method('getPermissionRoles')
            ->with(['ROLE_TEST'])
            ->willReturn([$roleTest])
        ;

        $sConfig = new SharingSubjectConfig(MockObject::class, SharingVisibilities::TYPE_PRIVATE);
        $this->sm->addSubjectConfig($sConfig);

        $iConfig = new SharingIdentityConfig(MockRole::class, 'role', false, true);
        $this->sm->addIdentityConfig($iConfig);
        $iConfig2 = new SharingIdentityConfig(MockUserRoleable::class, 'user', true);
        $this->sm->addIdentityConfig($iConfig2);

        $this->sm->preloadPermissions([$object]);
        $this->sm->preloadRolePermissions([$subject]);

        $this->assertTrue($this->sm->isGranted($operation, $subject, $field));
    }

    public function testIsGrantedWithField(): void
    {
        $operation = 'view';
        $field = 'name';
        $object = new MockObject('foo', 42);
        $subject = SubjectIdentity::fromObject($object);

        $this->assertFalse($this->sm->isGranted($operation, $subject, $field));
    }

    public function testIsGrantedWithoutIdentityConfigRoleable(): void
    {
        $operation = 'view';
        $field = null;
        $object = new MockObject('foo', 42);
        $subject = SubjectIdentity::fromObject($object);

        $perm = new MockPermission();
        $perm->setOperation('view');
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setSubjectId(42);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->setIdentityName('ROLE_USER');
        $sharing->getPermissions()->add($perm);

        $this->provider->expects($this->once())
            ->method('getSharingEntries')
            ->with([SubjectIdentity::fromObject($object)])
            ->willReturn([$sharing])
        ;

        $sConfig = new SharingSubjectConfig(MockObject::class, SharingVisibilities::TYPE_PRIVATE);
        $this->sm->addSubjectConfig($sConfig);

        $iConfig = new SharingIdentityConfig(MockRole::class, 'role', false, true);
        $this->sm->addIdentityConfig($iConfig);

        $this->sm->preloadPermissions([$object]);

        $this->assertTrue($this->sm->isGranted($operation, $subject, $field));
    }

    public function testRenameIdentity(): void
    {
        $this->provider->expects($this->once())
            ->method('renameIdentity')
            ->with(MockRole::class, 'ROLE_FOO', 'ROLE_BAR')
            ->willReturn('QUERY')
        ;

        $this->sm->renameIdentity(MockRole::class, 'ROLE_FOO', 'ROLE_BAR');
    }

    public function testDeletes(): void
    {
        $ids = [42, 50];

        $this->provider->expects($this->once())
            ->method('deletes')
            ->with($ids)
            ->willReturn('QUERY')
        ;

        $this->sm->deletes($ids);
    }

    public function testDeleteIdentity(): void
    {
        $this->provider->expects($this->once())
            ->method('deleteIdentity')
            ->with(MockRole::class, 'ROLE_FOO')
            ->willReturn('QUERY')
        ;

        $this->sm->deleteIdentity(MockRole::class, 'ROLE_FOO');
    }
}
