<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Authorization\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * Define some ExpressionLanguage functions.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class IsOrganizationProvider implements ExpressionFunctionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new ExpressionFunction('is_organization', function ($organization) {
                return sprintf('$organizational_context && $organizational_context->isOrganization(%s)', $organization);
            }, function (array $variables, $organization) {
                return isset($variables['organizational_context']) && $variables['organizational_context']->isOrganization($organization);
            }),
        );
    }
}
