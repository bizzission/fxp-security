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
class IsGrantedProvider implements ExpressionFunctionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new ExpressionFunction('is_granted', function ($attributes, $object = 'null') {
                return sprintf('$auth_checker && $auth_checker->isGranted(%s, %s)', $attributes, $object);
            }, function (array $variables, $attributes, $object = null) {
                return isset($variables['auth_checker']) && $variables['auth_checker']->isGranted($attributes, $object);
            }),
        );
    }
}
