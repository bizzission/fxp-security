<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Identity;

use Fxp\Component\Security\Identity\GroupSecurityIdentity;
use Fxp\Component\Security\Identity\OrganizationSecurityIdentity;
use Fxp\Component\Security\Identity\RoleSecurityIdentity;
use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use Fxp\Component\Security\Model\GroupInterface;
use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\UserInterface;
use Fxp\Component\Security\Organizational\OrganizationalContextInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrganizationUserRoleableGroupable;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserOrganizationUsersGroupable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class OrganizationSecurityIdentityTest extends TestCase
{
    public function testDebugInfo(): void
    {
        $sid = new OrganizationSecurityIdentity(MockOrganization::class, 'foo');

        $this->assertSame('OrganizationSecurityIdentity(foo)', (string) $sid);
    }

    public function testTypeAndIdentifier(): void
    {
        $identity = new OrganizationSecurityIdentity(MockOrganization::class, 'identifier');

        $this->assertSame(MockOrganization::class, $identity->getType());
        $this->assertSame('identifier', $identity->getIdentifier());
    }

    public function getIdentities(): array
    {
        $id3 = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();
        $id3->expects($this->any())->method('getType')->willReturn(MockOrganization::class);
        $id3->expects($this->any())->method('getIdentifier')->willReturn('identifier');

        return [
            [new OrganizationSecurityIdentity(MockOrganization::class, 'identifier'), true],
            [new OrganizationSecurityIdentity(MockOrganization::class, 'other'), false],
            [$id3, false],
        ];
    }

    /**
     * @dataProvider getIdentities
     *
     * @param mixed $value  The value
     * @param bool  $result The expected result
     */
    public function testEquals($value, $result): void
    {
        $identity = new OrganizationSecurityIdentity(MockOrganization::class, 'identifier');

        $this->assertSame($result, $identity->equals($value));
    }

    public function testFromAccount(): void
    {
        /** @var MockObject|OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $org->expects($this->once())
            ->method('getName')
            ->willReturn('foo')
        ;

        $sid = OrganizationSecurityIdentity::fromAccount($org);

        $this->assertInstanceOf(OrganizationSecurityIdentity::class, $sid);
        $this->assertSame(\get_class($org), $sid->getType());
        $this->assertSame('foo', $sid->getIdentifier());
    }

    public function testFormTokenWithoutOrganizationalContext(): void
    {
        $user = new MockUserOrganizationUsersGroupable();
        $org = new MockOrganization('foo');
        $orgUser = new MockOrganizationUserRoleableGroupable($org, $user);

        $org->addRole('ROLE_ORG_TEST');

        /** @var GroupInterface|MockObject $group */
        $group = $this->getMockBuilder(GroupInterface::class)->getMock();
        $group->expects($this->once())
            ->method('getName')
            ->willReturn('GROUP_ORG_USER_TEST')
        ;

        $orgUser->addGroup($group);
        $orgUser->addRole('ROLE_ORG_USER_TEST');

        $user->addUserOrganization($orgUser);

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        /** @var MockObject|RoleHierarchyInterface $roleHierarchy */
        $roleHierarchy = $this->getMockBuilder(RoleHierarchy::class)->disableOriginalConstructor()->getMock();
        $roleHierarchy->expects($this->once())
            ->method('getReachableRoleNames')
            ->willReturnCallback(static function ($value) {
                return $value;
            })
        ;

        $sids = OrganizationSecurityIdentity::fromToken($token, null, $roleHierarchy);

        $this->assertCount(5, $sids);
        $this->assertInstanceOf(OrganizationSecurityIdentity::class, $sids[0]);
        $this->assertSame('foo', $sids[0]->getIdentifier());
        $this->assertInstanceOf(GroupSecurityIdentity::class, $sids[1]);
        $this->assertSame('GROUP_ORG_USER_TEST__foo', $sids[1]->getIdentifier());
        $this->assertInstanceOf(RoleSecurityIdentity::class, $sids[2]);
        $this->assertSame('ROLE_ORG_USER_TEST__foo', $sids[2]->getIdentifier());
        $this->assertInstanceOf(RoleSecurityIdentity::class, $sids[3]);
        $this->assertSame('ROLE_ORGANIZATION_USER__foo', $sids[3]->getIdentifier());
        $this->assertInstanceOf(RoleSecurityIdentity::class, $sids[4]);
        $this->assertSame('ROLE_ORG_TEST__foo', $sids[4]->getIdentifier());
    }

    public function testFormTokenWithOrganizationalContext(): void
    {
        $user = new MockUserOrganizationUsersGroupable();
        $org = new MockOrganization('foo');
        $orgUser = new MockOrganizationUserRoleableGroupable($org, $user);

        $org->addRole('ROLE_ORG_TEST');

        /** @var GroupInterface|MockObject $group */
        $group = $this->getMockBuilder(GroupInterface::class)->getMock();
        $group->expects($this->once())
            ->method('getName')
            ->willReturn('GROUP_ORG_USER_TEST')
        ;

        $orgUser->addGroup($group);
        $orgUser->addRole('ROLE_ORG_USER_TEST');

        $user->addUserOrganization($orgUser);

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        /** @var MockObject|OrganizationalContextInterface $context */
        $context = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $context->expects($this->once())
            ->method('getCurrentOrganization')
            ->willReturn($org)
        ;
        $context->expects($this->once())
            ->method('getCurrentOrganizationUser')
            ->willReturn($orgUser)
        ;

        /** @var MockObject|RoleHierarchyInterface $roleHierarchy */
        $roleHierarchy = $this->getMockBuilder(RoleHierarchy::class)->disableOriginalConstructor()->getMock();
        $roleHierarchy->expects($this->once())
            ->method('getReachableRoleNames')
            ->willReturnCallback(static function ($value) {
                return $value;
            })
        ;

        $sids = OrganizationSecurityIdentity::fromToken($token, $context, $roleHierarchy);

        $this->assertCount(5, $sids);
        $this->assertInstanceOf(OrganizationSecurityIdentity::class, $sids[0]);
        $this->assertSame('foo', $sids[0]->getIdentifier());
        $this->assertInstanceOf(GroupSecurityIdentity::class, $sids[1]);
        $this->assertSame('GROUP_ORG_USER_TEST__foo', $sids[1]->getIdentifier());
        $this->assertInstanceOf(RoleSecurityIdentity::class, $sids[2]);
        $this->assertSame('ROLE_ORG_USER_TEST__foo', $sids[2]->getIdentifier());
        $this->assertInstanceOf(RoleSecurityIdentity::class, $sids[3]);
        $this->assertSame('ROLE_ORGANIZATION_USER__foo', $sids[3]->getIdentifier());
        $this->assertInstanceOf(RoleSecurityIdentity::class, $sids[4]);
        $this->assertSame('ROLE_ORG_TEST__foo', $sids[4]->getIdentifier());
    }

    public function testFormTokenWithUserOrganizationalContext(): void
    {
        $user = new MockUserOrganizationUsersGroupable();
        $org = new MockOrganization($user->getUsername());
        $org->setUser($user);

        $org->addRole('ROLE_ORG_TEST');

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        /** @var MockObject|OrganizationalContextInterface $context */
        $context = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $context->expects($this->once())
            ->method('getCurrentOrganization')
            ->willReturn($org)
        ;
        $context->expects($this->once())
            ->method('getCurrentOrganizationUser')
            ->willReturn(null)
        ;

        /** @var MockObject|RoleHierarchyInterface $roleHierarchy */
        $roleHierarchy = $this->getMockBuilder(RoleHierarchy::class)->disableOriginalConstructor()->getMock();
        $roleHierarchy->expects($this->once())
            ->method('getReachableRoleNames')
            ->willReturnCallback(static function ($value) {
                return $value;
            })
        ;

        $sids = OrganizationSecurityIdentity::fromToken($token, $context, $roleHierarchy);

        $this->assertCount(2, $sids);
        $this->assertInstanceOf(OrganizationSecurityIdentity::class, $sids[0]);
        $this->assertSame('user.test', $sids[0]->getIdentifier());
        $this->assertInstanceOf(RoleSecurityIdentity::class, $sids[1]);
        $this->assertSame('ROLE_ORG_TEST__user.test', $sids[1]->getIdentifier());
    }

    public function testFormTokenWithInvalidInterface(): void
    {
        /** @var MockObject|UserInterface $user */
        $user = $this->getMockBuilder(UserInterface::class)->getMock();

        /** @var MockObject|TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $sids = OrganizationSecurityIdentity::fromToken($token);

        $this->assertCount(0, $sids);
    }
}
