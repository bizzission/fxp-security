<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Authorization\Expression;

use Fxp\Component\Security\Authorization\Expression\IsOrganizationProvider;
use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Organizational\OrganizationalContextInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class IsOrganizationProviderTest extends TestCase
{
    public function testIsOrganization(): void
    {
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();
        $orgContext = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();

        $orgContext->expects(static::once())
            ->method('isOrganization')
            ->with()
            ->willReturn(true)
        ;

        $expressionLanguage = new ExpressionLanguage(null, [new IsOrganizationProvider()]);
        $variables = [
            'object' => $org,
            'organizational_context' => $orgContext,
        ];

        static::assertTrue($expressionLanguage->evaluate('is_organization()', $variables));

        $compiled = '$organizational_context && $organizational_context->isOrganization()';
        static::assertEquals($compiled, $expressionLanguage->compile('is_organization()', ['object']));
    }
}
