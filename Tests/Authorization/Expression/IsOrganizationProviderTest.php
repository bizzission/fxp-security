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

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Security\Authorization\Expression\IsOrganizationProvider;
use Sonatra\Component\Security\Model\OrganizationInterface;
use Sonatra\Component\Security\Organizational\OrganizationalContextInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class IsOrganizationProviderTest extends TestCase
{
    public function testIsOrganization()
    {
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $orgContext = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();

        $orgContext->expects($this->once())
            ->method('isOrganization')
            ->with()
            ->willReturn(true);

        $expressionLanguage = new ExpressionLanguage(null, array(new IsOrganizationProvider()));
        $variables = array(
            'object' => $org,
            'organizational_context' => $orgContext,
        );

        $this->assertTrue($expressionLanguage->evaluate('is_organization()', $variables));

        $compiled = '$organizational_context && $organizational_context->isOrganization()';
        $this->assertEquals($compiled, $expressionLanguage->compile('is_organization()', array('object')));
    }
}
