<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Model;

use Sonatra\Component\Security\Tests\Fixtures\Model\MockPermission;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockRole;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RoleTest extends \PHPUnit_Framework_TestCase
{
    public function testModel()
    {
        $nameUser = 'ROLE_USER';
        $nameAdmin = 'ROLE_ADMIN';
        $role = new MockRole($nameUser);

        $this->assertNull($role->getId());
        $this->assertSame($nameUser, $role->getRole());
        $this->assertSame($nameUser, $role->getName());

        $role->setName($nameAdmin);
        $this->assertSame($nameAdmin, $role->getRole());
        $this->assertSame($nameAdmin, $role->getName());
        $this->assertSame($nameAdmin, (string) $role);

        $this->assertCount(0, $role->getParents());
        $this->assertCount(0, $role->getParentNames());
        $this->assertFalse($role->hasParent('PARENT'));

        $this->assertCount(0, $role->getChildren());
        $this->assertCount(0, $role->getChildrenNames());
        $this->assertFalse($role->hasChild('CHILD'));
    }

    public function testModelPermissions()
    {
        $role = new MockRole('ROLE_USER');
        $perm = new MockPermission();

        $this->assertCount(0, $role->getPermissions());
        $this->assertFalse($role->hasPermission($perm));

        $role->addPermission($perm);
        $this->assertTrue($role->hasPermission($perm));

        $role->removePermission($perm);
        $this->assertFalse($role->hasPermission($perm));
    }

    public function testClone()
    {
        $role = new MockRole('TEST');
        $ref = new \ReflectionClass($role);

        $prop = $ref->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($role, 42);
        $prop->setAccessible(false);

        $this->assertSame(42, $role->getId());

        $roleClone = clone $role;
        $this->assertNull($roleClone->getId());
        $this->assertSame($role->getRole(), $roleClone->getRole());
        $this->assertSame($role->getName(), $roleClone->getName());
    }

    public function testParent()
    {
        $roleUser = new MockRole('ROLE_USER');
        $roleAdmin = new MockRole('ROLE_ADMIN');

        $this->assertCount(0, $roleUser->getParents());
        $this->assertCount(0, $roleUser->getChildren());
        $this->assertCount(0, $roleAdmin->getParents());
        $this->assertCount(0, $roleAdmin->getChildren());

        $roleUser->addParent($roleAdmin);

        $this->assertCount(1, $roleUser->getParents());
        $this->assertCount(0, $roleUser->getChildren());
        $this->assertCount(0, $roleAdmin->getParents());
        $this->assertCount(1, $roleAdmin->getChildren());

        $this->assertSame('ROLE_ADMIN', current($roleUser->getParentNames()));
        $this->assertSame('ROLE_USER', current($roleAdmin->getChildrenNames()));

        $roleUser->removeParent($roleAdmin);

        $this->assertCount(0, $roleUser->getParents());
        $this->assertCount(0, $roleUser->getChildren());
        $this->assertCount(0, $roleAdmin->getParents());
        $this->assertCount(0, $roleAdmin->getChildren());
    }

    public function testChildren()
    {
        $roleUser = new MockRole('ROLE_USER');
        $roleAdmin = new MockRole('ROLE_ADMIN');

        $this->assertCount(0, $roleUser->getParents());
        $this->assertCount(0, $roleUser->getChildren());
        $this->assertCount(0, $roleAdmin->getParents());
        $this->assertCount(0, $roleAdmin->getChildren());

        $roleAdmin->addChild($roleUser);

        $this->assertCount(0, $roleUser->getParents());
        $this->assertCount(0, $roleUser->getChildren());
        $this->assertCount(0, $roleAdmin->getParents());
        $this->assertCount(1, $roleAdmin->getChildren());

        $this->assertSame('ROLE_USER', current($roleAdmin->getChildrenNames()));

        $roleAdmin->removeChild($roleUser);

        $this->assertCount(0, $roleUser->getParents());
        $this->assertCount(0, $roleUser->getChildren());
        $this->assertCount(0, $roleAdmin->getParents());
        $this->assertCount(0, $roleAdmin->getChildren());
    }
}
