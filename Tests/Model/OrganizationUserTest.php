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

use Sonatra\Component\Security\Model\OrganizationInterface;
use Sonatra\Component\Security\Model\UserInterface;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockOrganizationUser;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationUserTest extends \PHPUnit_Framework_TestCase
{
    public function testModel()
    {
        /* @var OrganizationInterface|\PHPUnit_Framework_MockObject_MockObject $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $org->expects($this->any())
            ->method('getName')
            ->willReturn('foo');

        /* @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects($this->any())
            ->method('getUsername')
            ->willReturn('user.test');

        $orgUser = new MockOrganizationUser($org, $user);

        $this->assertSame(42, $orgUser->getId());
        $this->assertSame($org, $orgUser->getOrganization());
        $this->assertSame($user, $orgUser->getUser());

        $this->assertSame('foo:user.test', (string) $orgUser);

        /* @var OrganizationInterface|\PHPUnit_Framework_MockObject_MockObject $org2 */
        $org2 = $this->getMockBuilder(OrganizationInterface::class)->getMock();

        $orgUser->setOrganization($org2);

        $this->assertNotSame($org, $orgUser->getOrganization());
        $this->assertSame($org2, $orgUser->getOrganization());

        /* @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user2 */
        $user2 = $this->getMockBuilder(UserInterface::class)->getMock();

        $orgUser->setUser($user2);

        $this->assertNotSame($user, $orgUser->getUser());
        $this->assertSame($user2, $orgUser->getUser());
    }
}
