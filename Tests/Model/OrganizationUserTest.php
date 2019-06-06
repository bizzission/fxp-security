<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Model;

use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\UserInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrganizationUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class OrganizationUserTest extends TestCase
{
    public function testModel(): void
    {
        /** @var MockObject|OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $org->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('foo')
        ;

        /** @var MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $user->expects($this->atLeastOnce())
            ->method('getUsername')
            ->willReturn('user.test')
        ;

        $orgUser = new MockOrganizationUser($org, $user);

        $this->assertSame(42, $orgUser->getId());
        $this->assertSame($org, $orgUser->getOrganization());
        $this->assertSame($user, $orgUser->getUser());

        $this->assertSame('foo:user.test', (string) $orgUser);

        /** @var MockObject|OrganizationInterface $org2 */
        $org2 = $this->getMockBuilder(OrganizationInterface::class)->getMock();

        $orgUser->setOrganization($org2);

        $this->assertNotSame($org, $orgUser->getOrganization());
        $this->assertSame($org2, $orgUser->getOrganization());

        /** @var MockObject|UserInterface $user2 */
        $user2 = $this->getMockBuilder(UserInterface::class)->getMock();

        $orgUser->setUser($user2);

        $this->assertNotSame($user, $orgUser->getUser());
        $this->assertSame($user2, $orgUser->getUser());
    }
}
