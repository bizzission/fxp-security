<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Model\Traits;

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Security\Model\OrganizationInterface;
use Sonatra\Component\Security\Model\OrganizationUserInterface;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockUserOrganizationUsers;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class UserOrganizationUsersTraitTest extends TestCase
{
    public function testModel()
    {
        $user = new MockUserOrganizationUsers();

        $this->assertCount(0, $user->getUserOrganizations());
        $this->assertCount(0, $user->getUserOrganizationNames());
        $this->assertFalse($user->hasUserOrganization('foo'));
        $this->assertNull($user->getUserOrganization('foo'));

        /* @var OrganizationInterface|\PHPUnit_Framework_MockObject_MockObject $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $org->expects($this->any())
            ->method('getName')
            ->willReturn('foo');
        $org->expects($this->once())
            ->method('isUserOrganization')
            ->willReturn(false);

        /* @var OrganizationUserInterface|\PHPUnit_Framework_MockObject_MockObject $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();
        $orgUser->expects($this->any())
            ->method('getOrganization')
            ->willReturn($org);

        $user->addUserOrganization($orgUser);

        $this->assertCount(1, $user->getUserOrganizations());
        $this->assertCount(1, $user->getUserOrganizationNames());
        $this->assertTrue($user->hasUserOrganization('foo'));
        $this->assertSame($orgUser, $user->getUserOrganization('foo'));

        $user->removeUserOrganization($orgUser);

        $this->assertCount(0, $user->getUserOrganizations());
        $this->assertCount(0, $user->getUserOrganizationNames());
        $this->assertFalse($user->hasUserOrganization('foo'));
        $this->assertNull($user->getUserOrganization('foo'));
    }
}
