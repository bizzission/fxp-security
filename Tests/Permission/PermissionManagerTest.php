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
use Sonatra\Component\Security\Identity\SecurityIdentityRetrievalStrategyInterface;
use Sonatra\Component\Security\Permission\PermissionManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SecurityIdentityRetrievalStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidRetrievalStrategy;

    /**
     * @var PermissionManager
     */
    protected $pm;

    protected function setUp()
    {
        $this->sidRetrievalStrategy = $this->getMockBuilder(SecurityIdentityRetrievalStrategyInterface::class)->getMock();
        $this->pm = new PermissionManager($this->sidRetrievalStrategy);
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

        $this->sidRetrievalStrategy->expects($this->once())
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
        $this->sidRetrievalStrategy->expects($this->never())
            ->method('getSecurityIdentities');

        $sids = $this->pm->getSecurityIdentities();

        $this->assertTrue(is_array($sids));
        $this->assertCount(0, $sids);
    }

    public function testIsManaged()
    {
        $object = new \stdClass();

        $this->assertTrue($this->pm->isManaged($object));
    }

    public function testIsFieldManaged()
    {
        $object = new \stdClass();
        $object->foo = 42;
        $field = 'foo';

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
        $object = new \stdClass();
        $object->foo = 42;
        $field = 'foo';
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
