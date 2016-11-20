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
class HasOrgRoleProvider implements ExpressionFunctionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new ExpressionFunction('has_org_role', function ($role) {
                return sprintf('$organizational_role && $organizational_role->hasRole(%s)', $role);
            }, function (array $variables, $role) {
                return $variables['organizational_role']
                    && $variables['organizational_role']->hasRole($role);
            }),
        );
    }
}
