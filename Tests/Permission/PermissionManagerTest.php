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

use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SecurityIdentityManagerInterface;
use Sonatra\Component\Security\Permission\PermissionConfig;
use Sonatra\Component\Security\Permission\PermissionManager;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SecurityIdentityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidManager;

    /**
     * @var PermissionManager
     */
    protected $pm;

    protected function setUp()
    {
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->pm = new PermissionManager($this->sidManager);
    }

    public function testIsEnabled()
    {
        $this->assertTrue($this->pm->isEnabled());

        $this->pm->disable();
        $this->assertFalse($this->pm->isEnabled());

        $this->pm->enable();
        $this->assertTrue($this->pm->isEnabled());
    }

    public function testGetSecurityIdentities()
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $sid = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();

        $this->sidManager->expects($this->once())
            ->method('getSecurityIdentities')
            ->with($token)
            ->willReturn(array(
                $sid,
            ));

        $sids = $this->pm->getSecurityIdentities($token);

        $this->assertTrue(is_array($sids));
        $this->assertCount(1, $sids);
        $this->assertSame($sid, $sids[0]);
    }

    public function testGetSecurityIdentitiesWithoutToken()
    {
        $this->sidManager->expects($this->never())
            ->method('getSecurityIdentities');

        $sids = $this->pm->getSecurityIdentities();

        $this->assertTrue(is_array($sids));
        $this->assertCount(0, $sids);
    }

    public function testHasConfig()
    {
        $pm = new PermissionManager($this->sidManager, array(
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
        $object = new \stdClass();
        $permissions = 'view';

        $this->assertTrue($this->pm->isGranted($sids, $object, $permissions));
    }

    public function testIsFieldGranted()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
        );
        $object = new MockObject('foo');
        $field = 'name';
        $permissions = 'view';

        $this->assertTrue($this->pm->isFieldGranted($sids, $object, $field, $permissions));
    }

    public function testPreloadPermissions()
    {
        $objects = array(
            new \stdClass(),
        );

        $res = $this->pm->preloadPermissions($objects);

        $this->assertInstanceOf(\SplObjectStorage::class, $res);
    }

    public function testResetPreloadPermissions()
    {
        $objects = array(
            new \stdClass(),
        );

        $pm = $this->pm->resetPreloadPermissions($objects);

        $this->assertSame($this->pm, $pm);
    }
}
