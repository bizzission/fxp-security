<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Authorization\Expression;

use Sonatra\Component\Security\Authorization\Expression\HasOrgRoleProvider;
use Sonatra\Component\Security\Organizational\OrganizationalRoleInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class HasOrgRoleProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testHasOrgRole()
    {
        $orgRole = $this->getMockBuilder(OrganizationalRoleInterface::class)->getMock();
        $orgRole->expects($this->once())
            ->method('hasRole')
            ->with('ROLE_ADMIN')
            ->willReturn(true);

        $expressionLanguage = new ExpressionLanguage(null, array(new HasOrgRoleProvider()));
        $variables = array(
            'organizational_role' => $orgRole,
        );

        $this->assertTrue($expressionLanguage->evaluate('has_org_role("ROLE_ADMIN")', $variables));

        $compiled = '$organizational_role && $organizational_role->hasRole("ROLE_ADMIN")';
        $this->assertEquals($compiled, $expressionLanguage->compile('has_org_role("ROLE_ADMIN")'));
    }
}
