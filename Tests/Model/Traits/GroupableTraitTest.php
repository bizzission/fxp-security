<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Model\Traits;

use Fxp\Component\Security\Tests\Fixtures\Model\MockGroup;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrganizationUserRoleableGroupable;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserGroupable;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 * @coversNothing
 */
final class GroupableTraitTest extends TestCase
{
    public function testUserModel(): void
    {
        $groupable = new MockUserGroupable(false);
        $group = new MockGroup('GROUP_TEST');

        $this->assertEmpty($groupable->getGroups());

        $groupable->addGroup($group);

        $this->assertFalse($groupable->hasGroup('GROUP_FOO'));
        $this->assertTrue($groupable->hasGroup('GROUP_TEST'));
        $this->assertEquals(['GROUP_TEST'], $groupable->getGroupNames());
        $this->assertEquals([$group], $groupable->getGroups()->toArray());

        $groupable->removeGroup($group);

        $this->assertFalse($groupable->hasGroup('GROUP_TEST'));
        $this->assertEquals([], $groupable->getGroupNames());
        $this->assertEquals([], $groupable->getGroups()->toArray());
    }

    public function testOrganizationUserModel(): void
    {
        $org = new MockOrganization('foo');
        $user = new MockUserRoleable();
        $groupable = new MockOrganizationUserRoleableGroupable($org, $user);
        $group = new MockGroup('GROUP_TEST');

        $this->assertEmpty($groupable->getGroups());

        $groupable->addGroup($group);

        $this->assertFalse($groupable->hasGroup('GROUP_FOO'));
        $this->assertTrue($groupable->hasGroup('GROUP_TEST'));
        $this->assertEquals(['GROUP_TEST'], $groupable->getGroupNames());
        $this->assertEquals([$group], $groupable->getGroups()->toArray());

        $groupable->removeGroup($group);

        $this->assertFalse($groupable->hasGroup('GROUP_TEST'));
        $this->assertEquals([], $groupable->getGroupNames());
        $this->assertEquals([], $groupable->getGroups()->toArray());
    }
}
