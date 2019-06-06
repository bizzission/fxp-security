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

use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\OrganizationUserInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserOrganizationUsers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class UserOrganizationUsersTraitTest extends TestCase
{
    public function testModel(): void
    {
        $user = new MockUserOrganizationUsers();

        $this->assertCount(0, $user->getUserOrganizations());
        $this->assertCount(0, $user->getUserOrganizationNames());
        $this->assertFalse($user->hasUserOrganization('foo'));
        $this->assertNull($user->getUserOrganization('foo'));

        /** @var MockObject|OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $org->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('foo')
        ;
        $org->expects($this->once())
            ->method('isUserOrganization')
            ->willReturn(false)
        ;

        /** @var MockObject|OrganizationUserInterface $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();
        $orgUser->expects($this->atLeastOnce())
            ->method('getOrganization')
            ->willReturn($org)
        ;

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
